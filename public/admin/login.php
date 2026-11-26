<?php

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

if (!Auth::hasAnyAdmin()) {
    redirect('admin/setup.php');
}
if (Auth::check()) {
    redirect('admin/index.php');
}

$error = '';
$email = is_demo() ? (string) Env::get('DEMO_ADMIN_EMAIL', '') : '';

if (is_post()) {
    Csrf::verify();
    Throttle::prune();
    $email = post_string('email');
    $password = $_POST['password'] ?? '';

    if (Auth::tooManyAttempts()) {
        $error = 'Te veel mislukte pogingen. Wacht ' . Auth::WINDOW_MINUTES . ' minuten en probeer het opnieuw.';
    } elseif (!is_string($password) || strlen($password) > 1024 || !Auth::attempt($email, $password)) {
        $error = 'E-mailadres of wachtwoord klopt niet.';
    } else {
        redirect('admin/index.php');
    }
}

view('admin/auth-layout', [
    'title' => 'Inloggen',
    'error' => $error,
    'email' => $email,
    'button' => 'Inloggen',
    'passwordAutocomplete' => 'current-password',
    'demoLogin' => is_demo() ? ['email' => Env::get('DEMO_ADMIN_EMAIL', ''), 'password' => Env::get('DEMO_ADMIN_PASSWORD', '')] : null,
]);
