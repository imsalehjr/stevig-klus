<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$statuses = quote_statuses();
$services = Content::services(false);

$status = get_string('status');
$serviceId = get_int('service');
$q = mb_substr(get_string('q'), 0, 100);
$page = max(1, get_int('page'));
$perPage = 20;

$where = [];
$params = [];
if ($status === 'open') {
    $where[] = "q.status IN ('new', 'contacted', 'quote_sent')";
} elseif (array_key_exists($status, $statuses)) {
    $where[] = 'q.status = ?';
    $params[] = $status;
} else {
    $status = '';
}
if ($serviceId > 0) {
    $where[] = 'q.service_id = ?';
    $params[] = $serviceId;
}
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $where[] = '(q.reference LIKE ? OR q.name LIKE ? OR q.email LIKE ? OR q.town LIKE ? OR q.postcode LIKE ?)';
    array_push($params, $like, $like, $like, $like, $like);
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count = $pdo->prepare("SELECT COUNT(*) FROM quote_requests q $whereSql");
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare(
    "SELECT q.*, (SELECT COUNT(*) FROM quote_request_photos p WHERE p.quote_request_id = q.id) AS photo_count
     FROM quote_requests q $whereSql ORDER BY q.created_at DESC, q.id DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$query = static fn (array $extra): string => 'admin/requests.php?' . http_build_query(array_filter(
    array_merge(['status' => $status, 'service' => $serviceId ?: null, 'q' => $q], $extra),
    static fn ($v) => $v !== null && $v !== ''
));

view('admin/header', ['title' => 'Aanvragen', 'active' => 'requests.php']);
?>
<div class="admin-head">
    <div>
        <h1>Aanvragen</h1>
        <p><?= $total ?> <?= $total === 1 ? 'aanvraag' : 'aanvragen' ?> gevonden</p>
    </div>
</div>

<form class="filters" method="get" action="<?= e(url('admin/requests.php')) ?>">
    <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status" data-autosubmit>
            <option value="">Alle</option>
            <option value="open"<?= $status === 'open' ? ' selected' : '' ?>>Open (nieuw t/m offerte verstuurd)</option>
            <?php foreach ($statuses as $key => $label): ?>
                <option value="<?= e($key) ?>"<?= $status === $key ? ' selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="service">Soort klus</label>
        <select id="service" name="service" data-autosubmit>
            <option value="">Alle</option>
            <?php foreach ($services as $s): ?>
                <option value="<?= (int) $s['id'] ?>"<?= $serviceId === (int) $s['id'] ? ' selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="q">Zoeken</label>
        <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Naam, plaats, postcode of referentie">
    </div>
    <button class="btn btn-dark" type="submit">Filteren</button>
    <?php if ($status !== '' || $serviceId || $q !== ''): ?><a href="<?= e(url('admin/requests.php')) ?>">Wis filters</a><?php endif; ?>
</form>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th scope="col">Referentie</th><th scope="col">Klant</th><th scope="col">Klus</th><th scope="col">Ontvangen</th><th scope="col">Status</th><th scope="col">Foto's</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): view('admin/request-row', ['r' => $r]); endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="empty-row">Geen aanvragen gevonden met deze filters.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($pages > 1): ?>
    <nav class="filter-bar" aria-label="Paginering">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="<?= e(url($query(['page' => $i]))) ?>"<?= $i === $page ? ' aria-current="true"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
    </nav>
<?php endif; ?>
<?php view('admin/footer'); ?>
