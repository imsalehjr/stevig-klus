<?php

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

if (is_demo() || Auth::hasAnyAdmin()) {
    redirect('admin/login.php');
}

$error = '';
$email = '';
$name = '';

if (is_post()) {
    Csrf::verify();
    $name = post_string('name');
    $email = mb_strtolower(post_string('email'));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $error = 'Vul een naam in van 2 tot 80 tekens.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false || mb_strlen($email) > 190) {
        $error = 'Vul een geldig e-mailadres in.';
    } elseif (!is_string($password) || mb_strlen($password) < 12) {
        $error = 'Kies een wachtwoord van minimaal 12 tekens.';
    } elseif (strlen($password) > 72) {
        $error = 'Het wachtwoord mag maximaal 72 bytes lang zijn.';
    } elseif ($password !== $confirm) {
        $error = 'De wachtwoorden zijn niet gelijk.';
    } else {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        $exists = (int) $pdo->query('SELECT COUNT(*) FROM admin_users FOR UPDATE')->fetchColumn() > 0;
        if (!$exists) {
            $pdo->prepare('INSERT INTO admin_users (name, email, password_hash) VALUES (?, ?, ?)')
                ->execute([$name, $email, Auth::hash($password)]);
        }
        $pdo->commit();
        flash('success', 'Account aangemaakt. Log nu in.');
        redirect('admin/login.php');
    }
}

view('admin/auth-layout', [
    'title' => 'Eerste beheerder aanmaken',
    'intro' => 'Er is nog geen beheerder. Maak het eerste account aan. Deze pagina sluit zichzelf zodra er een beheerder bestaat.',
    'error' => $error,
    'email' => $email,
    'name' => $name,
    'confirm' => true,
    'button' => 'Account aanmaken',
    'passwordAutocomplete' => 'new-password',
]);
