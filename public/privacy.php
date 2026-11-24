<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$c = company();

view('layout/header', [
    'title' => 'Privacyverklaring',
    'active' => 'privacy',
    'description' => 'Hoe Stevig Klus & Renovatie omgaat met uw persoonsgegevens en foto\'s.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Privacyverklaring</h1>
        <p class="lead">Wij gebruiken uw gegevens alleen om uw klus te kunnen beoordelen en uitvoeren. Hieronder leest u precies wat we bewaren, waarom en hoe lang.</p>
    </div>
</section>
<section class="section section-white">
    <div class="container prose">
        <p class="muted">Laatst bijgewerkt: 25 november 2026</p>

        <h2>Wie zijn wij?</h2>
        <p><?= e($c['name']) ?>, <?= e($c['street']) ?>, <?= e($c['postcode'] . ' ' . $c['town']) ?>, is verantwoordelijk voor de verwerking van uw persoonsgegevens. Vragen? Mail naar <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>.</p>

        <h2>Welke gegevens verwerken we?</h2>
        <p>Als u een offerte aanvraagt, verwerken we:</p>
        <ul>
            <li>uw naam, e-mailadres en telefoonnummer, om contact met u op te nemen;</li>
            <li>het adres van de klus, om langs te komen en de reisafstand te bepalen;</li>
            <li>de omschrijving van de klus, de gewenste periode en eventuele foto's.</li>
        </ul>
        <p>De grondslag is het nemen van stappen op uw verzoek voordat er een overeenkomst is (artikel 6 lid 1 sub b AVG).</p>

        <h2>Uw foto's</h2>
        <p>Foto's die u uploadt, zetten we op onze server om naar een nieuw bestand. Daarbij verdwijnen alle verborgen gegevens die een camera of telefoon toevoegt, zoals GPS-locatie, datum en toestelmodel. Uw foto's zijn niet openbaar: alleen ingelogde medewerkers kunnen ze bekijken.</p>
        <p>We gebruiken uw foto's nooit op onze website zonder uw uitdrukkelijke, aparte toestemming.</p>

        <h2>Hoe lang bewaren we uw gegevens?</h2>
        <ul>
            <li>Aanvragen die niet tot een opdracht leiden: maximaal 12 maanden na het laatste contactmoment.</li>
            <li>Aanvragen die wel tot een opdracht leiden: zolang de garantieperiode loopt en daarna volgens de wettelijke bewaarplicht voor de administratie (7 jaar).</li>
        </ul>

        <h2>Beveiliging</h2>
        <p>We nemen passende maatregelen om misbruik te voorkomen. Formulieren zijn beveiligd tegen misbruik door andere websites, wachtwoorden zijn versleuteld opgeslagen en het aantal aanvragen per bezoeker is beperkt. Uw IP-adres slaan we niet leesbaar op, maar alleen als onomkeerbare code, en die verwijderen we na 24 uur.</p>

        <h2>Cookies</h2>
        <p>Deze website gebruikt alleen één functionele cookie die nodig is om formulieren veilig te versturen. Die verdwijnt als u de browser sluit. We gebruiken geen tracking- of advertentiecookies en geen externe diensten zoals Google Analytics of Google Fonts.</p>

        <h2>Delen met anderen</h2>
        <p>We verkopen uw gegevens nooit. We delen ze alleen met partijen die nodig zijn om onze dienst te leveren, zoals onze hostingpartij en e-mailprovider. Met hen hebben we een verwerkersovereenkomst.</p>

        <h2>Uw rechten</h2>
        <p>U mag uw gegevens inzien, laten corrigeren of laten verwijderen, en u kunt bezwaar maken tegen de verwerking. Stuur daarvoor een e-mail naar <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a>. We reageren binnen vier weken. Bent u niet tevreden over hoe wij met uw gegevens omgaan? Dan kunt u een klacht indienen bij de Autoriteit Persoonsgegevens.</p>
    </div>
</section>
<?php view('layout/footer'); ?>
