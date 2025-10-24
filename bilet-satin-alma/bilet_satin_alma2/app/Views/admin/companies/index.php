<?php
$pageTitle = 'Firma Yönetimi';
include VIEW_PATH . '/partials/header.php';
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-buildings"></i> Firma Yönetimi</h1>
  <p class="mb-0 text-white-50">Firma ekle/sil ve mevcutları görüntüle</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card shadow-sm card-hover h-100">
      <div class="card-body">
        <h2 class="h6 text-secondary">Yeni Firma Ekle</h2>
        <form method="post" action="/admin/firms/create" class="row gy-2">
          <?= csrf_field() ?>
          <div class="col-12">
            <label class="form-label">Firma Adı</label>
            <input class="form-control" name="name" required maxlength="100" placeholder="Örn. Hızlı Turizm">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ekle</button>
          </div>
        </form>
        <div class="form-text mt-2">Not: Geleceğe dönük seferi bulunan firma silinemez.</div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Ad</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($companies)): ?>
              <tr><td colspan="3"><div class="p-3">Henüz firma yok.</div></td></tr>
            <?php else: ?>
              <?php foreach ($companies as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td><?= e($c['name']) ?></td>
                  <td class="text-end">
                    <form method="post" action="/admin/firms/delete" class="d-inline" onsubmit="return confirm('Bu firma silinsin mi?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Sil</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
