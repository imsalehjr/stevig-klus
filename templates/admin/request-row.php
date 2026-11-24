<?php $statuses = quote_statuses(); ?>
<tr<?= $r['status'] === 'new' ? ' class="row-new"' : '' ?>>
    <td class="nowrap"><a href="<?= e(url('admin/request.php?id=' . (int) $r['id'])) ?>"><?= e($r['reference']) ?></a></td>
    <td><?= e($r['name']) ?><br><small class="muted"><?= e($r['town']) ?></small></td>
    <td><?= e($r['service_name']) ?></td>
    <td class="nowrap"><?= e(format_date($r['created_at'], true)) ?></td>
    <td><span class="status status-<?= e($r['status']) ?>"><?= e($statuses[$r['status']] ?? $r['status']) ?></span></td>
    <td class="nowrap"><?= (int) ($r['photo_count'] ?? 0) ?></td>
</tr>
