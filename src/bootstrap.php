<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(static function (string $class): void {
    $file = BASE_PATH . '/src/' . $class . '.php';
    if (preg_match('/^[A-Za-z]+$/', $class) && is_file($file)) {
        require $file;
    }
});

require BASE_PATH . '/src/helpers.php';
require BASE_PATH . '/lib/PHPMailer/Exception.php';
require BASE_PATH . '/lib/PHPMailer/PHPMailer.php';
require BASE_PATH . '/lib/PHPMailer/SMTP.php';

Env::load(BASE_PATH . '/.env');

date_default_timezone_set('Europe/Amsterdam');

$isLocal = Env::get('APP_ENV', 'production') === 'local';
ini_set('display_errors', $isLocal ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (strlen((string) Env::get('APP_KEY', '')) < 32) {
    http_response_code(500);
    exit('APP_KEY in .env is missing or too short (min. 32 characters). See README.');
}

header_remove('X-Powered-By');

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_name('stevig_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
