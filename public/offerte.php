<?php

require dirname(__DIR__) . '/src/bootstrap.php';

$services = Content::services();
$periods = quote_periods();
$maxFiles = Env::int('UPLOAD_MAX_FILES', 5);
$maxMb = Env::int('UPLOAD_MAX_MB', 5);
$maxTotalMb = Env::int('UPLOAD_MAX_TOTAL_MB', 20);
$limitPerHour = Env::int('QUOTE_LIMIT_PER_HOUR', 5);

$fields = ['name', 'email', 'phone', 'street', 'house_number', 'postcode', 'town', 'service', 'description', 'preferred_period'];
$old = array_fill_keys($fields, '');
$old['service'] = get_string('dienst');
$errors = [];
$formError = '';

if (is_post()) {
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($_POST === [] && $contentLength > 0) {
        $formError = "Uw foto's zijn samen te groot om te versturen. Kies minder of kleinere foto's (maximaal $maxTotalMb MB samen).";
    } else {
        Csrf::verify();
        Throttle::prune();
        ImageUpload::cleanupTmp();

        foreach ($fields as $f) {
            $old[$f] = post_string($f);
        }
        $old['email'] = mb_strtolower($old['email']);
        $old['postcode'] = strtoupper(preg_replace('/\s+/', '', $old['postcode']) ?? '');
        if (strlen($old['postcode']) === 6) {
            $old['postcode'] = substr($old['postcode'], 0, 4) . ' ' . substr($old['postcode'], 4);
        }

        $startedAt = (int) ($_SESSION['quote_form_ts'] ?? 0);
        $isBot = post_string('website') !== '' || $startedAt === 0 || (time() - $startedAt) < 3;

        if ($isBot) {
            Throttle::hit('quote_attempt');
            $_SESSION['quote_done'] = ['reference' => null];
            redirect('offerte-bedankt');
        }

        if (Throttle::tooMany('quote_attempt', 30, 60) || Throttle::tooMany('quote', $limitPerHour, 60)) {
            $formError = 'U heeft het afgelopen uur al meerdere aanvragen verstuurd. Probeer het later opnieuw of bel ons.';
        } else {
            Throttle::hit('quote_attempt');

            $serviceRow = null;
            foreach ($services as $s) {
                if ($s['slug'] === $old['service']) {
                    $serviceRow = $s;
                }
            }

            $v = new Validator();
            $v->required('name', $old['name'], 'Naam')->length('name', $old['name'], 2, 100, 'Naam')
              ->required('email', $old['email'], 'E-mailadres')->length('email', $old['email'], 1, 190, 'E-mailadres')->email('email', $old['email'])
              ->required('phone', $old['phone'], 'Telefoonnummer')->phone('phone', $old['phone'])
              ->required('street', $old['street'], 'Straat')->length('street', $old['street'], 2, 120, 'Straat')
              ->required('house_number', $old['house_number'], 'Huisnummer')
              ->pattern('house_number', $old['house_number'], '/^[1-9][0-9]{0,4}(?:[ -]?[A-Za-z0-9]{1,4})?$/', 'Vul een geldig huisnummer in, bijvoorbeeld 12 of 12a.')
              ->required('postcode', $old['postcode'], 'Postcode')->postcode('postcode', $old['postcode'])
              ->required('town', $old['town'], 'Plaats')->length('town', $old['town'], 2, 80, 'Plaats')
              ->required('description', $old['description'], 'Omschrijving')->length('description', $old['description'], 20, 3000, 'Omschrijving')
              ->in('preferred_period', $old['preferred_period'], array_keys($periods), 'Kies wanneer de klus uitgevoerd moet worden.');
            if ($serviceRow === null) {
                $v->add('service', 'Kies het soort klus.');
            }
            if (($_POST['privacy'] ?? '') !== '1') {
                $v->add('privacy', 'Geef toestemming om uw gegevens te gebruiken voor deze aanvraag.');
            }

            $files = ImageUpload::files('photos');
            if (count($files) > $maxFiles) {
                $v->add('photos', "U kunt maximaal $maxFiles foto's meesturen.");
            }
            $totalBytes = array_sum(array_column($files, 'size'));
            if ($totalBytes > $maxTotalMb * 1048576) {
                $v->add('photos', "Uw foto's zijn samen groter dan $maxTotalMb MB.");
            }

            $processed = [];
            if (!$v->fails()) {
                foreach ($files as $file) {
                    try {
                        $processed[] = ImageUpload::process($file, $maxMb * 1048576);
                    } catch (UploadException $ex) {
                        $v->add('photos', $ex->getMessage());
                        break;
                    }
                }
            }

            if ($v->fails()) {
                foreach ($processed as $p) {
                    ImageUpload::discard($p);
                }
                $errors = $v->errors();
            } else {
                $data = [
                    'name' => $old['name'],
                    'email' => $old['email'],
                    'phone' => $old['phone'],
                    'street' => $old['street'],
                    'house_number' => $old['house_number'],
                    'postcode' => $old['postcode'],
                    'town' => $old['town'],
                    'service_id' => (int) $serviceRow['id'],
                    'service_name' => $serviceRow['name'],
                    'description' => $old['description'],
                    'preferred_period' => $old['preferred_period'],
                ];
                $result = QuoteRequestService::create($data, $processed);
                Throttle::hit('quote');

                $quote = QuoteRequestService::find($result['id']);
                Mailer::quoteConfirmation($quote, $result['photo_count']);
                Mailer::quoteNotification($quote, $result['photo_count']);

                unset($_SESSION['quote_form_ts']);
                $_SESSION['quote_done'] = [
                    'reference' => $result['reference'],
                    'email' => $quote['email'],
                    'photos' => $result['photo_count'],
                ];
                redirect('offerte-bedankt');
            }
        }
    }
}

