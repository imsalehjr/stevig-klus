<?php $periods = quote_periods(); ?>
<!DOCTYPE html>
<html lang="nl">
<body style="margin:0;padding:0;background:#f5f6f3;font-family:Arial,Helvetica,sans-serif;color:#1f2a33;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f6f3;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-top:6px solid #1f2a33;">
<tr><td style="padding:28px 32px 8px;">
<h1 style="margin:0 0 6px;font-size:20px;">Nieuwe offerteaanvraag</h1>
<p style="margin:0 0 18px;color:#5a6770;"><?= e($quote['reference']) ?> &middot; <?= e(format_date($quote['created_at'] ?? date('Y-m-d H:i:s'), true)) ?></p>
<table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
<tr><td style="color:#5a6770;width:140px;">Naam</td><td><?= e($quote['name']) ?></td></tr>
<tr><td style="color:#5a6770;">E-mail</td><td><?= e($quote['email']) ?></td></tr>
<tr><td style="color:#5a6770;">Telefoon</td><td><?= e($quote['phone']) ?></td></tr>
<tr><td style="color:#5a6770;">Adres</td><td><?= e($quote['street'] . ' ' . $quote['house_number']) ?>, <?= e($quote['postcode'] . ' ' . $quote['town']) ?></td></tr>
<tr><td style="color:#5a6770;">Soort klus</td><td><?= e($quote['service_name']) ?></td></tr>
<tr><td style="color:#5a6770;">Periode</td><td><?= e($periods[$quote['preferred_period']] ?? '') ?></td></tr>
<tr><td style="color:#5a6770;">Foto's</td><td><?= (int) $photoCount ?></td></tr>
<tr><td style="color:#5a6770;vertical-align:top;">Omschrijving</td><td><?= nl2br(e($quote['description']), false) ?></td></tr>
</table>
<p style="margin:24px 0 28px;"><a href="<?= e(url('admin/request.php?id=' . (int) $quote['id'])) ?>" style="display:inline-block;background:#f2b705;color:#1f2a33;padding:12px 20px;font-weight:bold;text-decoration:none;">Open in het beheer</a></p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
