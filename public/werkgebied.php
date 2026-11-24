<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$areas = Content::areas();

view('layout/header', [
    'title' => 'Werkgebied',
    'active' => 'werkgebied',
    'description' => 'Wij werken in Eikenrode en de dorpen in de omgeving, tot ongeveer 25 kilometer.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Werkgebied</h1>
        <p class="lead">We werken binnen ongeveer 25 kilometer rond onze werkplaats in Eikenrode. Zo zijn we snel ter plaatse en rekenen we geen voorrijkosten.</p>
    </div>
</section>
<?php if ($areas): view('partials/ruler', ['areas' => $areas]); endif; ?>
<section class="section section-white">
    <div class="container split">
        <div>
            <h2>Plaatsen waar we werken</h2>
            <?php if ($areas): ?>
                <ul class="area-list">
                    <?php foreach ($areas as $a): ?>
                        <li><?= e($a['town']) ?> <span><?= (int) $a['distance_km'] === 0 ? 'onze thuisbasis' : (int) $a['distance_km'] . ' km'; ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="empty">Het werkgebied wordt binnenkort bijgewerkt.</p>
            <?php endif; ?>
        </div>
        <aside class="panel">
            <h2>Woont u net buiten ons gebied?</h2>
            <p>Bij grotere klussen, zoals een complete badkamer of verbouwing, komen we graag verder. Vermeld uw woonplaats in de aanvraag, dan laten we snel weten of het past.</p>
            <p><a class="btn btn-primary" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a></p>
        </aside>
    </div>
</section>
<?php view('layout/footer'); ?>
