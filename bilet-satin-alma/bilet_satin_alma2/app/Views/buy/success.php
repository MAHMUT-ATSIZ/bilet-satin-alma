<?php
$pageTitle = 'Satın Alma Başarılı';
include VIEW_PATH . '/partials/header.php';


$company   = $trip['company_name'] ?? '—';
$depName   = $trip['dep_name'] ?? '—';
$arrName   = $trip['arr_name'] ?? '—';
$depAt     = isset($trip['departure_time']) ? date('d.m.Y H:i', strtotime($trip['departure_time'])) : '—';
$arrAt     = isset($trip['arrival_time'])   ? date('d.m.Y H:i', strtotime($trip['arrival_time']))   : '—';
$listPrice = (float)($trip['price'] ?? 0.0);
$final     = isset($finalPaid) ? (float)$finalPaid : max(0.0, $listPrice - (float)($discount ?? 0));
?>
<section class="hero text-center">
  <div class="display-6 mb-2 text-success"><i class="bi bi-check-circle-fill"></i></div>
  <h1 class="h3 mb-1">Satın Alma Başarılı</h1>
  <p class="text-white-50 mb-0">PNR kodunuzla biletinizi yönetebilirsiniz.</p>
</section>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Bilet Bilgileri</h5>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="small text-secondary">PNR</div>
            <div class="fs-5 fw-semibold"><?= e($pnr ?? '') ?></div>
          </div>
          <div class="col-md-6">
            <div class="small text-secondary">Firma</div>
            <div class="fw-semibold"><?= e($company) ?></div>
          </div>

          <div class="col-md-6">
            <div class="small text-secondary">Güzergâh</div>
            <div><?= e($depName) ?> → <?= e($arrName) ?></div>
          </div>
          <div class="col-md-6">
            <div class="small text-secondary">Koltuk</div>
            <span class="badge text-bg-light">#<?= (int)($seat ?? 0) ?></span>
          </div>

          <div class="col-md-6">
            <div class="small text-secondary">Kalkış</div>
            <div><?= e($depAt) ?></div>
          </div>
          <div class="col-md-6">
            <div class="small text-secondary">Varış</div>
            <div><?= e($arrAt) ?></div>
          </div>

          <div class="col-12">
            <div class="small text-secondary">Ödeme</div>
            <div class="fs-5 fw-semibold">
              <?php if (!empty($discount) && $discount > 0): ?>
                <s class="text-secondary me-2"><?= e(number_format($listPrice,2,',','.')) ?> ₺</s>
                <?= e(number_format($final,2,',','.')) ?> ₺
                <span class="badge text-bg-success ms-2">−<?= e(number_format($discount,2,',','.')) ?> ₺</span>
              <?php else: ?>
                <?= e(number_format($final,2,',','.')) ?> ₺
              <?php endif; ?>
            </div>
          </div>
        </div>

        <hr>

        <div class="d-flex gap-2 flex-wrap">
          <a href="/account/ticket/pdf?pnr=<?= e($pnr ?? '') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-pdf"></i> PDF
          </a>
          <a href="/account/tickets" class="btn btn-primary">
            <i class="bi bi-collection"></i> Biletlerim
          </a>
          <a href="/pnr" class="btn btn-light">
            <i class="bi bi-search"></i> PNR Sorgu
          </a>
          <a href="/" class="btn btn-light">
            <i class="bi bi-house"></i> Anasayfa
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
