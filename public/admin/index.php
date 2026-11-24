<?php
require dirname(__DIR__, 2) . '/src/admin.php';
view('admin/header', ['title' => 'Dashboard', 'active' => 'index.php']);
echo '<h1>Dashboard</h1>';
view('admin/footer');
