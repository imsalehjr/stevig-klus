<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$done = $_SESSION['quote_done'] ?? null;
if (!is_array($done)) {
    redirect('offerte');
}
unset($_SESSION['quote_done']);
header('Cache-Control: no-store');

view('layout/header', ['title' => 'Aanvraag ontvangen', 'active' => 'offerte']);
?>
<section class="section">
    <div class="container narrow">
        <div class="panel confirm-box">
            <h1>Bedankt, uw aanvraag is binnen</h1>
            <?php if (!empty($done['reference'])): ?>
                <p>Uw referentienummer is</p>
                <p class="reference"><?= e($done['reference']) ?></p>
                <p>We hebben een bevestiging gestuurd naar <strong><?= e($done['email']) ?></strong><?= (int) $done['photos'] > 0 ? ', met een overzicht van uw aanvraag. We hebben ' . (int) $done['photos'] . ' foto(\'s)' . ' ontvangen' : '' ?>. Geen e-mail ontvangen? Kijk dan even in uw map met ongewenste e-mail.</p>
            <?php else: ?>
                <p>We hebben uw aanvraag ontvangen en nemen zo snel mogelijk contact met u op.</p>
            <?php endif; ?>
            <h2>Wat gebeurt er nu?</h2>
            <ul class="check-list">
                <li><?php view('partials/icon', ['name' => 'phone']); ?><span>Binnen 2 werkdagen bellen of mailen we u om de klus te bespreken.</span></li>
                <li><?php view('partials/icon', ['name' => 'house']); ?><span>Bij grotere klussen plannen we een gratis inmeting bij u thuis.</span></li>
                <li><?php view('partials/icon', ['name' => 'check']); ?><span>Daarna ontvangt u een offerte met een vaste prijs.</span></li>
            </ul>
            <p><a class="btn btn-ghost" href="<?= e(url('projecten')) ?>">Bekijk intussen onze projecten</a></p>
        </div>
    </div>
</section>
<?php view('layout/footer'); ?>
