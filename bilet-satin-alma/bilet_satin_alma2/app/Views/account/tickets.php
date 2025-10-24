<?php
$pageTitle = 'Biletlerim';
include VIEW_PATH . '/partials/header.php';

$me = current_user();
$isUser = $me && (int)$me['role_id']===3;


?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-collection"></i> Biletlerim</h1>
  <p class="mb-0 text-white-50">Aktif ve geçmiş biletlerin listesi</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<?php if (empty($tickets)): ?>
  <div class="alert alert-info">
    Henüz biletiniz yok. <a href="/">Hemen bir sefer arayın</a>.
  </div>
<?php else: ?>
  <div class="card shadow-sm card-hover">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>PNR</th>
              <th>Firma</th>
              <th>Güzergâh</th>
              <th>Kalkış / Varış</th>
              <th>Koltuk</th>
              <th>Ödenen</th>
              <th>Durum</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($tickets as $t): ?>
            <?php
              $final = max(0.0, (float)$t['price'] - (float)$t['discount_amount']);
              $canCancel = ($t['status'] === 'PAID') && (strtotime($t['departure_time']) - time() >= 3600);
              $badge = $t['status']==='PAID' ? 'success' : ($t['status']==='CANCELLED'?'secondary':'warning');
            ?>
            <tr>
              <td class="fw-semibold"><?= e($t['pnr']) ?></td>
              <td><span class="badge text-bg-primary-subtle text-primary-emphasis"><?= e($t['company_name']) ?></span></td>
              <td class="text-secondary small">
                <?= e($t['dep_name']) ?> → <?= e($t['arr_name']) ?>
              </td>
              <td>
                <div class="small">Kalkış: <strong><?= e(date('d.m.Y H:i', strtotime($t['departure_time']))) ?></strong></div>
                <div class="small text-secondary">Varış: <?= e(date('d.m.Y H:i', strtotime($t['arrival_time']))) ?></div>
              </td>
              <td><span class="badge text-bg-light seat-badge">#<?= (int)$t['seat_no'] ?></span></td>
              <td class="fw-semibold"><?= e(number_format($final,2,',','.')) ?> ₺</td>
              <td><span class="badge text-bg-<?= $badge ?>"><?= e($t['status']) ?></span></td>
              <td class="text-end">
                <?php if ($isUser): ?>
                  <a class="btn btn-outline-secondary btn-sm" href="/account/ticket/pdf?pnr=<?= e($t['pnr']) ?>">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </a>
                <?php endif; ?>

                <?php if ($canCancel): ?>
                  <form method="post" action="/ticket/cancel" class="d-inline" onsubmit="return confirm('Bilet iptal edilsin mi?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="pnr" value="<?= e($t['pnr']) ?>">
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> İptal Et</button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm" disabled>
                    <i class="bi bi-lock"></i> İptal Edilemez
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
