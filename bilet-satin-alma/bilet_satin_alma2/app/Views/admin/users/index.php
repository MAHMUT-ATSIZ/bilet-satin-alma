<?php
$pageTitle = 'Kullanıcı Yönetimi';
include VIEW_PATH . '/partials/header.php';
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-shield-check"></i> Kullanıcı Yönetimi</h1>
  <p class="mb-0 text-white-50">Kullanıcıları listele, ara ve rol/firma ataması yap</p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<div class="card shadow-sm card-hover mb-3">
  <div class="card-body">
    <form method="get" action="/admin/users" class="row gy-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Ara (ad/e-posta)</label>
        <input class="form-control" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="ada göre, e-postaya göre">
      </div>
      <div class="col-md-3">
        <label class="form-label">Rol</label>
        <select class="form-select" name="role">
          <option value="0">— Hepsi —</option>
          <option value="2" <?= (isset($_GET['role']) && (int)$_GET['role']===2)?'selected':'' ?>>Firma Admini</option>
          <option value="3" <?= (isset($_GET['role']) && (int)$_GET['role']===3)?'selected':'' ?>>Kullanıcı</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Firma</label>
        <select class="form-select" name="company_id">
          <option value="0">— Hepsi —</option>
          <?php foreach ($companies as $co): ?>
            <option value="<?= (int)$co['id'] ?>" <?= ((int)($_GET['company_id'] ?? 0)===(int)$co['id'])?'selected':'' ?>>
              <?= e($co['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filtrele</button>
      </div>
    </form>
  </div>
</div>

<?php if (empty($users)): ?>
  <div class="alert alert-info">Kayıt bulunamadı.</div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>E-posta</th>
            <th>Rol</th>
            <th>Firma</th>
            <th>Bakiye</th>
            <th class="text-end">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= e($u['fullname']) ?></td>
              <td><?= e($u['email']) ?></td>
              <td><?= (int)$u['role_id']===1 ? 'Sistem Admini' : ((int)$u['role_id']===2 ? 'Firma Admini' : 'Kullanıcı') ?></td>
              <td><?= e($u['company_name'] ?? '—') ?></td>
              <td><?= e(number_format((float)$u['balance'],2,',','.')) ?> ₺</td>
              <td class="text-end">
                <?php if ((int)$u['role_id'] !== 1): ?>
                  <form method="post" action="/admin/users/role" class="d-flex gap-2 justify-content-end">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <select name="role_id" class="form-select form-select-sm" style="max-width:160px;">
                      <option value="3" <?= ((int)$u['role_id']===3)?'selected':'' ?>>Kullanıcı</option>
                      <option value="2" <?= ((int)$u['role_id']===2)?'selected':'' ?>>Firma Admini</option>
                    </select>
                    <select name="company_id" class="form-select form-select-sm" style="max-width:220px;" title="Firma (yalnız Firma Admini)">
                      <option value="">— Firma seç —</option>
                      <?php foreach ($companies as $co): ?>
                        <option value="<?= (int)$co['id'] ?>" <?= ((int)($u['company_id'] ?? 0)===(int)$co['id'])?'selected':'' ?>>
                          <?= e($co['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-primary btn-sm"><i class="bi bi-save"></i> Kaydet</button>
                  </form>
                  <div class="form-text text-end">“Firma Admini” seçerseniz firma belirtin; “Kullanıcı” seçerseniz firma boş kalır.</div>
                <?php else: ?>
                  <span class="text-secondary small">Sistem admini için işlem yok</span>
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
