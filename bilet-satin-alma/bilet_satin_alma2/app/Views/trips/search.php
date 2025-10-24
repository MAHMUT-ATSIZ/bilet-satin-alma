<?php $pageTitle = 'Sefer Ara'; include VIEW_PATH . '/partials/header.php'; ?>

<section class="hero">
  <div class="row align-items-center">
    <div class="col-lg-7">
      <h1 class="h3 mb-2"><i class="bi bi-bus-front"></i> Biletini Bul</h1>
      <p class="lead mb-0">Şehir, tarih seç; uygun seferleri hemen listele.</p>
    </div>
    <div class="col-lg-5 text-lg-end mt-3 mt-lg-0">
      <i class="bi bi-ticket-perforated" style="font-size:56px; opacity:.8"></i>
    </div>
  </div>
</section>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card shadow-sm card-hover">
  <div class="card-body">
    <form class="row gy-3" method="get" action="/seferler">
      <div class="col-md-4">
        <label class="form-label">Kalkış Şehri</label>
        <select name="from" class="form-select" required>
          <option value="">— Seçiniz —</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Varış Şehri</label>
        <select name="to" class="form-select" required>
          <option value="">— Seçiniz —</option>
          <?php foreach ($cities as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Tarih</label>
        <input type="date" name="date" class="form-control" required min="<?= e(date('Y-m-d')) ?>">
      </div>

      <div class="col-12 text-end">
        <button class="btn btn-primary">
          <i class="bi bi-search"></i> Seferleri Listele
        </button>
      </div>
    </form>
  </div>
</div>
