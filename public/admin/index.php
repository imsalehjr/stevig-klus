<?php

require dirname(__DIR__, 2) . '/src/admin.php';

$pdo = Database::pdo();
$statuses = quote_statuses();

$byStatus = array_fill_keys(array_keys($statuses), 0);
foreach ($pdo->query('SELECT status, COUNT(*) AS n FROM quote_requests GROUP BY status') as $row) {
    $byStatus[$row['status']] = (int) $row['n'];
}
$total = array_sum($byStatus);
$open = $byStatus['new'] + $byStatus['contacted'] + $byStatus['quote_sent'];
$lastWeek = (int) $pdo->query('SELECT COUNT(*) FROM quote_requests WHERE created_at >= NOW() - INTERVAL 7 DAY')->fetchColumn();
$decided = $byStatus['accepted'] + $byStatus['done'] + $byStatus['rejected'];
$conversion = $decided > 0 ? (int) round(($byStatus['accepted'] + $byStatus['done']) / $decided * 100) : null;

$perService = $pdo->query(
    'SELECT service_name, COUNT(*) AS n FROM quote_requests GROUP BY service_name ORDER BY n DESC, service_name'
)->fetchAll();
$maxService = max(array_column($perService, 'n') ?: [1]);

$openRequests = $pdo->query(
    "SELECT q.*, (SELECT COUNT(*) FROM quote_request_photos p WHERE p.quote_request_id = q.id) AS photo_count
     FROM quote_requests q WHERE q.status IN ('new', 'contacted', 'quote_sent')
     ORDER BY FIELD(q.status, 'new', 'contacted', 'quote_sent'), q.created_at DESC LIMIT 10"
)->fetchAll();

view('admin/header', ['title' => 'Dashboard', 'active' => 'index.php']);
?>
<div class="admin-head">
    <div>
        <h1><?= e((int) date('G') < 12 ? 'Goedemorgen' : ((int) date('G') < 18 ? 'Goedemiddag' : 'Goedenavond')) ?><?= isset($_SESSION['admin_name']) ? ', ' . e($_SESSION['admin_name']) : '' ?></h1>
        <p><?= $byStatus['new'] === 0 ? 'Geen nieuwe aanvragen. Alles is opgepakt.' : $byStatus['new'] . ' nieuwe ' . ($byStatus['new'] === 1 ? 'aanvraag wacht' : 'aanvragen wachten') . ' op een reactie.' ?></p>
    </div>
    <a class="btn btn-primary" href="<?= e(url('admin/requests.php?status=new')) ?>">Nieuwe aanvragen bekijken</a>
</div>

<div class="stats">
    <a class="stat stat-new" href="<?= e(url('admin/requests.php?status=new')) ?>"><strong><?= $byStatus['new'] ?></strong><span>Nieuw</span></a>
    <a class="stat" href="<?= e(url('admin/requests.php?status=open')) ?>"><strong><?= $open ?></strong><span>Open (nog niet beslist)</span></a>
    <div class="stat"><strong><?= $lastWeek ?></strong><span>Binnen in de laatste 7 dagen</span></div>
    <div class="stat"><strong><?= $conversion === null ? '–' : $conversion . '%' ?></strong><span>Geaccepteerd van beslist</span></div>
    <a class="stat" href="<?= e(url('admin/requests.php')) ?>"><strong><?= $total ?></strong><span>Aanvragen totaal</span></a>
</div>

<div class="admin-grid">
    <section>
        <h2>Open aanvragen</h2>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th scope="col">Referentie</th><th scope="col">Klant</th><th scope="col">Klus</th><th scope="col">Ontvangen</th><th scope="col">Status</th><th scope="col">Foto's</th></tr></thead>
                <tbody>
                <?php foreach ($openRequests as $r): view('admin/request-row', ['r' => $r]); endforeach; ?>
                <?php if (!$openRequests): ?><tr><td colspan="6" class="empty-row">Geen open aanvragen.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <aside>
        <div class="panel">
            <h2>Per status</h2>
            <ul class="bar-list">
                <?php foreach ($statuses as $key => $label): ?>
                    <li><a href="<?= e(url('admin/requests.php?status=' . $key)) ?>"><?= e($label) ?></a><span class="bar"><span data-w="<?= $total ? round($byStatus[$key] / $total * 100) : 0 ?>"></span></span><strong><?= $byStatus[$key] ?></strong></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="panel">
            <h2>Per soort klus</h2>
            <ul class="bar-list">
                <?php foreach ($perService as $row): ?>
                    <li><span><?= e($row['service_name']) ?></span><span class="bar"><span data-w="<?= round($row['n'] / $maxService * 100) ?>"></span></span><strong><?= (int) $row['n'] ?></strong></li>
                <?php endforeach; ?>
                <?php if (!$perService): ?><li>Nog geen aanvragen.</li><?php endif; ?>
            </ul>
        </div>
    </aside>
</div>
<?php view('admin/footer'); ?>
