<?php
$pageTitle = 'Bilet Satın Al';
include VIEW_PATH . '/partials/header.php';


$seatCapacity = (int)$trip['seat_capacity'];
$selectedSeat = (int)($_GET['seat_no'] ?? 0);
?>
<section class="hero">
  <h1 class="h4 mb-1"><i class="bi bi-ticket-perforated"></i> Bilet Satın Al</h1>
  <p class="mb-0 text-white-50"></p>
</section>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>
<?php if (!empty($applyErr)): ?>
  <div class="alert alert-warning"><?= e($applyErr) ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="mb-3">
          <div class="small text-secondary">Firma</div>
          <div class="fw-semibold"><?= e($trip['company_name']) ?></div>
        </div>
        <div class="row">
          <div class="col-md-7">
            <div class="small text-secondary">Güzergâh</div>
            <div class="fw-semibold"><?= e($trip['dep_name']) ?> → <?= e($trip['arr_name']) ?></div>
          </div>
          <div class="col-md-5">
            <div class="small text-secondary">Koltuk Kapasitesi</div>
            <div>#<?= (int)$trip['seat_capacity'] ?></div>
          </div>
        </div>
        <div class="row mt-2">
          <div class="col-md-6">
            <div class="small text-secondary">Kalkış</div>
            <div><?= e(date('d.m.Y H:i', strtotime($trip['departure_time']))) ?></div>
          </div>
          <div class="col-md-6">
            <div class="small text-secondary">Varış</div>
            <div><?= e(date('d.m.Y H:i', strtotime($trip['arrival_time']))) ?></div>
          </div>
        </div>

        <hr>

        <!-- Kupon kodu kullanma -->
        <form method="get" action="/buy" class="row g-3">
          <input type="hidden" name="trip_id" value="<?= (int)$trip['id'] ?>">

          <div class="col-md-6">
            <label class="form-label">Koltuk Seç</label>
            <select name="seat_no" class="form-select" required>
              <option value="">Seçiniz</option>
              <?php for ($i=1; $i <= $seatCapacity; $i++): 
                    $isTaken = in_array($i, $seatsTaken, true);
                    $sel = $selectedSeat === $i ? 'selected' : '';
              ?>
                <option value="<?= $i ?>" <?= $sel ?> <?= $isTaken ? 'disabled' : '' ?>>
                  #<?= $i ?> <?= $isTaken ? '(DOLU)' : '' ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Kupon Kodu</label>
            <div class="input-group">
              <input name="coupon" class="form-control" value="<?= e($coupon) ?>" placeholder="ATSBILET20">
              <button class="btn btn-outline-primary" type="submit">
                <i class="bi bi-percent"></i> Kuponu Uygula
              </button>
            </div>
            <div class="form-text">Kupon kodunuz var ise giriniz.</div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Özet</h5>
        <div class="d-flex justify-content-between">
          <span>Liste Fiyatı</span>
          <span class="<?= ($discount>0?'text-secondary text-decoration-line-through':'') ?>">
            <?= e(number_format((float)$trip['price'],2,',','.')) ?> ₺
          </span>
        </div>
        <?php if ($discount > 0): ?>
          <div class="d-flex justify-content-between">
            <span>Kupon İndirimi</span>
            <span class="text-success">− <?= e(number_format($discount,2,',','.')) ?> ₺</span>
          </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between fw-semibold fs-5 mt-2">
          <span>Ödenecek</span>
          <span><?= e(number_format($final,2,',','.')) ?> ₺</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between">
          <span>Bakiyeniz</span>
          <span><?= e(number_format($balance,2,',','.')) ?> ₺</span>
        </div>

        <?php if ($selectedSeat <= 0): ?>
          <div class="alert alert-info mt-3">Satın almak için önce koltuk seçiniz.</div>
        <?php elseif ($final > $balance): ?>
          <div class="alert alert-warning mt-3">
            Bakiye yetersiz. <a href="/account/profile" class="alert-link">Bakiye ekleyin</a> ve tekrar deneyin.
          </div>
        <?php endif; ?>

        <!-- Satın alma -->
        <form method="post" action="/buy" class="mt-3">
          <?= csrf_field() ?>
          <input type="hidden" name="trip_id" value="<?= (int)$trip['id'] ?>">
          <input type="hidden" name="seat_no" value="<?= (int)$selectedSeat ?>">
          <input type="hidden" name="coupon"  value="<?= e($coupon) ?>">

          <button class="btn btn-primary w-100"
                  <?= ($selectedSeat<=0 || $final>$balance) ? 'disabled' : '' ?>>
            <i class="bi bi-wallet2"></i> Satın Al (Bakiyeden)
          </button>
        </form>

        <a href="/" class="btn btn-light w-100 mt-2"><i class="bi bi-house"></i> Anasayfa</a>
      </div>
    </div>
  </div>
</div>

<?php include VIEW_PATH . '/partials/footer.php'; ?>
