<!doctype html>
<?php include VIEW_PATH . '/partials/nav.php'; ?>

<meta charset="utf-8">
<title>PNR Sonuç</title>
<h1>PNR Sonucu</h1>
<?php if (!$ticket): ?>
<p>Bu PNR ve e-posta ile eşleşen bilet bulunamadı.</p>
<p><a href="/pnr">Geri dön</a></p>
<?php return; ?>
<?php endif; ?>


<p>
<strong>PNR:</strong> <?= e($ticket['pnr']) ?>
| <strong>Yolcu:</strong> <?= e($ticket['fullname']) ?> (<?= e($ticket['email']) ?>)
</p>
<p>
<strong>Sefer:</strong> <?= e($ticket['company_name']) ?> —
<?= e($ticket['dep_name']) ?> → <?= e($ticket['arr_name']) ?>
</p>
<p>
<strong>Kalkış:</strong> <?= e(date('d.m.Y H:i', strtotime($ticket['departure_time']))) ?>
| <strong>Koltuk:</strong> <?= (int)$ticket['seat_no'] ?>
| <strong>Durum:</strong> <?= e($ticket['status']) ?>
</p>
<p><a href="/">Anasayfa</a></p>