if (!is_post() || !isset($_SESSION['quote_form_ts'])) {
    $_SESSION['quote_form_ts'] = time();
}

$labels = [
    'name' => 'Naam', 'email' => 'E-mailadres', 'phone' => 'Telefoonnummer', 'street' => 'Straat',
    'house_number' => 'Huisnummer', 'postcode' => 'Postcode', 'town' => 'Plaats', 'service' => 'Soort klus',
    'description' => 'Omschrijving', 'preferred_period' => 'Gewenste periode', 'photos' => "Foto's", 'privacy' => 'Toestemming',
];
$err = static fn (string $f): string => isset($errors[$f]) ? ' aria-invalid="true" aria-describedby="' . $f . '-error"' : '';
$errMsg = static fn (string $f): string => isset($errors[$f]) ? '<p class="field-error" id="' . $f . '-error">' . e($errors[$f]) . '</p>' : '';

view('layout/header', [
    'title' => 'Offerte aanvragen',
    'active' => 'offerte',
    'description' => 'Vraag gratis en vrijblijvend een offerte aan bij Stevig Klus & Renovatie. Stuur foto\'s mee voor een snelle inschatting.',
]);
?>
<section class="page-head">
    <div class="container">
        <h1>Offerte aanvragen</h1>
        <p class="lead">Gratis en vrijblijvend. Hoe meer u vertelt, hoe beter onze eerste inschatting. Binnen 2 werkdagen hoort u van ons.</p>
    </div>
