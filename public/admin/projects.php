<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$services = Content::services(false);
$serviceIds = array_map(static fn ($s) => (int) $s['id'], $services);
$uploadDir = BASE_PATH . '/public/uploads/projects';
$maxMb = Env::int('UPLOAD_MAX_MB', 5);
$maxFiles = Env::int('UPLOAD_MAX_FILES', 5);
$errors = [];

$deleteFiles = static function (array $names) use ($uploadDir): void {
    foreach ($names as $name) {
        if (ImageUpload::isSafeName($name)) {
            @unlink($uploadDir . '/' . $name);
        }
    }
};

if (is_post()) {
    $action = post_string('action');
    $id = post_int('id');

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('SELECT file FROM project_photos WHERE project_id = ?');
        $stmt->execute([$id]);
        $files = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $pdo->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        $deleteFiles($files);
        flash('success', "Project en " . count($files) . " foto('s) verwijderd.");
        redirect('admin/projects.php');
    }

    if ($action === 'delete_photo') {
        $photoId = post_int('photo_id');
        $stmt = $pdo->prepare('SELECT file FROM project_photos WHERE id = ? AND project_id = ?');
        $stmt->execute([$photoId, $id]);
        $file = $stmt->fetchColumn();
        if ($file !== false) {
            $pdo->prepare('DELETE FROM project_photos WHERE id = ?')->execute([$photoId]);
            $deleteFiles([$file]);
            flash('success', 'Foto verwijderd.');
        }
        redirect('admin/projects.php?edit=' . $id . '#fotos');
    }

    if ($action === 'upload' && $id > 0) {
        Throttle::prune();
        ImageUpload::cleanupTmp();
        $kind = post_string('kind') === 'after' ? 'after' : 'before';
        $alt = mb_substr(post_string('alt'), 0, 150);
        $files = ImageUpload::files('photos');
        $count = 0;
        if (!$files) {
            flash('error', 'Kies minimaal één foto.');
        } elseif (count($files) > $maxFiles) {
            flash('error', "Upload maximaal $maxFiles foto's tegelijk.");
        } else {
            $order = $pdo->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM project_photos WHERE project_id = ? AND kind = ?');
            $order->execute([$id, $kind]);
            $sort = (int) $order->fetchColumn();
            $insert = $pdo->prepare('INSERT INTO project_photos (project_id, kind, file, width, height, alt, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)');
            foreach ($files as $file) {
                try {
                    $processed = ImageUpload::process($file, $maxMb * 1048576);
                    $name = ImageUpload::store($processed, $uploadDir);
                    $insert->execute([$id, $kind, $name, $processed['width'], $processed['height'], $alt, $sort++]);
                    $count++;
                } catch (UploadException $ex) {
                    flash('error', $ex->getMessage());
                }
            }
            if ($count > 0) {
                flash('success', "$count foto('s) toegevoegd als " . ($kind === 'after' ? '"na"' : '"voor"') . '.');
            }
        }
        redirect('admin/projects.php?edit=' . $id . '#fotos');
    }

    if ($action === 'save') {
        $form = [
            'title' => post_string('title'),
            'slug' => slugify(post_string('slug') !== '' ? post_string('slug') : post_string('title') . ' ' . post_string('town')),
            'town' => post_string('town'),
            'service_id' => post_int('service_id'),
            'summary' => post_string('summary'),
            'description' => post_string('description'),
            'duration' => post_string('duration'),
            'completed_on' => post_string('completed_on'),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
        $v = new Validator();
        $v->required('title', $form['title'], 'Titel')->length('title', $form['title'], 3, 120, 'Titel')
          ->required('town', $form['town'], 'Plaats')->length('town', $form['town'], 2, 80, 'Plaats')
          ->required('summary', $form['summary'], 'Samenvatting')->length('summary', $form['summary'], 10, 255, 'Samenvatting')
          ->required('description', $form['description'], 'Omschrijving')->length('description', $form['description'], 20, 8000, 'Omschrijving')
          ->length('duration', $form['duration'], 1, 40, 'Duur')
          ->date('completed_on', $form['completed_on'], 'Opleverdatum');
        if ($form['service_id'] !== 0 && !in_array($form['service_id'], $serviceIds, true)) {
            $v->add('service_id', 'Kies een bestaande dienst.');
        }
        $dupe = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE slug = ? AND id <> ?');
        $dupe->execute([$form['slug'], $id]);
        if ((int) $dupe->fetchColumn() > 0) {
            $v->add('slug', 'Deze URL-naam is al in gebruik.');
        }

        if ($v->fails()) {
            $errors = $v->errors();
            $editing = $form + ['id' => $id];
        } else {
            $values = [
                $form['title'], $form['slug'], $form['town'], $form['service_id'] ?: null, $form['summary'], $form['description'],
                $form['duration'] ?: null, $form['completed_on'] ?: null, $form['is_featured'], $form['is_published'],
            ];
            if ($id > 0) {
                $pdo->prepare('UPDATE projects SET title = ?, slug = ?, town = ?, service_id = ?, summary = ?, description = ?, duration = ?, completed_on = ?, is_featured = ?, is_published = ? WHERE id = ?')
                    ->execute([...$values, $id]);
                flash('success', 'Project opgeslagen.');
            } else {
                $pdo->prepare('INSERT INTO projects (title, slug, town, service_id, summary, description, duration, completed_on, is_featured, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute($values);
                $id = (int) $pdo->lastInsertId();
                flash('success', "Project aangemaakt. Voeg nu de foto's van voor en na toe.");
            }
            redirect('admin/projects.php?edit=' . $id . ($id ? '#fotos' : ''));
        }
    }
}

if (!isset($editing)) {
    $editing = null;
    if (get_int('edit') > 0) {
        $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ?');
        $stmt->execute([get_int('edit')]);
        $editing = $stmt->fetch() ?: null;
    } elseif (isset($_GET['new'])) {
        $editing = ['id' => 0, 'title' => '', 'slug' => '', 'town' => '', 'service_id' => 0, 'summary' => '', 'description' => '', 'duration' => '', 'completed_on' => date('Y-m-d'), 'is_featured' => 0, 'is_published' => 1];
    }
}
$photos = $editing && $editing['id'] ? Content::photos((int) $editing['id']) : ['before' => [], 'after' => []];

$projects = $pdo->query(
    "SELECT p.*, s.name AS service_name,
        (SELECT COUNT(*) FROM project_photos f WHERE f.project_id = p.id AND f.kind = 'before') AS before_count,
        (SELECT COUNT(*) FROM project_photos f WHERE f.project_id = p.id AND f.kind = 'after') AS after_count,
        (SELECT file FROM project_photos f WHERE f.project_id = p.id AND f.kind = 'after' ORDER BY f.sort_order, f.id LIMIT 1) AS cover_file
     FROM projects p LEFT JOIN services s ON s.id = p.service_id ORDER BY p.completed_on DESC, p.id DESC"
)->fetchAll();

view('admin/header', ['title' => 'Projecten', 'active' => 'projects.php']);
?>
<div class="admin-head">
    <div><h1>Projecten</h1><p>Projecten met foto's van voor en na voor de website.</p></div>
    <a class="btn btn-primary" href="<?= e(url('admin/projects.php?new=1')) ?>">Project toevoegen</a>
</div>

<?php if ($editing !== null): ?>
    <section class="panel">
        <h2><?= $editing['id'] ? 'Project bewerken' : 'Nieuw project' ?></h2>
        <?php view('admin/form-errors', ['errors' => $errors]); ?>
        <form class="form" method="post" action="<?= e(url('admin/projects.php')) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
            <input type="hidden" name="action" value="save">
            <div class="field-row">
                <div class="field"><label for="title">Titel</label><input type="text" id="title" name="title" value="<?= e($editing['title']) ?>" maxlength="120" required></div>
                <div class="field"><label for="town">Plaats</label><input type="text" id="town" name="town" value="<?= e($editing['town']) ?>" maxlength="80" required></div>
            </div>
            <div class="field-row">
                <div class="field"><label for="service_id">Dienst</label>
                    <select id="service_id" name="service_id"><option value="0">Geen</option><?php foreach ($services as $s): ?><option value="<?= (int) $s['id'] ?>"<?= (int) $editing['service_id'] === (int) $s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="field"><label for="slug">URL-naam</label><span class="hint">Leeg laten om automatisch te maken.</span><input type="text" id="slug" name="slug" value="<?= e($editing['slug']) ?>" maxlength="90" pattern="[a-z0-9-]*"></div>
            </div>
            <div class="field"><label for="summary">Samenvatting (1 zin)</label><input type="text" id="summary" name="summary" value="<?= e($editing['summary']) ?>" maxlength="255" required></div>
            <div class="field"><label for="description">Omschrijving</label><span class="hint">Laat een witregel tussen alinea's.</span><textarea id="description" name="description" rows="7" maxlength="8000" required><?= e($editing['description']) ?></textarea></div>
            <div class="field-row">
                <div class="field"><label for="duration">Duur</label><input type="text" id="duration" name="duration" value="<?= e($editing['duration'] ?? '') ?>" maxlength="40" placeholder="Bijvoorbeeld 5 werkdagen"></div>
                <div class="field"><label for="completed_on">Opgeleverd op</label><input type="date" id="completed_on" name="completed_on" value="<?= e($editing['completed_on'] ?? '') ?>"></div>
            </div>
            <label class="toggle"><input type="checkbox" name="is_published" value="1"<?= $editing['is_published'] ? ' checked' : '' ?>> Zichtbaar op de website</label>
            <label class="toggle"><input type="checkbox" name="is_featured" value="1"<?= $editing['is_featured'] ? ' checked' : '' ?>> Uitlichten op de homepage</label>
            <p><button class="btn btn-primary" type="submit">Opslaan</button> <a href="<?= e(url('admin/projects.php')) ?>">Sluiten</a>
            <?php if ($editing['id']): ?> <a href="<?= e(url('projecten/' . $editing['slug'])) ?>" target="_blank" rel="noopener">Bekijk op website</a><?php endif; ?></p>
        </form>
    </section>

    <?php if ($editing['id']): ?>
        <section class="panel" id="fotos">
            <h2>Foto's</h2>
            <p class="muted">De eerste "voor"- en "na"-foto vormen samen de schuifvergelijking. Gebruik bij voorkeur foto's vanuit dezelfde hoek. Locatiegegevens worden automatisch verwijderd.</p>
            <form class="form" method="post" action="<?= e(url('admin/projects.php')) ?>" enctype="multipart/form-data">
                <?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                <input type="hidden" name="action" value="upload">
                <div class="field-row">
                    <div class="field"><label for="kind">Soort</label><select id="kind" name="kind"><option value="before">Voor</option><option value="after">Na</option></select></div>
                    <div class="field"><label for="alt">Beschrijving voor schermlezers</label><input type="text" id="alt" name="alt" maxlength="150" placeholder="Bijvoorbeeld: badkamer met inloopdouche"></div>
                </div>
                <div class="field"><label for="photos">Foto's (max. <?= $maxFiles ?>, JPG/PNG/WebP, tot <?= $maxMb ?> MB)</label><input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></div>
                <p><button class="btn btn-dark" type="submit">Uploaden</button></p>
            </form>
            <?php foreach (['before' => 'Voor', 'after' => 'Na'] as $kind => $label): ?>
                <h3><?= $label ?> (<?= count($photos[$kind]) ?>)</h3>
                <?php if ($photos[$kind]): ?>
                    <div class="photo-grid">
                        <?php foreach ($photos[$kind] as $photo): ?>
                            <figure>
                                <img src="<?= e(upload_url($photo['file'])) ?>" alt="<?= e($photo['alt']) ?>" width="<?= (int) $photo['width'] ?>" height="<?= (int) $photo['height'] ?>" loading="lazy">
                                <figcaption>
                                    <span class="muted"><?= e($photo['alt'] !== '' ? $photo['alt'] : 'Geen beschrijving') ?></span>
                                    <form method="post" action="<?= e(url('admin/projects.php')) ?>" data-confirm="Deze foto verwijderen?">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $editing['id'] ?>">
                                        <input type="hidden" name="photo_id" value="<?= (int) $photo['id'] ?>">
                                        <input type="hidden" name="action" value="delete_photo">
                                        <button class="btn btn-danger btn-small" type="submit">Verwijderen</button>
                                    </form>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="muted">Nog geen <?= strtolower($label) ?>-foto's.</p>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col"><span class="sr-only">Foto</span></th><th scope="col">Project</th><th scope="col">Dienst</th><th scope="col">Opgeleverd</th><th scope="col">Foto's</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Acties</span></th></tr></thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
            <tr>
                <td><?php if ($p['cover_file']): ?><img src="<?= e(upload_url($p['cover_file'])) ?>" alt="" width="64" height="48" loading="lazy"><?php endif; ?></td>
                <td><strong><?= e($p['title']) ?></strong><br><small class="muted"><?= e($p['town']) ?></small></td>
                <td><?= e($p['service_name'] ?? '–') ?></td>
                <td class="nowrap"><?= e(format_date($p['completed_on'])) ?></td>
                <td class="nowrap"><?= (int) $p['before_count'] ?> voor, <?= (int) $p['after_count'] ?> na</td>
                <td><?= $p['is_published'] ? ($p['is_featured'] ? 'Online, uitgelicht' : 'Online') : 'Verborgen' ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-ghost btn-small" href="<?= e(url('admin/projects.php?edit=' . (int) $p['id'])) ?>">Bewerken</a>
                        <form method="post" action="<?= e(url('admin/projects.php')) ?>" data-confirm="Project &quot;<?= e($p['title']) ?>&quot; en alle foto's verwijderen?">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button class="btn btn-danger btn-small" type="submit">Verwijderen</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$projects): ?><tr><td colspan="7" class="empty-row">Nog geen projecten.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php view('admin/footer'); ?>
