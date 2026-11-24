<?php
$maxKm = 5;
foreach ($areas as $a) {
    $maxKm = max($maxKm, (int) $a['distance_km']);
}
$maxKm = (int) (ceil($maxKm / 5) * 5);
?>
<section class="ruler" data-max="<?= $maxKm ?>" aria-labelledby="ruler-title">
    <div class="container">
        <div class="ruler-inner">
            <h2 class="ruler-title" id="ruler-title">Wij werken in en rond Eikenrode</h2>
            <ul class="ruler-towns">
                <?php foreach ($areas as $a): ?>
                    <li data-town="<?= e($a['town']) ?>" data-km="<?= (int) $a['distance_km'] ?>"><?= e($a['town']) ?> <span><?= (int) $a['distance_km'] ?> km</span></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="ruler-scale"></div>
    </div>
</section>
