<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$c = company();

view('layout/header', [
    'title' => 'Contact',
    'active' => 'contact',
    'description' => 'Neem contact op met Stevig Klus & Renovatie in Eikenrode. Bel, mail of vraag direct een offerte aan.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Contact</h1>
        <p class="lead">Voor een prijsopgave werkt het formulier het snelst: met uw foto's erbij kunnen we vaak al een eerste inschatting maken.</p>
    </div>
</section>
<section class="section section-white">
    <div class="container split">
        <div>
            <h2>Bereikbaar</h2>
            <ul class="contact-lines">
                <li><?php view('partials/icon', ['name' => 'phone']); ?><span><a href="tel:<?= e($c['phone_link']) ?>"><?= e($c['phone']) ?></a><br><span class="muted">Ma t/m vr 7:30 tot 17:00</span></span></li>
                <li><?php view('partials/icon', ['name' => 'mail']); ?><span><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a><br><span class="muted">We reageren binnen 2 werkdagen</span></span></li>
                <li><?php view('partials/icon', ['name' => 'pin']); ?><span><?= e($c['street']) ?><br><?= e($c['postcode'] . ' ' . $c['town']) ?><br><span class="muted">Werkplaats, alleen op afspraak</span></span></li>
            </ul>
            <p class="muted">KvK <?= e($c['kvk']) ?> (fictief)</p>
        </div>
        <aside class="panel confirm-box">
            <h2>Offerte nodig?</h2>
            <p>Beschrijf de klus, stuur maximaal 5 foto's mee en kies wanneer het u uitkomt. U krijgt direct een bevestiging per e-mail.</p>
            <p><a class="btn btn-primary" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a></p>
        </aside>
    </div>
</section>
<?php view('layout/footer'); ?>
