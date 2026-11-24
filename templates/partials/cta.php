<?php $c = company(); ?>
<section class="cta-band">
    <div class="container cta-band-inner">
        <div>
            <h2>Klus in gedachten?</h2>
            <p>Stuur een paar foto's mee, dan krijgt u binnen 2 werkdagen een reactie.</p>
        </div>
        <p><a class="btn btn-dark" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a> <a class="btn btn-ghost" href="tel:<?= e($c['phone_link']) ?>">Bel <?= e($c['phone']) ?></a></p>
    </div>
</section>
