<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$statuses = quote_statuses();
$periods = quote_periods();
$id = is_post() ? post_int('id') : get_int('id');
$quote = QuoteRequestService::find($id);
if ($quote === null) {
    http_response_code(404);
    flash('error', 'Deze aanvraag bestaat niet (meer).');
    redirect('admin/requests.php');
}

if (is_post()) {
    $action = post_string('action');

    if ($action === 'status') {
        $new = post_string('status');
        if (QuoteRequestService::changeStatus($id, $new, Auth::id())) {
            flash('success', 'Status gewijzigd naar "' . $statuses[$new] . '".');
        } else {
            flash('info', 'De status is niet gewijzigd.');
        }
    } elseif ($action === 'note') {
        $body = post_string('body');
        if ($body === '' || mb_strlen($body) > 2000) {
            flash('error', 'Een notitie moet tussen 1 en 2000 tekens lang zijn.');
        } else {
            $pdo->prepare('INSERT INTO quote_request_notes (quote_request_id, admin_user_id, body) VALUES (?, ?, ?)')
                ->execute([$id, Auth::id(), $body]);
            flash('success', 'Notitie toegevoegd.');
        }
    } elseif ($action === 'delete' && post_string('confirm_reference') === $quote['reference']) {
        $files = $pdo->prepare('SELECT file FROM quote_request_photos WHERE quote_request_id = ?');
        $files->execute([$id]);
        $names = $files->fetchAll(PDO::FETCH_COLUMN);
        $pdo->prepare('DELETE FROM quote_requests WHERE id = ?')->execute([$id]);
        foreach ($names as $name) {
            if (ImageUpload::isSafeName($name)) {
                @unlink(BASE_PATH . '/storage/uploads/requests/' . $name);
            }
        }
        flash('success', 'Aanvraag ' . $quote['reference'] . ' en ' . count($names) . " foto('s) zijn definitief verwijderd.");
        redirect('admin/requests.php');
    } elseif ($action === 'delete') {
        flash('error', 'Typ het referentienummer exact over om te verwijderen.');
    }
    redirect('admin/request.php?id=' . $id);
}

$photos = $pdo->prepare('SELECT * FROM quote_request_photos WHERE quote_request_id = ? ORDER BY id');
$photos->execute([$id]);
$photos = $photos->fetchAll();

$notes = $pdo->prepare(
    'SELECT n.*, a.name AS admin_name FROM quote_request_notes n LEFT JOIN admin_users a ON a.id = n.admin_user_id
     WHERE n.quote_request_id = ? ORDER BY n.created_at DESC, n.id DESC'
);
$notes->execute([$id]);
$notes = $notes->fetchAll();

$log = $pdo->prepare(
    'SELECT l.*, a.name AS admin_name FROM quote_request_status_log l LEFT JOIN admin_users a ON a.id = l.admin_user_id
     WHERE l.quote_request_id = ? ORDER BY l.changed_at DESC, l.id DESC'
);
$log->execute([$id]);
$log = $log->fetchAll();

$mapsQuery = rawurlencode($quote['street'] . ' ' . $quote['house_number'] . ', ' . $quote['postcode'] . ' ' . $quote['town']);

view('admin/header', ['title' => $quote['reference'], 'active' => 'requests.php']);
?>
<p class="breadcrumb"><a href="<?= e(url('admin/requests.php')) ?>">Aanvragen</a> / <?= e($quote['reference']) ?></p>
<div class="admin-head">
    <div>
        <h1><?= e($quote['reference']) ?></h1>
        <p><?= e($quote['service_name']) ?> in <?= e($quote['town']) ?>, ontvangen op <?= e(format_date($quote['created_at'], true)) ?></p>
    </div>
    <span class="status status-<?= e($quote['status']) ?>"><?= e($statuses[$quote['status']]) ?></span>
</div>

