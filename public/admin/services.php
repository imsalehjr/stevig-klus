<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$icons = ['house' => 'Huis', 'brush' => 'Kwast', 'grid' => 'Tegels', 'saw' => 'Zaag', 'wrench' => 'Moersleutel', 'tools' => 'Gereedschap'];
$errors = [];

if (is_post()) {
    $action = post_string('action');
    $id = post_int('id');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
        flash('success', 'Dienst verwijderd. Bestaande aanvragen houden hun dienstnaam.');
        redirect('admin/services.php');
    }

    $form = [
        'name' => post_string('name'),
        'slug' => slugify(post_string('slug') !== '' ? post_string('slug') : post_string('name')),
        'icon' => post_string('icon'),
        'intro' => post_string('intro'),
        'description' => post_string('description'),
        'sort_order' => post_int('sort_order'),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    $v = new Validator();
    $v->required('name', $form['name'], 'Naam')->length('name', $form['name'], 2, 80, 'Naam')
      ->in('icon', $form['icon'], array_keys($icons), 'Kies een icoon.')
      ->required('intro', $form['intro'], 'Korte intro')->length('intro', $form['intro'], 10, 255, 'Korte intro')
      ->required('description', $form['description'], 'Omschrijving')->length('description', $form['description'], 20, 5000, 'Omschrijving');
    $dupe = $pdo->prepare('SELECT COUNT(*) FROM services WHERE slug = ? AND id <> ?');
    $dupe->execute([$form['slug'], $id]);
    if ((int) $dupe->fetchColumn() > 0) {
        $v->add('slug', 'Deze URL-naam is al in gebruik.');
    }

    if ($v->fails()) {
        $errors = $v->errors();
        $editing = $form + ['id' => $id];
    } else {
        $values = [$form['name'], $form['slug'], $form['icon'], $form['intro'], $form['description'], min($form['sort_order'], 9999), $form['is_active']];
        if ($id > 0) {
            $pdo->prepare('UPDATE services SET name = ?, slug = ?, icon = ?, intro = ?, description = ?, sort_order = ?, is_active = ? WHERE id = ?')
                ->execute([...$values, $id]);
            flash('success', 'Dienst "' . $form['name'] . '" opgeslagen.');
        } else {
            $pdo->prepare('INSERT INTO services (name, slug, icon, intro, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute($values);
            flash('success', 'Dienst "' . $form['name'] . '" toegevoegd.');
        }
        redirect('admin/services.php');
    }
}

if (!isset($editing)) {
    $editing = null;
    if (get_int('edit') > 0) {
        $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([get_int('edit')]);
        $editing = $stmt->fetch() ?: null;
    } elseif (isset($_GET['new'])) {
        $editing = ['id' => 0, 'name' => '', 'slug' => '', 'icon' => 'tools', 'intro' => '', 'description' => '', 'sort_order' => 100, 'is_active' => 1];
    }
}

$services = $pdo->query(
    'SELECT s.*, (SELECT COUNT(*) FROM projects p WHERE p.service_id = s.id) AS project_count,
            (SELECT COUNT(*) FROM quote_requests q WHERE q.service_id = s.id) AS quote_count
     FROM services s ORDER BY s.sort_order, s.name'
)->fetchAll();

view('admin/header', ['title' => 'Diensten', 'active' => 'services.php']);
?>
<div class="admin-head">
    <div><h1>Diensten</h1><p>Deze diensten staan op de website en in het offerteformulier.</p></div>
    <a class="btn btn-primary" href="<?= e(url('admin/services.php?new=1')) ?>">Dienst toevoegen</a>
</div>

<?php if ($editing !== null): ?>
    <section class="panel">
        <h2><?= $editing['id'] ? 'Dienst bewerken' : 'Nieuwe dienst' ?></h2>
        <?php view('admin/form-errors', ['errors' => $errors]); ?>
        <form class="form" method="post" action="<?= e(url('admin/services.php')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <input type="hidden" name="action" value="save">
            <div class="field-row">
                <div class="field"><label for="name">Naam</label><input type="text" id="name" name="name" value="<?= e($editing['name']) ?>" maxlength="80" required></div>
                <div class="field"><label for="slug">URL-naam</label><span class="hint">Leeg laten om automatisch te maken.</span><input type="text" id="slug" name="slug" value="<?= e($editing['slug']) ?>" maxlength="80" pattern="[a-z0-9-]*"></div>
            </div>
            <div class="field-row">
                <div class="field"><label for="icon">Icoon</label>
                    <select id="icon" name="icon"><?php foreach ($icons as $key => $label): ?><option value="<?= e($key) ?>"<?= $editing['icon'] === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
                </div>
                <div class="field"><label for="sort_order">Volgorde</label><input type="number" id="sort_order" name="sort_order" value="<?= (int) $editing['sort_order'] ?>" min="0" max="9999"></div>
            </div>
            <div class="field"><label for="intro">Korte intro</label><input type="text" id="intro" name="intro" value="<?= e($editing['intro']) ?>" maxlength="255" required></div>
            <div class="field"><label for="description">Omschrijving</label><textarea id="description" name="description" rows="6" maxlength="5000" required><?= e($editing['description']) ?></textarea></div>
            <label class="toggle"><input type="checkbox" name="is_active" value="1"<?= $editing['is_active'] ? ' checked' : '' ?>> Zichtbaar op de website</label>
            <p><button class="btn btn-primary" type="submit">Opslaan</button> <a href="<?= e(url('admin/services.php')) ?>">Annuleren</a></p>
        </form>
    </section>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col">Volgorde</th><th scope="col">Naam</th><th scope="col">Zichtbaar</th><th scope="col">Projecten</th><th scope="col">Aanvragen</th><th scope="col"><span class="sr-only">Acties</span></th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td><?= (int) $s['sort_order'] ?></td>
                <td><strong><?= e($s['name']) ?></strong><br><small class="muted">/diensten#<?= e($s['slug']) ?></small></td>
                <td><?= $s['is_active'] ? 'Ja' : 'Nee' ?></td>
                <td><?= (int) $s['project_count'] ?></td>
                <td><?= (int) $s['quote_count'] ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-ghost btn-small" href="<?= e(url('admin/services.php?edit=' . (int) $s['id'])) ?>">Bewerken</a>
                        <form method="post" data-confirm="Dienst &quot;<?= e($s['name']) ?>&quot; verwijderen? Projecten en reviews blijven bestaan zonder dienst.">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-danger btn-small" type="submit">Verwijderen</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$services): ?><tr><td colspan="6" class="empty-row">Nog geen diensten.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php view('admin/footer'); ?>
