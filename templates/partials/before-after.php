<?php $label = $label ?? 'Schuif om voor en na te vergelijken'; ?>
<div class="ba">
    <img class="ba-before" src="<?= e(upload_url($before['file'])) ?>" alt="<?= e($before['alt'] !== '' ? $before['alt'] : 'Situatie voor de klus') ?>" width="<?= (int) $before['width'] ?>" height="<?= (int) $before['height'] ?>" <?= !empty($eager) ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
    <img class="ba-after" src="<?= e(upload_url($after['file'])) ?>" alt="<?= e($after['alt'] !== '' ? $after['alt'] : 'Resultaat na de klus') ?>" width="<?= (int) $after['width'] ?>" height="<?= (int) $after['height'] ?>" <?= !empty($eager) ? '' : 'loading="lazy"' ?>>
    <span class="ba-label ba-label-before" aria-hidden="true">Voor</span>
    <span class="ba-label ba-label-after" aria-hidden="true">Na</span>
    <span class="ba-handle" aria-hidden="true"></span>
    <input class="ba-range" type="range" min="0" max="100" value="50" aria-label="<?= e($label) ?>">
</div>
