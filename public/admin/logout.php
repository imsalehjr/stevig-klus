<?php

require dirname(__DIR__, 2) . '/src/bootstrap.php';

header('Cache-Control: no-store');

if (!is_post()) {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed');
}

Csrf::verify();
Auth::logout();
session_start();
flash('success', 'U bent uitgelogd.');
redirect('admin/login.php');
