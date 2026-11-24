<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$services = Content::services();
$pair = Content::featuredPair();
$projects = Content::projects(null, 3, true);
$reviews = Content::reviews(3);
$rating = Content::ratingSummary();
$areas = Content::areas();
$c = company();

view('layout/header', ['active' => '']);
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <h1>Verbouwen zonder gedoe</h1>
            <p class="lead">Badkamer, schilderwerk, tegels of dat lijstje kleine klussen: wij komen langs, maken een vaste prijs en ruimen elke dag netjes op.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a>
                <a class="btn btn-ghost" href="<?= e(url('projecten')) ?>">Bekijk projecten</a>
            </div>
            <ul class="hero-facts">
                <li><?php view('partials/icon', ['name' => 'clock']); ?>Binnen 2 werkdagen reactie</li>
                <li><?php view('partials/icon', ['name' => 'check']); ?>Gratis inmeting en vaste prijs</li>
                <?php if ($rating['total'] > 0): ?>
                    <li><?php view('partials/icon', ['name' => 'shield']); ?>Gemiddeld <?= e(number_format($rating['avg'], 1, ',', '')) ?> van 5 uit <?= $rating['total'] ?> reviews</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php if ($pair): ?>
            <figure class="hero-figure">
                <?php view('partials/before-after', ['before' => $pair['before'], 'after' => $pair['after'], 'eager' => true, 'label' => 'Vergelijk voor en na: ' . $pair['project']['title']]); ?>
                <figcaption><a href="<?= e(url('projecten/' . $pair['project']['slug'])) ?>"><?= e($pair['project']['title']) ?></a>, <?= e($pair['project']['town']) ?>. Schuif om het verschil te zien.</figcaption>
            </figure>
        <?php endif; ?>
    </div>
</section>

<?php if ($areas): view('partials/ruler', ['areas' => $areas]); endif; ?>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Wat we doen</h2>
            <p>Vijf vakgebieden, één team. Zo hoeft u niet zelf drie partijen op elkaar af te stemmen.</p>
        </div>
        <ul class="service-list">
            <?php foreach ($services as $s): ?>
                <li class="service-row">
                    <?php view('partials/icon', ['name' => $s['icon']]); ?>
                    <h3><a href="<?= e(url('diensten#' . $s['slug'])) ?>"><?= e($s['name']) ?></a></h3>
                    <p><?= e($s['intro']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<?php if ($projects): ?>
<section class="section section-white">
    <div class="container">
        <div class="section-head">
            <h2>Recent opgeleverd</h2>
            <p><a href="<?= e(url('projecten')) ?>">Alle projecten bekijken</a></p>
        </div>
        <div class="project-grid">
            <?php foreach ($projects as $project): view('partials/project-card', ['project' => $project]); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section-ink">
    <div class="container">
        <h2>Zo werkt het</h2>
        <ol class="steps">
            <li><h3>Aanvraag</h3><p>Vul het formulier in en stuur een paar foto's mee. Dat kost u 3 minuten.</p></li>
            <li><h3>Inmeting</h3><p>We bellen u binnen 2 werkdagen en komen gratis bij u langs om op te meten.</p></li>
            <li><h3>Vaste prijs</h3><p>U krijgt een duidelijke offerte. Geen meerwerk zonder dat u het vooraf weet.</p></li>
            <li><h3>Uitvoering</h3><p>We werken volgens planning en leveren de ruimte bezemschoon op.</p></li>
        </ol>
    </div>
</section>

<?php if ($reviews): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Wat klanten zeggen</h2>
            <p><a href="<?= e(url('reviews')) ?>">Alle <?= $rating['total'] ?> reviews lezen</a></p>
        </div>
        <div class="review-grid">
            <?php foreach ($reviews as $review): view('partials/review', ['review' => $review]); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php view('partials/cta'); ?>
<?php view('layout/footer'); ?>
