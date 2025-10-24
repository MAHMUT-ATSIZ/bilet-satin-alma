<?php
$pageTitle = 'PNR Sorgu';
include VIEW_PATH . '/partials/header.php';


?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-search"></i> PNR Sorgu</h1>
  <p class="mb-0 text-white-50">PNR ve e-posta ile biletinizi görüntüleyin.</p>
</section>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <form method="post" action="/pnr" class="row g-3">
      <?= csrf_field() ?>
      <div class="col-md-4">
        <label class="form-label">PNR</label>
        <input name="pnr" class="form-control" maxlength="8" required
               value="<?= e($_POST['pnr'] ?? '') ?>" placeholder="A1B2C3">
      </div>
      <div class="col-md-6">
        <label class="form-label">E-posta</label>
        <input name="email" type="email" class="form-control" required
               value="<?= e($_POST['email'] ?? '') ?>" placeholder="ornek@mail.com">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100"><i class="bi bi-search"></i> Sorgula</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($ticket)): ?>
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title mb-3">Bilet</h5>
      <div class="row g-3">
        <div class="col-md-4"><div class="small text-secondary">PNR</div><div class="fw-semibold"><?= e($ticket['pnr']) ?></div></div>
        <div class="col-md-4"><div class="small text-secondary">Firma</div><div><?= e($ticket['company_name']) ?></div></div>
        <div class="col-md-4"><div class="small text-secondary">Durum</div>
          <span class="badge text-bg-<?= $ticket['status']==='PAID'?'success':'secondary' ?>"><?= e($ticket['status']) ?></span>
        </div>
        <div class="col-md-6"><div class="small text-secondary">Güzergâh</div><div><?= e($ticket['dep_name']) ?> → <?= e($ticket['arr_name']) ?></div></div>
        <div class="col-md-3"><div class="small text-secondary">Kalkış</div><div><?= e(date('d.m.Y H:i', strtotime($ticket['departure_time']))) ?></div></div>
        <div class="col-md-3"><div class="small text-secondary">Koltuk</div><span class="badge text-bg-light">#<?= (int)$ticket['seat_no'] ?></span></div>
      </div>

      <hr>
      <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="/account/ticket/pdf?pnr=<?= e($ticket['pnr']) ?>"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a class="btn btn-light" href="/"><i class="bi bi-house"></i> Anasayfa</a>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
