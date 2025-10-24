<?php
$isEdit = !empty($trip);
$pageTitle = $isEdit ? 'Seferi Düzenle' : 'Yeni Sefer';
include VIEW_PATH . '/partials/header.php';


?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-bus-front"></i> <?= $isEdit ? 'Seferi Düzenle' : 'Yeni Sefer' ?></h1>
  <p class="mb-0 text-white-50"><?= $isEdit ? 'Mevcut sefer bilgilerini güncelleyin' : 'Yeni bir sefer oluşturun' ?></p>
</section>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger"><?= e($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="card shadow-sm card-hover">
  <div class="card-body">
    <form method="post" action="<?= $isEdit ? '/firma/sefer/duzenle' : '/firma/sefer/ekle' ?>" class="row gy-3">
      <?= csrf_field() ?>
      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)$trip['id'] ?>">
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Kalkış Şehri</label>
        <select class="form-select" name="departure_city_id" required>
          <option value="">— Seçiniz —</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $isEdit && (int)$trip['departure_city_id']===(int)$c['id'] ? 'selected':'' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Varış Şehri</label>
        <select class="form-select" name="arrival_city_id" required>
          <option value="">— Seçiniz —</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= $isEdit && (int)$trip['arrival_city_id']===(int)$c['id'] ? 'selected':'' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Kalkış Zamanı</label>
        <input class="form-control" type="datetime-local" name="departure_time"
               value="<?= $isEdit ? e(date('Y-m-d\TH:i', strtotime($trip['departure_time']))) : '' ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Varış Zamanı</label>
        <input class="form-control" type="datetime-local" name="arrival_time"
               value="<?= $isEdit ? e(date('Y-m-d\TH:i', strtotime($trip['arrival_time']))) : '' ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Fiyat (₺)</label>
        <input class="form-control" type="number" name="price" min="0" step="0.01"
               value="<?= $isEdit ? e($trip['price']) : '' ?>" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Koltuk Kapasitesi</label>
        <input class="form-control" type="number" name="seat_capacity" min="1" step="1"
               value="<?= $isEdit ? (int)$trip['seat_capacity'] : 40 ?>" required>
      </div>

      <div class="col-12 text-end">
        <a class="btn btn-outline-secondary" href="/firma/seferler"><i class="bi bi-arrow-left"></i> Geri</a>
        <button class="btn btn-primary"><?= $isEdit ? 'Güncelle' : 'Ekle' ?></button>
      </div>
    </form>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
