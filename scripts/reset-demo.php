<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/src/bootstrap.php';

if (!is_demo()) {
    fwrite(STDERR, "DEMO_MODE is not true. Refusing to reset.\n");
    exit(1);
}

$pdo = Database::pdo();

foreach (['schema.sql', 'seed.sql', 'demo.sql'] as $file) {
    $sql = (string) file_get_contents(BASE_PATH . '/database/' . $file);
    $sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b.*$/mi', '', $sql);
    $pdo->exec($sql);
}

$email = (string) Env::get('DEMO_ADMIN_EMAIL', '');
$password = (string) Env::get('DEMO_ADMIN_PASSWORD', '');
if ($email !== '' && $password !== '') {
    $pdo->prepare('INSERT INTO admin_users (name, email, password_hash) VALUES (?, ?, ?)')
        ->execute(['Demo', $email, password_hash($password, PASSWORD_DEFAULT)]);
}

$keep = static fn (string $path): bool => str_starts_with(basename($path), 'demo-') || basename($path) === '.gitkeep' || basename($path) === '.htaccess';
foreach ([
    BASE_PATH . '/storage/mail/*',
    BASE_PATH . '/storage/uploads/tmp/*',
    BASE_PATH . '/storage/uploads/requests/*',
    BASE_PATH . '/public/uploads/projects/*',
] as $pattern) {
    foreach (glob($pattern) ?: [] as $file) {
        if (is_file($file) && !$keep($file)) {
            unlink($file);
        }
    }
}

echo 'Demo reset at ' . date('Y-m-d H:i:s') . "\n";
