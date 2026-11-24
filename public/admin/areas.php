<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$errors = [];
$form = ['town' => '', 'distance_km' => ''];

if (is_post()) {
    $action = post_string('action');
    $id = post_int('id');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM service_areas WHERE id = ?')->execute([$id]);
        flash('success', 'Plaats verwijderd uit het werkgebied.');
        redirect('admin/areas.php');
    }

    if ($action === 'save') {
        $form = ['town' => post_string('town'), 'distance_km' => post_string('distance_km')];
        $v = new Validator();
        $v->required('town', $form['town'], 'Plaats')->length('town', $form['town'], 2, 80, 'Plaats')
          ->pattern('town', $form['town'], "/^[\p{L} '.-]+$/u", 'Een plaatsnaam mag alleen letters, spaties, punten, streepjes en apostroffen bevatten.')
          ->required('distance_km', $form['distance_km'], 'Afstand')
          ->pattern('distance_km', $form['distance_km'], '/^\d{1,3}$/', 'Vul de afstand in hele kilometers in (0 tot 200).');
        if ((int) $form['distance_km'] > 200) {
            $v->add('distance_km', 'Vul de afstand in hele kilometers in (0 tot 200).');
        }
        $dupe = $pdo->prepare('SELECT COUNT(*) FROM service_areas WHERE town = ? AND id <> ?');
        $dupe->execute([$form['town'], $id]);
        if ((int) $dupe->fetchColumn() > 0) {
            $v->add('town', 'Deze plaats staat al in het werkgebied.');
        }
        if ($v->fails()) {
            $errors = $v->errors();
        } else {
            if ($id > 0) {
                $pdo->prepare('UPDATE service_areas SET town = ?, distance_km = ? WHERE id = ?')->execute([$form['town'], (int) $form['distance_km'], $id]);
            } else {
                $pdo->prepare('INSERT INTO service_areas (town, distance_km) VALUES (?, ?)')->execute([$form['town'], (int) $form['distance_km']]);
            }
            flash('success', $form['town'] . ' opgeslagen.');
            redirect('admin/areas.php');
        }
    }
}

$editId = get_int('edit');
$areas = Content::areas();

view('admin/header', ['title' => 'Werkgebied', 'active' => 'areas.php']);
?>
<div class="admin-head">
    <div><h1>Werkgebied</h1><p>Deze plaatsen staan op de duimstok op de website, op volgorde van afstand.</p></div>
</div>

<div class="admin-grid">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th scope="col">Plaats</th><th scope="col">Afstand</th><th scope="col"><span class="sr-only">Acties</span></th></tr></thead>
            <tbody>
            <?php foreach ($areas as $a): ?>
                <tr>
                    <?php if ($editId === (int) $a['id']): ?>
                        <td colspan="3">
                            <form class="inline-form" method="post" action="<?= e(url('admin/areas.php')) ?>">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                <input type="hidden" name="action" value="save">
                                <div class="field"><label for="edit-town">Plaats</label><input type="text" id="edit-town" name="town" value="<?= e($a['town']) ?>" maxlength="80" required></div>
                                <div class="field"><label for="edit-km">Km</label><input type="number" id="edit-km" name="distance_km" value="<?= (int) $a['distance_km'] ?>" min="0" max="200" required></div>
                                <button class="btn btn-primary" type="submit">Opslaan</button>
                                <a href="<?= e(url('admin/areas.php')) ?>">Annuleren</a>
                            </form>
                        </td>
                    <?php else: ?>
                        <td><strong><?= e($a['town']) ?></strong></td>
                        <td><?= (int) $a['distance_km'] ?> km</td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-ghost btn-small" href="<?= e(url('admin/areas.php?edit=' . (int) $a['id'])) ?>">Bewerken</a>
                                <form method="post" action="<?= e(url('admin/areas.php')) ?>" data-confirm="<?= e($a['town']) ?> uit het werkgebied halen?">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn btn-danger btn-small" type="submit">Verwijderen</button>
                                </form>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (!$areas): ?><tr><td colspan="3" class="empty-row">Nog geen plaatsen.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <section class="panel">
        <h2>Plaats toevoegen</h2>
        <?php view('admin/form-errors', ['errors' => $errors]); ?>
        <form class="form" method="post" action="<?= e(url('admin/areas.php')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="action" value="save">
            <div class="field"><label for="town">Plaats</label><input type="text" id="town" name="town" value="<?= e($form['town']) ?>" maxlength="80" required></div>
            <div class="field"><label for="distance_km">Afstand vanaf de werkplaats (km)</label><input type="number" id="distance_km" name="distance_km" value="<?= e($form['distance_km']) ?>" min="0" max="200" required></div>
            <p><button class="btn btn-primary" type="submit">Toevoegen</button></p>
        </form>
    </section>
</div>
<?php view('admin/footer'); ?>