<div class="admin-grid">
    <div>
        <section class="panel">
            <h2>Klus</h2>
            <dl class="detail-list">
                <dt>Soort klus</dt><dd><?= e($quote['service_name']) ?></dd>
                <dt>Gewenste periode</dt><dd><?= e($periods[$quote['preferred_period']] ?? '') ?></dd>
                <dt>Omschrijving</dt><dd><p class="description-box"><?= e($quote['description']) ?></p></dd>
            </dl>
        </section>

        <section class="panel">
            <h2>Foto's (<?= count($photos) ?>)</h2>
            <?php if ($photos): ?>
                <div class="photo-grid">
                    <?php foreach ($photos as $i => $photo): ?>
                        <figure>
                            <a href="<?= e(url('admin/photo.php?id=' . (int) $photo['id'])) ?>" target="_blank" rel="noopener">
                                <img src="<?= e(url('admin/photo.php?id=' . (int) $photo['id'])) ?>" alt="Foto <?= $i + 1 ?> bij <?= e($quote['reference']) ?>" width="<?= (int) $photo['width'] ?>" height="<?= (int) $photo['height'] ?>" loading="lazy">
                            </a>
                            <figcaption><span>Foto <?= $i + 1 ?></span><span class="muted"><?= (int) $photo['width'] ?>×<?= (int) $photo['height'] ?>, <?= number_format($photo['bytes'] / 1024, 0, ',', '.') ?> KB</span></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted">De klant heeft geen foto's meegestuurd.</p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h2>Interne notities</h2>
            <form class="form" method="post">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="note">
                <div class="field">
                    <label for="body">Nieuwe notitie</label>
                    <textarea id="body" name="body" rows="3" maxlength="2000" required placeholder="Bijvoorbeeld: gebeld, inmeting gepland op dinsdag 10:00"></textarea>
                </div>
                <p><button class="btn btn-dark btn-small" type="submit">Notitie opslaan</button></p>
            </form>
            <?php foreach ($notes as $note): ?>
                <div class="note">
                    <small><?= e($note['admin_name'] ?? 'Systeem') ?>, <?= e(format_date($note['created_at'], true)) ?></small>
                    <p><?= e($note['body']) ?></p>
                </div>
            <?php endforeach; ?>
            <?php if (!$notes): ?><p class="muted">Nog geen notities.</p><?php endif; ?>
        </section>
    </div>

    <aside>
        <section class="panel">
            <h2>Status</h2>
            <form class="inline-form" method="post">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="status">
                <div class="field">
                    <label class="sr-only" for="new-status">Nieuwe status</label>
                    <select id="new-status" name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= e($key) ?>"<?= $quote['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Wijzigen</button>
            </form>
            <h3>Geschiedenis</h3>
            <ol class="timeline">
                <?php foreach ($log as $entry): ?>
                    <li>
                        <?= $entry['old_status'] === null ? 'Aanvraag ontvangen' : e(($statuses[$entry['old_status']] ?? $entry['old_status']) . ' → ' . ($statuses[$entry['new_status']] ?? $entry['new_status'])) ?>
                        <time datetime="<?= e(date('c', strtotime($entry['changed_at']))) ?>"><?= e(format_date($entry['changed_at'], true)) ?><?= $entry['admin_name'] ? ' door ' . e($entry['admin_name']) : '' ?></time>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <section class="panel">
            <h2>Klant</h2>
            <dl class="detail-list">
                <dt>Naam</dt><dd><?= e($quote['name']) ?></dd>
                <dt>E-mail</dt><dd><a href="mailto:<?= e($quote['email']) ?>?subject=<?= e(rawurlencode('Uw aanvraag ' . $quote['reference'])) ?>"><?= e($quote['email']) ?></a></dd>
                <dt>Telefoon</dt><dd><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $quote['phone'])) ?>"><?= e($quote['phone']) ?></a></dd>
                <dt>Adres</dt><dd><?= e($quote['street'] . ' ' . $quote['house_number']) ?><br><?= e($quote['postcode'] . ' ' . $quote['town']) ?><br><a href="https://www.openstreetmap.org/search?query=<?= e($mapsQuery) ?>" target="_blank" rel="noopener noreferrer">Bekijk op kaart</a></dd>
                <dt>Toestemming</dt><dd><?= e(format_date($quote['privacy_consent_at'], true)) ?></dd>
            </dl>
        </section>

        <section class="panel">
            <h2>Verwijderen (AVG)</h2>
            <p class="muted">Verwijdert de aanvraag, alle foto's, notities en de geschiedenis definitief. Gebruik dit als een klant vraagt om zijn gegevens te wissen.</p>
            <form class="form" method="post" data-confirm="Weet u zeker dat u deze aanvraag definitief wilt verwijderen?">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="delete">
                <div class="field">
                    <label for="confirm_reference">Typ <strong><?= e($quote['reference']) ?></strong> om te bevestigen</label>
                    <input type="text" id="confirm_reference" name="confirm_reference" autocomplete="off" required>
                </div>
                <p><button class="btn btn-danger btn-small" type="submit">Definitief verwijderen</button></p>
            </form>
        </section>
    </aside>
</div>
<?php view('admin/footer'); ?>
