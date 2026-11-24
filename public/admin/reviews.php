<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$services = Content::services(false);
$serviceIds = array_map(static fn ($s) => (int) $s['id'], $services);
$projects = $pdo->query('SELECT id, title, town FROM projects ORDER BY completed_on DESC, id DESC')->fetchAll();
$projectIds = array_map(static fn ($p) => (int) $p['id'], $projects);
$errors = [];

if (is_post()) {
    $action = post_string('action');
    $id = post_int('id');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
        flash('success', 'Review verwijderd.');
        redirect('admin/reviews.php');
    }

    if ($action === 'toggle' && $id > 0) {
        $pdo->prepare('UPDATE reviews SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
        flash('success', 'Zichtbaarheid aangepast.');
        redirect('admin/reviews.php');
    }

    $form = [
        'customer_name' => post_string('customer_name'),
        'town' => post_string('town'),
        'rating' => post_int('rating'),
        'body' => post_string('body'),
        'service_id' => post_int('service_id'),
        'project_id' => post_int('project_id'),
        'reviewed_on' => post_string('reviewed_on'),
        'is_published' => isset($_POST['is_published']) ? 1 : 0,
    ];
    $v = new Validator();
    $v->required('customer_name', $form['customer_name'], 'Naam')->length('customer_name', $form['customer_name'], 2, 80, 'Naam')
      ->required('town', $form['town'], 'Plaats')->length('town', $form['town'], 2, 80, 'Plaats')
      ->in('rating', (string) $form['rating'], ['1', '2', '3', '4', '5'], 'Kies 1 tot 5 sterren.')
      ->required('body', $form['body'], 'Review')->length('body', $form['body'], 10, 2000, 'Review')
      ->required('reviewed_on', $form['reviewed_on'], 'Datum')->date('reviewed_on', $form['reviewed_on'], 'Datum');
    if ($form['service_id'] !== 0 && !in_array($form['service_id'], $serviceIds, true)) {
        $v->add('service_id', 'Kies een bestaande dienst.');
    }
    if ($form['project_id'] !== 0 && !in_array($form['project_id'], $projectIds, true)) {
        $v->add('project_id', 'Kies een bestaand project.');
    }
    if ($form['reviewed_on'] !== '' && $form['reviewed_on'] > date('Y-m-d')) {
        $v->add('reviewed_on', 'De datum mag niet in de toekomst liggen.');
    }

    if ($v->fails()) {
        $errors = $v->errors();
        $editing = $form + ['id' => $id];
    } else {
        $values = [$form['customer_name'], $form['town'], $form['rating'], $form['body'], $form['service_id'] ?: null, $form['project_id'] ?: null, $form['reviewed_on'], $form['is_published']];
        if ($id > 0) {
            $pdo->prepare('UPDATE reviews SET customer_name = ?, town = ?, rating = ?, body = ?, service_id = ?, project_id = ?, reviewed_on = ?, is_published = ? WHERE id = ?')
                ->execute([...$values, $id]);
        } else {
            $pdo->prepare('INSERT INTO reviews (customer_name, town, rating, body, service_id, project_id, reviewed_on, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute($values);
        }
        flash('success', 'Review opgeslagen.');
        redirect('admin/reviews.php');
    }
}

if (!isset($editing)) {
    $editing = null;
    if (get_int('edit') > 0) {
        $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
        $stmt->execute([get_int('edit')]);
        $editing = $stmt->fetch() ?: null;
    } elseif (isset($_GET['new'])) {
        $editing = ['id' => 0, 'customer_name' => '', 'town' => '', 'rating' => 5, 'body' => '', 'service_id' => 0, 'project_id' => 0, 'reviewed_on' => date('Y-m-d'), 'is_published' => 1];
    }
}

$reviews = $pdo->query(
    'SELECT r.*, s.name AS service_name, p.title AS project_title FROM reviews r
     LEFT JOIN services s ON s.id = r.service_id LEFT JOIN projects p ON p.id = r.project_id
     ORDER BY r.reviewed_on DESC, r.id DESC'
)->fetchAll();

view('admin/header', ['title' => 'Reviews', 'active' => 'reviews.php']);
?>
<div class="admin-head">
    <div><h1>Reviews</h1><p>Voeg alleen echte reviews van klanten toe, met hun toestemming.</p></div>
    <a class="btn btn-primary" href="<?= e(url('admin/reviews.php?new=1')) ?>">Review toevoegen</a>
</div>

<?php if ($editing !== null): ?>
    <section class="panel">
        <h2><?= $editing['id'] ? 'Review bewerken' : 'Nieuwe review' ?></h2>
        <?php view('admin/form-errors', ['errors' => $errors]); ?>
        <form class="form" method="post" action="<?= e(url('admin/reviews.php')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <input type="hidden" name="action" value="save">
            <div class="field-row">
                <div class="field"><label for="customer_name">Naam klant</label><span class="hint">Bijvoorbeeld alleen voornaam of "Familie J."</span><input type="text" id="customer_name" name="customer_name" value="<?= e($editing['customer_name']) ?>" maxlength="80" required></div>
                <div class="field"><label for="town">Plaats</label><input type="text" id="town" name="town" value="<?= e($editing['town']) ?>" maxlength="80" required></div>
            </div>
            <div class="field-row">
                <div class="field"><label for="rating">Sterren</label>
                    <select id="rating" name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"<?= (int) $editing['rating'] === $i ? ' selected' : '' ?>><?= $i ?> <?= stars($i) ?></option><?php endfor; ?></select>
                </div>
                <div class="field"><label for="reviewed_on">Datum</label><input type="date" id="reviewed_on" name="reviewed_on" value="<?= e($editing['reviewed_on']) ?>" max="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div class="field"><label for="body">Review</label><textarea id="body" name="body" rows="4" maxlength="2000" required><?= e($editing['body']) ?></textarea></div>
            <div class="field-row">
                <div class="field"><label for="service_id">Dienst</label>
                    <select id="service_id" name="service_id"><option value="0">Geen</option><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"<?= (int) $editing['service_id'] === (int) $s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="field"><label for="project_id">Project (optioneel)</label>
                    <select id="project_id" name="project_id"><option value="0">Geen</option><?php foreach ($projects as $p): ?><option value="<?= (int) $p['id'] ?>"<?= (int) $editing['project_id'] === (int) $p['id'] ? ' selected' : '' ?>><?= e($p['title'] . ', ' . $p['town']) ?></option><?php endforeach; ?></select>
                </div>
            </div>
            <label class="toggle"><input type="checkbox" name="is_published" value="1"<?= $editing['is_published'] ? ' checked' : '' ?>> Zichtbaar op de website</label>
            <p><button class="btn btn-primary" type="submit">Opslaan</button> <a href="<?= e(url('admin/reviews.php')) ?>">Annuleren</a></p>
        </form>
    </section>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col">Klant</th><th scope="col">Sterren</th><th scope="col">Review</th><th scope="col">Datum</th><th scope="col">Zichtbaar</th><th scope="col"><span class="sr-only">Acties</span></th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
            <tr>
                <td><strong><?= e($r['customer_name']) ?></strong><br><small class="muted"><?= e($r['town']) ?></small></td>
                <td class="nowrap"><span aria-label="<?= (int) $r['rating'] ?> sterren"><?= stars((int) $r['rating']) ?></span></td>
                <td><?= e(mb_strimwidth($r['body'], 0, 110, '…')) ?><?php if ($r['project_title']): ?><br><small class="muted">Project: <?= e($r['project_title']) ?></small><?php endif; ?></td>
                <td class="nowrap"><?= e(format_date($r['reviewed_on'])) ?></td>
                <td>
                    <form method="post" action="<?= e(url('admin/reviews.php')) ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                        <input type="hidden" name="action" value="toggle">
                        <button class="link-button link-dark" type="submit"><?= $r['is_published'] ? 'Ja' : 'Nee' ?><span class="sr-only">, wijzigen</span></button>
                    </form>
                </td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-ghost btn-small" href="<?= e(url('admin/reviews.php?edit=' . (int) $r['id'])) ?>">Bewerken</a>
                        <form method="post" action="<?= e(url('admin/reviews.php')) ?>" data-confirm="Review van <?= e($r['customer_name']) ?> verwijderen?">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-danger btn-small" type="submit">Verwijderen</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$reviews): ?><tr><td colspan="6" class="empty-row">Nog geen reviews.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php view('admin/footer'); ?>
