<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$slug = get_string('slug');
$project = preg_match('/^[a-z0-9-]{1,90}$/', $slug) ? Content::project($slug) : null;
if ($project === null) {
    not_found();
}

$photos = Content::photos((int) $project['id']);
$hasPair = $photos['before'] && $photos['after'];
$extra = $hasPair
    ? array_merge(array_slice($photos['before'], 1), array_slice($photos['after'], 1))
    : array_merge($photos['before'], $photos['after']);

$stmt = Database::pdo()->prepare('SELECT r.*, NULL AS service_name FROM reviews r WHERE r.project_id = ? AND r.is_published = 1 ORDER BY r.reviewed_on DESC LIMIT 1');
$stmt->execute([$project['id']]);
$review = $stmt->fetch();

view('layout/header', [
    'title' => $project['title'] . ' in ' . $project['town'],
    'active' => 'projecten',
    'description' => $project['summary'],
]);
?>
<section class="page-head">
    <div class="container">
        <p class="breadcrumb"><a href="<?= e(url('projecten')) ?>">Projecten</a> / <?= e($project['title']) ?></p>
        <h1><?= e($project['title']) ?></h1>
        <p class="lead"><?= e($project['summary']) ?></p>
    </div>
</section>
<section class="section section-white">
    <div class="container split">
        <div>
            <?php if ($hasPair): ?>
                <?php view('partials/before-after', ['before' => $photos['before'][0], 'after' => $photos['after'][0], 'eager' => true]); ?>
            <?php endif; ?>
            <?php if ($extra): ?>
                <div class="photo-strip">
                    <?php foreach ($extra as $photo): ?>
                        <figure>
                            <img src="<?= e(upload_url($photo['file'])) ?>" alt="<?= e($photo['alt']) ?>" width="<?= (int) $photo['width'] ?>" height="<?= (int) $photo['height'] ?>" loading="lazy">
                            <figcaption><?= $photo['kind'] === 'before' ? 'Voor' : 'Na' ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <ul class="project-meta">
                <li><strong>Plaats</strong><br><?= e($project['town']) ?></li>
                <?php if ($project['service_name']): ?><li><strong>Dienst</strong><br><a href="<?= e(url('diensten#' . $project['service_slug'])) ?>"><?= e($project['service_name']) ?></a></li><?php endif; ?>
                <?php if ($project['duration']): ?><li><strong>Duur</strong><br><?= e($project['duration']) ?></li><?php endif; ?>
                <?php if ($project['completed_on']): ?><li><strong>Opgeleverd</strong><br><?= e(format_month($project['completed_on'])) ?></li><?php endif; ?>
            </ul>
            <div class="prose">
                <?php foreach (preg_split('/\R{2,}/', trim($project['description'])) as $paragraph): ?>
                    <p><?= nl2br(e($paragraph), false) ?></p>
                <?php endforeach; ?>
            </div>
            <?php if ($review): ?>
                <?php view('partials/review', ['review' => $review]); ?>
            <?php endif; ?>
            <p><a class="btn btn-primary" href="<?= e(url('offerte' . ($project['service_slug'] ? '?dienst=' . rawurlencode($project['service_slug']) : ''))) ?>">Zoiets ook bij u? Vraag een offerte aan</a></p>
        </div>
    </div>
</section>
<?php view('layout/footer'); ?>
