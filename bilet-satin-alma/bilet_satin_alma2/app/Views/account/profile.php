<?php
$pageTitle = 'Profilim';
include VIEW_PATH . '/partials/header.php';
$u = current_user();
$roleName = fn($r) => $r===1?'Sistem Admini':($r===2?'Firma Admini':'Kullanıcı');
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-person"></i> Profilim</h1>
  <p class="mb-0 text-white-50">Hesap bilgileri ve bakiye işlemleri</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm h-100">
      <div class="card-body">
        <h2 class="h6 text-secondary">Hesap</h2>
        <div class="mb-2"><span class="text-secondary small">Ad Soyad</span><div class="fw-semibold"><?= e($u['fullname']) ?></div></div>
        <div class="mb-2"><span class="text-secondary small">E-posta</span><div class="fw-semibold"><?= e($u['email']) ?></div></div>
        <div class="mb-2"><span class="text-secondary small">Rol</span><div class="badge text-bg-info-subtle text-info-emphasis"><?= e($roleName((int)$u['role_id'])) ?></div></div>
        <?php if (!empty($u['company_id'])): ?>
          <div class="mb-2"><span class="text-secondary small">Firma</span><div class="fw-semibold"><?= e($u['company_name'] ?? '-') ?></div></div>
        <?php endif; ?>
        <div><span class="text-secondary small">Bakiye</span><div class="badge text-bg-success-subtle text-success-emphasis fs-6"><?= e(number_format((float)($user['balance'] ?? 0), 2, ',', '.')) ?> ₺</div></div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="h6 text-secondary">Bakiye Ekle</h2>
        <form method="post" action="/account/balance/add" class="row gy-3">
          <?= csrf_field() ?>
          <div class="col-md-6">
            <label class="form-label">Tutar (₺)</label>
            <input class="form-control" type="number" name="amount" min="1" step="0.01" required placeholder="250.00">
          </div>
          <div class="col-md-6">
            <label class="form-label">Kart Numarası (16 hane)</label>
            <input class="form-control" name="card_number" inputmode="numeric" placeholder="4242 4242 4242 4242">
          </div>
          <div class="col-md-6">
            <label class="form-label">Son Kullanma</label>
            <input class="form-control" type="month" name="expiry">
          </div>
          <div class="col-md-6">
            <label class="form-label">CVV</label>
            <input class="form-control" name="cvv" inputmode="numeric" maxlength="3" placeholder="123">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-primary"><i class="bi bi-wallet2"></i> Bakiye Ekle</button>
          </div>
        </form>
        <div class="form-text mt-2">Not: Bu alan demo amaçlıdır; kart bilgileri yalnızca format kontrolünden geçer.</div>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
