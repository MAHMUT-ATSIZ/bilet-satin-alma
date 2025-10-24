<?php
$pageTitle = 'Firma Biletleri';
include VIEW_PATH . '/partials/header.php';
$me = current_user();
$isSysAdmin = $me && (int)$me['role_id'] === 1;


?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-receipt-cutoff"></i> Firma Biletleri</h1>
  <p class="mb-0 text-white-50">Firmanıza ait bilet hareketleri</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<div class="card shadow-sm card-hover mb-3">
  <div class="card-body">
    <form method="get" action="/firma/biletler" class="row gy-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label">Kullanıcı (ad/e-posta)</label>
        <input class="form-control" name="q" placeholder="musteri@ornek.com" value="<?= e($_GET['q'] ?? '') ?>">
      </div>
      <?php if ($isSysAdmin && !empty($companies)): ?>
        <div class="col-md-4">
          <label class="form-label">Firma</label>
          <select class="form-select" name="company_id">
            <option value="0">— Hepsi —</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)($_GET['company_id'] ?? 0)===(int)$c['id'])?'selected':'' ?>>
                <?= e($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="col-md-3">
        <button class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filtrele</button>
      </div>
    </form>
  </div>
</div>

<?php if (empty($tickets)): ?>
  <div class="alert alert-info">Kayıt bulunamadı.</div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>PNR</th>
            <th>Müşteri</th>
            <th>Güzergâh</th>
            <th>Kalkış</th>
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
              <td>
                <div class="small"><?= e($t['fullname']) ?></div>
                <div class="text-secondary small"><?= e($t['email']) ?></div>
              </td>
              <td class="text-secondary small"><?= e($t['dep_name']) ?> → <?= e($t['arr_name']) ?></td>
              <td><?= e(date('d.m.Y H:i', strtotime($t['departure_time']))) ?></td>
              <td><span class="badge text-bg-light">#<?= (int)$t['seat_no'] ?></span></td>
              <td class="fw-semibold"><?= e(number_format($final,2,',','.')) ?> ₺</td>
              <td><span class="badge text-bg-<?= $badge ?>"><?= e($t['status']) ?></span></td>
              <td class="text-end">
                <?php if ((int)$me['role_id'] === 2 && $canCancel): ?>
                  <form method="post" action="/firma/bilet/iptal" class="d-inline" onsubmit="return confirm('Bilet iptal edilsin mi?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="pnr" value="<?= e($t['pnr']) ?>">
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> İptal Et</button>
                  </form>
                <?php else: ?>
                  <button class="btn btn-outline-secondary btn-sm" disabled><i class="bi bi-lock"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
