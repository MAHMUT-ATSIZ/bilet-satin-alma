<?php
$pageTitle = 'Kuponlar';
include VIEW_PATH . '/partials/header.php';
$me = current_user();
$isAdmin = ((int)$me['role_id'] === 1);
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-percent"></i> Kuponlar</h1>
  <?php if (!$isAdmin): ?>
    <p class="mb-0 text-white-50">Bu sayfadan <strong>firmanıza</strong> özel kuponları yönetebilirsiniz.</p>
  <?php else: ?>
    <p class="mb-0 text-white-50">Sistemdeki tüm firmalara ait kuponları görüntülüyorsunuz.</p>
  <?php endif; ?>
</section>

<div class="d-flex justify-content-between mb-3">
  <a class="btn btn-primary" href="/firma/kupon/ekle"><i class="bi bi-plus-lg"></i> Kupon Oluştur</a>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Kod</th>
          <?php if ($isAdmin): ?><th>Firma</th><?php endif; ?>
          <th>%</th>
          <th>Başlangıç</th>
          <th>Bitiş</th>
          <th>Kullanım</th>
          <th>Durum</th>
          <th style="width:160px">İşlem</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($coupons as $c): ?>
        <tr>
          <td class="fw-semibold"><?= e($c['code']) ?></td>
          <?php if ($isAdmin): ?><td><?= e($c['company_name'] ?? 'Tüm Firmalar') ?></td><?php endif; ?>
          <td><?= (int)$c['discount_percent'] ?></td>
          <td><?= e($c['starts_at'] ?? '—') ?></td>
          <td><?= e($c['ends_at']   ?? '—') ?></td>
          <td><?= (int)$c['used_count'] ?><?= isset($c['max_uses']) ? ' / '.(int)$c['max_uses'] : '' ?></td>
          <td>
            <span class="badge text-bg-<?= ((int)$c['is_active']? 'success':'secondary') ?>">
              <?= (int)$c['is_active'] ? 'Aktif' : 'Pasif' ?>
            </span>
          </td>
          <td>
            <form class="d-inline" method="post" action="/firma/kupon/toggle">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-outline-secondary">
                <?= (int)$c['is_active'] ? 'Pasifleştir' : 'Aktifleştir' ?>
              </button>
            </form>
            <form class="d-inline ms-1" method="post" action="/firma/kupon/sil"
                  onsubmit="return confirm('Silinsin mi?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Sil</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
