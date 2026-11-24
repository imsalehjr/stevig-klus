<?php

require dirname(__DIR__) . '/src/bootstrap.php';

view('layout/header', [
    'title' => 'Over ons',
    'active' => 'over-ons',
    'description' => 'Stevig Klus & Renovatie is een klein familiebedrijf uit Eikenrode met drie vakmensen.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Een klein team dat doet wat het zegt</h1>
        <p class="lead">Stevig is in 2011 begonnen met één bus en een gereedschapskist. Inmiddels werken we met drie vaste vakmensen, nog steeds vanuit Eikenrode.</p>
    </div>
</section>
<section class="section section-white">
    <div class="container split">
        <div class="prose">
            <h2>Hoe het begon</h2>
            <p>Oprichter Ruud werkte vijftien jaar bij een grote aannemer. Hij zag hoe klanten soms weken moesten wachten op een tegelzetter, terwijl de schilder al klaarstond. Dat kon beter, vond hij. Daarom begon hij een bedrijf waarin één team alle vakken beheerst en zelf de planning maakt.</p>
            <h2>Waar we voor staan</h2>
            <ul class="check-list">
                <li><?php view('partials/icon', ['name' => 'check']); ?><span><strong>Vaste prijs.</strong> De offerte is de prijs. Meerwerk bespreken we altijd eerst met u.</span></li>
                <li><?php view('partials/icon', ['name' => 'check']); ?><span><strong>Eén aanspreekpunt.</strong> U belt met de persoon die ook bij u aan het werk is.</span></li>
                <li><?php view('partials/icon', ['name' => 'check']); ?><span><strong>Opgeruimd werken.</strong> Aan het eind van elke werkdag is de werkplek bezemschoon.</span></li>
                <li><?php view('partials/icon', ['name' => 'check']); ?><span><strong>Garantie.</strong> Twee jaar garantie op ons werk, schriftelijk vastgelegd.</span></li>
            </ul>
            <h2>Het team</h2>
            <p><strong>Ruud</strong> is eigenaar en timmerman. Hij doet de inmetingen en maakt de offertes. <strong>Sanne</strong> is schilder en specialist in houtrot-herstel. <strong>Mehmet</strong> is tegelzetter en doet het meeste badkamerwerk.</p>
        </div>
        <aside class="panel">
            <h2>In cijfers</h2>
            <ul class="area-list">
                <li>Opgericht <span>2011</span></li>
                <li>Vakmensen in vaste dienst <span>3</span></li>
                <li>Werkgebied <span>25 km rond Eikenrode</span></li>
                <li>Garantie op ons werk <span>2 jaar</span></li>
            </ul>
            <p><a class="btn btn-primary" href="<?= e(url('offerte')) ?>">Offerte aanvragen</a></p>
        </aside>
    </div>
</section>
<?php view('layout/footer'); ?>
