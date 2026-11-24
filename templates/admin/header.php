<?php
$title = $title ?? 'Beheer';
$active = $active ?? '';
$newCount = (int) Database::pdo()->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")->fetchColumn();
$nav = [
    'index.php' => 'Dashboard',
    'requests.php' => 'Aanvragen',
    'services.php' => 'Diensten',
    'projects.php' => 'Projecten',
    'reviews.php' => 'Reviews',
    'areas.php' => 'Werkgebied',
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | Beheer Stevig</title>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="admin">
<a class="skip-link" href="#main">Naar de inhoud</a>
<header class="admin-bar">
    <div class="admin-bar-inner">
        <a class="logo logo-light" href="<?= e(url('admin/index.php')) ?>">
            <span class="logo-word">STEVIG</span>
            <span class="logo-sub">beheer</span>
        </a>
        <nav class="admin-nav" aria-label="Beheer">
            <?php foreach ($nav as $href => $label): ?>
                <a href="<?= e(url('admin/' . $href)) ?>"<?= $active === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?><?php if ($href === 'requests.php' && $newCount > 0): ?> <span class="badge" aria-label="<?= $newCount ?> nieuw"><?= $newCount ?></span><?php endif; ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-user">
            <a href="<?= e(url()) ?>">Website</a>
            <form method="post" action="<?= e(url('admin/logout.php')) ?>">
                <?= Csrf::field() ?>
                <button type="submit" class="link-button">Uitloggen <span class="muted-light">(<?= e($_SESSION['admin_name'] ?? '') ?>)</span></button>
            </form>
        </div>
    </div>
</header>
<main id="main" class="admin-main">
<?php foreach (take_flashes() as $flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endforeach; ?>