</section>
<section class="section">
    <div class="container split">
        <div>
            <?php if ($formError !== ''): ?>
                <div class="alert alert-error" role="alert"><?= e($formError) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="error-summary" role="alert" tabindex="-1" id="error-summary">
                    <h2>Controleer de volgende <?= count($errors) === 1 ? 'vraag' : count($errors) . ' vragen' ?></h2>
                    <ul>
                        <?php foreach ($errors as $field => $message): ?>
                            <li><a href="#<?= e($field === 'service' ? 'service-' . ($services[0]['slug'] ?? '') : ($field === 'preferred_period' ? 'period-asap' : $field)) ?>"><?= e($labels[$field] ?? $field) ?>: <?= e($message) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (isset($errors['photos']) || ImageUpload::files('photos')): ?>
                        <p>Kies uw foto's opnieuw voordat u verstuurt.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form class="form" method="post" action="<?= e(url('offerte')) ?>" enctype="multipart/form-data" data-submit-once>
                <?= Csrf::field() ?>
                <div class="hp-field" aria-hidden="true">
                    <label for="website">Laat dit veld leeg</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <fieldset>
                    <legend>Wat voor klus is het?</legend>
                    <div class="choice-grid"<?= isset($errors['service']) ? ' aria-describedby="service-error"' : '' ?>>
                        <?php foreach ($services as $s): ?>
                            <label class="choice">
                                <input type="radio" name="service" id="service-<?= e($s['slug']) ?>" value="<?= e($s['slug']) ?>"<?= $old['service'] === $s['slug'] ? ' checked' : '' ?> required>
                                <span><?php view('partials/icon', ['name' => $s['icon']]); ?><?= e($s['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= $errMsg('service') ?>

                    <div class="field">
                        <label for="description">Omschrijving van de klus</label>
                        <span class="hint" id="description-hint">Bijvoorbeeld afmetingen, materialen en wat er nu niet goed is. Minimaal 20 tekens.</span>
                        <textarea id="description" name="description" rows="6" maxlength="3000" required aria-describedby="description-hint"<?= $err('description') ?>><?= e($old['description']) ?></textarea>
                        <?= $errMsg('description') ?>
                    </div>

                    <div class="field">
                        <span class="label" id="period-label">Wanneer moet het gebeuren?</span>
                        <div class="choice-grid" role="radiogroup" aria-labelledby="period-label">
                            <?php foreach ($periods as $key => $label): ?>
                                <label class="choice">
                                    <input type="radio" name="preferred_period" id="period-<?= e($key) ?>" value="<?= e($key) ?>"<?= $old['preferred_period'] === $key ? ' checked' : '' ?> required>
                                    <span><?= e($label) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?= $errMsg('preferred_period') ?>
                    </div>

                    <div class="field">
                        <label for="photos">Foto's (optioneel)</label>
                        <div class="dropzone" data-dropzone data-max-files="<?= $maxFiles ?>" data-max-mb="<?= $maxMb ?>">
                            <?php view('partials/icon', ['name' => 'camera']); ?>
                            <span class="hint" id="photos-hint">Maximaal <?= $maxFiles ?> foto's, JPG, PNG of WebP, tot <?= $maxMb ?> MB per foto. Locatiegegevens in uw foto's halen we automatisch weg.</span>
                            <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple aria-describedby="photos-hint"<?= isset($errors['photos']) ? ' aria-invalid="true"' : '' ?>>
                            <ul class="file-previews" aria-live="polite"></ul>
                        </div>
                        <?= $errMsg('photos') ?>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Uw gegevens</legend>
                    <div class="field">
                        <label for="name">Naam</label>
                        <input type="text" id="name" name="name" value="<?= e($old['name']) ?>" maxlength="100" autocomplete="name" required<?= $err('name') ?>>
                        <?= $errMsg('name') ?>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="email">E-mailadres</label>
                            <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" maxlength="190" autocomplete="email" required<?= $err('email') ?>>
                            <?= $errMsg('email') ?>
                        </div>
                        <div class="field">
                            <label for="phone">Telefoonnummer</label>
                            <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" maxlength="20" autocomplete="tel" required<?= $err('phone') ?>>
                            <?= $errMsg('phone') ?>
                        </div>
                    </div>
                    <div class="field-row field-row-address">
                        <div class="field">
                            <label for="street">Straat</label>
                            <input type="text" id="street" name="street" value="<?= e($old['street']) ?>" maxlength="120" autocomplete="address-line1" required<?= $err('street') ?>>
                            <?= $errMsg('street') ?>
                        </div>
                        <div class="field">
                            <label for="house_number">Huisnummer</label>
                            <input type="text" id="house_number" name="house_number" value="<?= e($old['house_number']) ?>" maxlength="10" required<?= $err('house_number') ?>>
                            <?= $errMsg('house_number') ?>
                        </div>
                    </div>
                    <div class="field-row">
                        <div class="field">
                            <label for="postcode">Postcode</label>
                            <input type="text" id="postcode" name="postcode" value="<?= e($old['postcode']) ?>" maxlength="7" autocomplete="postal-code" required<?= $err('postcode') ?>>
                            <?= $errMsg('postcode') ?>
                        </div>
                        <div class="field">
                            <label for="town">Plaats</label>
                            <input type="text" id="town" name="town" value="<?= e($old['town']) ?>" maxlength="80" autocomplete="address-level2" required<?= $err('town') ?>>
                            <?= $errMsg('town') ?>
                        </div>
                    </div>
                    <div class="field">
                        <label class="checkbox" for="privacy">
                            <input type="checkbox" id="privacy" name="privacy" value="1" required<?= isset($errors['privacy']) ? ' aria-invalid="true" aria-describedby="privacy-error"' : '' ?>>
                            <span>Ik geef toestemming om mijn gegevens en foto's te gebruiken voor het beoordelen van deze aanvraag. Lees de <a href="<?= e(url('privacy')) ?>">privacyverklaring</a>.</span>
                        </label>
                        <?= $errMsg('privacy') ?>
                    </div>
                </fieldset>

                <p><button class="btn btn-primary" type="submit">Aanvraag versturen</button></p>
            </form>
        </div>
        <aside class="panel">
            <h2>Wat gebeurt er hierna?</h2>
            <ul class="check-list">
                <li><?php view('partials/icon', ['name' => 'mail']); ?><span>U krijgt direct een bevestiging met een referentienummer per e-mail.</span></li>
                <li><?php view('partials/icon', ['name' => 'phone']); ?><span>Binnen 2 werkdagen nemen we contact met u op.</span></li>
                <li><?php view('partials/icon', ['name' => 'house']); ?><span>Bij grotere klussen komen we gratis langs voor een inmeting.</span></li>
                <li><?php view('partials/icon', ['name' => 'shield']); ?><span>Uw foto's zijn alleen zichtbaar voor ons team.</span></li>
            </ul>
        </aside>
    </div>
</section>
<?php view('layout/footer'); ?>
