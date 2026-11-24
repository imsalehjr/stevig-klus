<figure class="review">
    <div class="review-stars" role="img" aria-label="<?= (int) $review['rating'] ?> van de 5 sterren"><?= stars((int) $review['rating']) ?></div>
    <blockquote><p><?= nl2br(e($review['body']), false) ?></p></blockquote>
    <figcaption><strong><?= e($review['customer_name']) ?></strong><?= e($review['town']) ?><?= !empty($review['service_name']) ? ', ' . e(mb_strtolower($review['service_name'])) : '' ?><br><?= e(format_month($review['reviewed_on'])) ?></figcaption>
</figure>
