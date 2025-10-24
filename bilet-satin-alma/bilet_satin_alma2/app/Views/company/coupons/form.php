<?php
$pageTitle = 'Kupon Oluştur';
include VIEW_PATH . '/partials/header.php';
$me = current_user();
$isAdmin = ((int)$me['role_id'] === 1);
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-percent"></i> Kupon Oluştur</h1>
  <p class="mb-0 text-white-50">
    <?= $isAdmin ? 'Bir firma seçip o firmaya özel kupon oluşturun.' : 'Firmanıza özel kupon oluşturun.' ?>
  </p>
</section>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="post" action="/firma/kupon/ekle" class="row g-3">
      <?= csrf_field() ?>

      <?php if ($isAdmin): ?>
        <div class="col-md-6">
          <label class="form-label">Firma</label>
          <select name="company_id" class="form-select" required>
            <option value="">Seçiniz</option>
            <?php foreach ($companies as $co): ?>
              <option value="<?= (int)$co['id'] ?>"><?= e($co['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php else: ?>
        <div class="col-md-6">
          <label class="form-label">Firma</label>
          <input class="form-control" value="<?= e($me['company_name'] ?? '') ?>" disabled>
        </div>
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Kod</label>
        <input name="code" class="form-control" placeholder="KAMPANYA2025" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">% İndirim</label>
        <input name="discount_percent" type="number" min="1" max="90" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Başlangıç</label>
        <input name="starts_at" type="datetime-local" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Bitiş</label>
        <input name="ends_at" type="datetime-local" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Maks. Kullanım</label>
        <input name="max_uses" type="number" min="1" class="form-control" placeholder="Sınırsız için boş bırakın">
      </div>

      <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
          <label class="form-check-label" for="is_active">Aktif</label>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-check2"></i> Kaydet</button>
        <a href="/firma/kuponlar" class="btn btn-light">İptal</a>
      </div>
    </form>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
