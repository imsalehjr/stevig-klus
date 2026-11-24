<a class="project-card" href="<?= e(url('projecten/' . $project['slug'])) ?>">
    <div class="project-card-media">
        <?php if (!empty($project['cover_file'])): ?>
            <img src="<?= e(upload_url($project['cover_file'])) ?>" alt="<?= e($project['cover_alt'] ?? '') ?>" width="800" height="600" loading="lazy">
        <?php endif; ?>
        <?php if (!empty($project['service_name'])): ?><span class="tag"><?= e($project['service_name']) ?></span><?php endif; ?>
    </div>
    <h3><?= e($project['title']) ?></h3>
    <p><?= e($project['town']) ?><?= $project['completed_on'] ? ', ' . e(format_month($project['completed_on'])) : '' ?></p>
</a>
