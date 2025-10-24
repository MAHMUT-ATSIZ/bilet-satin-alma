<?php
$pageTitle = 'Kayıt Ol — ATSBİLET';
include VIEW_PATH . '/partials/header.php';
?>
<section class="hero">
  <div class="row align-items-center">
    <div class="col-lg-8">
      <h1 class="h3 mb-2"><i class="bi bi-person-plus"></i> Kayıt Ol</h1>
      <p class="lead mb-0">ATSBİLET’e katıl, biletini kolayca satın al ve yönet.</p>
    </div>
  </div>
</section>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php elseif (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm card-hover">
      <div class="card-body p-4">
        <form method="post" action="/auth/register" class="row gy-3">
          <?= csrf_field() ?>

          <div class="col-12">
            <label class="form-label">Ad Soyad</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input class="form-control" type="text" name="fullname" required placeholder="Adınız Soyadınız" value="<?= e($_POST['fullname'] ?? '') ?>">
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">E-posta</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-at"></i></span>
              <input class="form-control" type="email" name="email" required placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Parola (min 8)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input class="form-control" type="password" name="password" required placeholder="••••••••">
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Parola (Tekrar)</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input class="form-control" type="password" name="password2" required placeholder="••••••••">
            </div>
          </div>

          <div class="col-12 d-grid">
            <button class="btn btn-primary"><i class="bi bi-person-check"></i> Kayıt Ol</button>
          </div>

          <div class="col-12 text-center">
            <span class="text-secondary small">Zaten üye misiniz?</span>
            <a href="/auth/login">Giriş Yap</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
