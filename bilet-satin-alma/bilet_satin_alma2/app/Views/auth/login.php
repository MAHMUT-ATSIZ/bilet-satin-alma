<?php
$pageTitle = 'Giriş Yap — ATSBİLET';
include VIEW_PATH . '/partials/header.php';
?>
<section class="hero">
  <div class="row align-items-center">
    <div class="col-lg-8">
      <h1 class="h3 mb-2"><i class="bi bi-box-arrow-in-right"></i> Giriş Yap</h1>
      <p class="lead mb-0">Hesabınıza giriş yaparak seferleri görüntüleyin ve bilet alın.</p>
    </div>
  </div>
</section>

<div class="row justify-content-center">
  <div class="col-md-7 col-lg-5">
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= e($error) ?></div>
    <?php elseif (!empty($_SESSION['flash_error'])): ?>
      <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm card-hover">
      <div class="card-body p-4">
        <form method="post" action="/auth/login" class="row gy-3">
          <?= csrf_field() ?>

          <div class="col-12">
            <label class="form-label">E-posta</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-at"></i></span>
              <input class="form-control" type="email" name="email" required placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Parola</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input class="form-control" type="password" name="password" required placeholder="••••••••">
            </div>
          </div>

          <div class="col-12 d-grid">
            <button class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Giriş Yap</button>
          </div>

          <div class="col-12 text-center">
            <span class="text-secondary small">Hesabınız yok mu?</span>
            <a href="/auth/register">Kayıt Ol</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
