<?php
$pageTitle = 'Sefer Sonuçları';
include VIEW_PATH . '/partials/header.php';

$me = current_user();
$isCompanyAdmin = $me && (int)$me['role_id'] === 2;
?>

<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-list-ul"></i> Seferler</h1>
  <p class="mb-0">
    <?= e($trips[0]['dep_name'] ?? '') ?> → <?= e($trips[0]['arr_name'] ?? '') ?> ·
    <?= e(isset($_GET['date']) ? date('d.m.Y', strtotime($_GET['date'])) : '') ?>
  </p>
</section>

<?php if (empty($trips)): ?>
  <div class="alert alert-warning">
    Bu kriterlere uygun sefer bulunamadı. <a href="/">Aramaya dön</a>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($trips as $t): ?>
      <div class="col-md-6">
        <div class="card shadow-sm card-hover h-100">
          <div class="card-body d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge text-bg-primary-subtle text-primary-emphasis"><?= e($t['company_name']) ?></span>
              <span class="fw-bold fs-5"><?= e(number_format((float)$t['price'],2,',','.')) ?> ₺</span>
            </div>

            <div class="d-flex align-items-center gap-3 mb-2">
              <div>
                <div class="small text-secondary">Kalkış</div>
                <div class="fw-semibold"><?= e(date('d.m H:i', strtotime($t['departure_time']))) ?></div>
              </div>
              <i class="bi bi-arrow-right fs-4 text-secondary"></i>
              <div>
                <div class="small text-secondary">Varış</div>
                <div class="fw-semibold"><?= e(date('d.m H:i', strtotime($t['arrival_time']))) ?></div>
              </div>
            </div>

            <div class="mt-auto d-flex justify-content-between align-items-center">
              <div class="text-secondary small">
                <?= e($t['dep_name']) ?> → <?= e($t['arr_name']) ?>
              </div>
              <?php if (!$isCompanyAdmin): ?>
                <a class="btn btn-success" href="/buy?trip_id=<?= (int)$t['id'] ?>">
                  <i class="bi bi-cart-check"></i> Satın Al
                </a>
              <?php else: ?>
                <span class="badge text-bg-secondary">Satın alma kapalı</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="mt-3">
  <a class="btn btn-outline-secondary" href="/"><i class="bi bi-arrow-left"></i> Aramaya dön</a>
</div>
