<?php

declare(strict_types=1);

final class Auth
{
    public const MAX_ATTEMPTS = 5;
    public const WINDOW_MINUTES = 15;
    private const IDLE_TIMEOUT = 3600;
    private const ABSOLUTE_TIMEOUT = 43200;
    private const DUMMY_HASH = '$2y$12$e2exBVmPVp9jv7w0X3.O.uYy7NH0QGX1z4zrSzu6L7c09JLPX4qv.';
    public const HASH_OPTIONS = ['cost' => 12];

    public static function attempt(string $email, string $password): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, name, password_hash FROM admin_users WHERE email = ?');
        $stmt->execute([mb_strtolower($email)]);
        $user = $stmt->fetch();

        $passwordOk = password_verify($password, $user['password_hash'] ?? self::DUMMY_HASH);
        if ($user === false || !$passwordOk) {
            Throttle::hit('login');
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, self::HASH_OPTIONS)) {
            $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')
                ->execute([self::hash($password), $user['id']]);
        }

        Throttle::clear('login');
        $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['admin_id'] = (int) $user['id'];
        $_SESSION['admin_name'] = (string) $user['name'];
        $_SESSION['admin_login_at'] = time();
        $_SESSION['admin_last_seen'] = time();
        $_SESSION['admin_ua'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        return true;
    }

    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, self::HASH_OPTIONS);
    }

    public static function tooManyAttempts(): bool
    {
        return Throttle::tooMany('login', self::MAX_ATTEMPTS, self::WINDOW_MINUTES);
    }

    public static function check(): bool
    {
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        $now = time();
        $expired = $now - ($_SESSION['admin_last_seen'] ?? 0) > self::IDLE_TIMEOUT
            || $now - ($_SESSION['admin_login_at'] ?? 0) > self::ABSOLUTE_TIMEOUT
            || !hash_equals($_SESSION['admin_ua'] ?? '', hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? ''));
        if ($expired) {
            self::logout();
            return false;
        }
        $_SESSION['admin_last_seen'] = $now;
        return true;
    }

    public static function id(): int
    {
        return (int) ($_SESSION['admin_id'] ?? 0);
    }

    public static function require(): void
    {
        if (!self::check()) {
            redirect('admin/login.php');
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function hasAnyAdmin(): bool
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
    }
}
