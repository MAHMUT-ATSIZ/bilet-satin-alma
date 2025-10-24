<?php
$pageTitle = 'Firma Seferleri';
include VIEW_PATH . '/partials/header.php';
?>
<section class="hero">
  <div class="d-flex align-items-center justify-content-between">
    <div>
      <h1 class="h4 mb-1"><i class="bi bi-bus-front"></i> Firma Seferleri</h1>
      <p class="mb-0 text-white-50">Seferlerinizi yönetin</p>
    </div>
    <a class="btn btn-light" href="/firma/sefer/ekle"><i class="bi bi-plus-circle"></i> Yeni Sefer</a>
  </div>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success"><?= e($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>

<?php if (empty($trips)): ?>
  <div class="alert alert-info">Henüz sefer yok. <a href="/firma/sefer/ekle">İlk seferi ekleyin.</a></div>
<?php else: ?>
  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Güzergâh</th>
            <th>Kalkış</th>
            <th>Varış</th>
            <th>Fiyat</th>
            <th>Kapasite</th>
            <th class="text-end">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($trips as $t): ?>
            <tr>
              <td class="text-secondary small"><?= e($t['dep_name']) ?> → <?= e($t['arr_name']) ?></td>
              <td><?= e(date('d.m.Y H:i', strtotime($t['departure_time']))) ?></td>
              <td><?= e(date('d.m.Y H:i', strtotime($t['arrival_time']))) ?></td>
              <td class="fw-semibold"><?= e(number_format((float)$t['price'],2,',','.')) ?> ₺</td>
              <td><?= (int)$t['seat_capacity'] ?></td>
              <td class="text-end">
                <a class="btn btn-outline-primary btn-sm" href="/firma/sefer/duzenle?id=<?= (int)$t['id'] ?>">
                  <i class="bi bi-pencil"></i> Düzenle
                </a>
                <form method="post" action="/firma/sefer/sil" class="d-inline" onsubmit="return confirm('Sefer silinsin mi?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                  <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Sil</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
