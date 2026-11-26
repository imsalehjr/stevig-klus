<?php
$title = $title ?? '';
$active = $active ?? '';
$c = company();
$nav = [
    'diensten' => 'Diensten',
    'projecten' => 'Projecten',
    'reviews' => 'Reviews',
    'werkgebied' => 'Werkgebied',
    'over-ons' => 'Over ons',
    'contact' => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title !== '' ? $title . ' | ' . $c['name'] : $c['name'] . ' | Klusbedrijf in Eikenrode') ?></title>
    <meta name="description" content="<?= e($description ?? 'Stevig Klus & Renovatie uit Eikenrode: renovatie, schilderwerk, tegelwerk, timmerwerk en kleine reparaties. Vraag gratis een offerte aan.') ?>">
    <meta name="theme-color" content="#1f2a33">
<?php if (is_demo()): ?>
    <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
    <link rel="icon" href="<?= e(asset('img/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preload" href="<?= e(url('assets/fonts/archivo-var.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Naar de inhoud</a>
<?php if (is_demo()): ?>
<aside class="demo-banner" aria-label="Demo-melding">
    <div class="container"><strong>Demo:</strong> Stevig Klus &amp; Renovatie is een verzonnen bedrijf. Je kunt alles uitproberen, maar er gaan geen e-mails de deur uit en alle gegevens worden automatisch teruggezet. Vul geen echte gegevens in. <a href="<?= e(url('admin/login.php')) ?>">Bekijk ook het beheerpaneel</a>.</div>
</aside>
<?php endif; ?>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="<?= e(url()) ?>" aria-label="<?= e($c['name']) ?>, naar de homepage">
            <span class="logo-word">STEVIG</span>
            <span class="logo-sub">klus &amp; renovatie</span>
        </a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
            <span class="sr-only">Menu</span><span class="nav-toggle-bar" aria-hidden="true"></span>
        </button>
        <nav id="site-nav" class="site-nav" aria-label="Hoofdmenu">
            <ul>
                <?php foreach ($nav as $href => $label): ?>
                    <li><a href="<?= e(url($href)) ?>"<?= $active === $href ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a class="btn btn-primary" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a>
        </nav>
    </div>
</header>
<main id="main">
<?php foreach (take_flashes() as $flash): ?>
    <div class="container"><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div></div>
<?php endforeach; ?>
