<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$services = Content::services();

view('layout/header', [
    'title' => 'Diensten',
    'active' => 'diensten',
    'description' => 'Renovatie, schilderwerk, tegelwerk, timmerwerk en kleine reparaties in Eikenrode en omgeving.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Diensten</h1>
        <p class="lead">Van één losse tegel tot een complete badkamer. Alles in eigen beheer, dus u heeft één aanspreekpunt en één planning.</p>
    </div>
</section>
<section class="section section-white">
    <div class="container">
        <?php foreach ($services as $s): ?>
            <article class="service-detail" id="<?= e($s['slug']) ?>">
                <?php view('partials/icon', ['name' => $s['icon']]); ?>
                <div class="measure">
                    <h2><?= e($s['name']) ?></h2>
                    <p class="lead"><?= e($s['intro']) ?></p>
                    <p><?= nl2br(e($s['description']), false) ?></p>
                </div>
                <div>
                    <a class="btn btn-primary" href="<?= e(url('offerte?dienst=' . rawurlencode($s['slug']))) ?>">Offerte voor <?= e(mb_strtolower($s['name'])) ?></a>
                    <p><a href="<?= e(url('projecten?dienst=' . rawurlencode($s['slug']))) ?>">Bekijk projecten</a></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php view('partials/cta'); ?>
<?php view('layout/footer'); ?>
