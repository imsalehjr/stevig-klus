<?php $c = company(); $periods = quote_periods(); ?>
<!DOCTYPE html>
<html lang="nl">
<body style="margin:0;padding:0;background:#f5f6f3;font-family:Arial,Helvetica,sans-serif;color:#1f2a33;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f6f3;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-top:6px solid #f2b705;">
<tr><td style="padding:28px 32px 8px;">
<p style="margin:0;font-size:22px;font-weight:900;letter-spacing:1px;">STEVIG</p>
<p style="margin:0 0 20px;font-size:12px;">klus &amp; renovatie</p>
<h1 style="margin:0 0 12px;font-size:22px;">Bedankt voor uw aanvraag, <?= e($quote['name']) ?></h1>
<p style="margin:0 0 16px;line-height:1.6;">We hebben uw aanvraag goed ontvangen. Binnen 2 werkdagen nemen we telefonisch of per e-mail contact met u op om de klus te bespreken en eventueel een inmeting te plannen.</p>
<p style="margin:0 0 20px;padding:12px 16px;background:#fff8dd;font-size:15px;">Uw referentienummer: <strong><?= e($quote['reference']) ?></strong></p>
</td></tr>
<tr><td style="padding:0 32px 24px;">
<table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;border-collapse:collapse;">
<tr><td style="color:#5a6770;width:140px;">Soort klus</td><td><?= e($quote['service_name']) ?></td></tr>
<tr><td style="color:#5a6770;">Adres</td><td><?= e($quote['street'] . ' ' . $quote['house_number']) ?>, <?= e($quote['postcode'] . ' ' . $quote['town']) ?></td></tr>
<tr><td style="color:#5a6770;">Periode</td><td><?= e($periods[$quote['preferred_period']] ?? '') ?></td></tr>
<tr><td style="color:#5a6770;">Foto's</td><td><?= (int) $photoCount ?></td></tr>
<tr><td style="color:#5a6770;vertical-align:top;">Omschrijving</td><td><?= nl2br(e($quote['description']), false) ?></td></tr>
</table>
</td></tr>
<tr><td style="padding:20px 32px 28px;border-top:1px solid #d7dbd4;font-size:13px;color:#5a6770;line-height:1.6;">
Klopt er iets niet? Beantwoord deze e-mail of bel <?= e($c['phone']) ?>.<br>
<?= e($c['name']) ?>, <?= e($c['street']) ?>, <?= e($c['postcode'] . ' ' . $c['town']) ?>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
