<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$services = Content::services();
$filterSlug = get_string('dienst');
$filter = null;
foreach ($services as $s) {
    if ($s['slug'] === $filterSlug) {
        $filter = $s;
    }
}
$projects = Content::projects($filter ? (int) $filter['id'] : null);

view('layout/header', [
    'title' => $filter ? 'Projecten ' . mb_strtolower($filter['name']) : 'Projecten',
    'active' => 'projecten',
    'description' => 'Bekijk voor- en na-foto\'s van badkamers, schilderwerk, tegelwerk en timmerwerk door Stevig Klus & Renovatie.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Projecten</h1>
        <p class="lead">Een selectie van onze klussen, met foto's van voor en na. Open een project en schuif over de foto om het verschil te zien.</p>
    </div>
</section>
<section class="section" aria-labelledby="overview-title">
    <div class="container">
        <h2 class="sr-only" id="overview-title">Overzicht van projecten</h2>
        <nav class="filter-bar" aria-label="Filter op dienst">
            <a href="<?= e(url('projecten')) ?>"<?= $filter ? '' : ' aria-current="true"' ?>>Alles</a>
            <?php foreach ($services as $s): ?>
                <a href="<?= e(url('projecten?dienst=' . rawurlencode($s['slug']))) ?>"<?= $filter && $filter['id'] === $s['id'] ? ' aria-current="true"' : '' ?>><?= e($s['name']) ?></a>
            <?php endforeach; ?>
        </nav>
        <?php if ($projects): ?>
            <div class="project-grid">
                <?php foreach ($projects as $project): view('partials/project-card', ['project' => $project]); endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">Voor deze dienst staan nog geen projecten online. <a href="<?= e(url('projecten')) ?>">Bekijk alle projecten</a>.</p>
        <?php endif; ?>
    </div>
</section>
<?php view('partials/cta'); ?>
<?php view('layout/footer'); ?>
