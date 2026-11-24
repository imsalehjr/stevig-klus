<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$reviews = Content::reviews();
$rating = Content::ratingSummary();

view('layout/header', [
    'title' => 'Reviews',
    'active' => 'reviews',
    'description' => 'Lees wat klanten zeggen over Stevig Klus & Renovatie.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Reviews</h1>
        <p class="lead">Alle reviews komen van klanten bij wie we echt aan het werk zijn geweest. We plaatsen ook de minder lovende.</p>
    </div>
</section>
<section class="section">
    <div class="container">
        <?php if ($reviews): ?>
            <div class="rating-summary">
                <strong><?= e(number_format($rating['avg'], 1, ',', '')) ?></strong>
                <div>
                    <div class="review-stars" role="img" aria-label="Gemiddeld <?= e(number_format($rating['avg'], 1, ',', '')) ?> van de 5 sterren"><?= stars((int) round($rating['avg'])) ?></div>
                    <span class="muted">Gemiddelde uit <?= $rating['total'] ?> reviews</span>
                </div>
            </div>
            <div class="review-grid">
                <?php foreach ($reviews as $review): view('partials/review', ['review' => $review]); endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">Er staan nog geen reviews online.</p>
        <?php endif; ?>
    </div>
</section>
<?php view('partials/cta'); ?>
<?php view('layout/footer'); ?>
