<!doctype html>
<meta charset="utf-8">
<?php $pageTitle = 'Kullanıcı Arama — ATSBİLET'; include VIEW_PATH . '/partials/header.php'; ?>

<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-people"></i> Kullanıcı Arama</h1>
  <p class="mb-0">Müşteri e-posta veya adına göre arama yapın</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<div class="card shadow-sm card-hover mb-3">
  <div class="card-body">
    <form method="get" action="/firma/kullanicilar" class="row gy-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label">Arama</label>
        <input class="form-control" name="q" placeholder="ad veya e-posta" value="<?= e($_GET['q'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Ara</button>
      </div>
    </form>
  </div>
</div>

<?php if (empty($users)): ?>
  <div class="alert alert-info">Kriterlere uygun kullanıcı bulunamadı.</div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>E-posta</th>
            <th>Bakiye</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td class="fw-semibold"><?= e($u['fullname']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e(number_format((float)($u['balance'] ?? 0),2,',','.')) ?> ₺</td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
