<?php
$u = current_user();
$roleText = fn(int $r) => $r===1 ? 'Sistem Admini' : ($r===2 ? 'Firma Admini' : 'Kullanıcı');
$balance = 0.0;
if ($u) {
  try {
    $pdo = \App\Core\DB::pdo();
    $st  = $pdo->prepare('SELECT balance FROM users WHERE id=?');
    $st->execute([$u['id']]);
    $balance = (float)$st->fetchColumn();
  } catch (\Throwable $e) {
    $balance = (float)($u['balance'] ?? 0.0);
  }
}
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="/"><i class="bi bi-ticket-perforated"></i> ATSBİLET</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/pnr"><i class="bi bi-search"></i> PNR Sorgu</a></li>
        <li class="nav-item"><a class="nav-link" href="/account/tickets"><i class="bi bi-collection"></i> Biletlerim</a></li>
        <li class="nav-item"><a class="nav-link" href="/account/profile"><i class="bi bi-person"></i> Profilim</a></li>

        <?php if ($u && in_array((int)$u['role_id'], [1,2], true)): ?>
          <li class="nav-item"><a class="nav-link" href="/firma/seferler"><i class="bi bi-bus-front"></i> Firma Seferleri</a></li>
          <li class="nav-item"><a class="nav-link" href="/firma/biletler"><i class="bi bi-receipt-cutoff"></i> Firma Biletleri</a></li>
          <li class="nav-item"><a class="nav-link" href="/firma/kuponlar"><i class="bi bi-percent"></i> Kuponlar</a></li>
          <li class="nav-item"><a class="nav-link" href="/firma/kullanicilar"><i class="bi bi-people"></i> Kullanıcı Arama</a></li>
        <?php endif; ?>

        <?php if ($u && (int)$u['role_id'] === 1): ?>
          <li class="nav-item"><a class="nav-link" href="/admin/users"><i class="bi bi-shield-check"></i> Kullanıcı Yönetimi</a></li>
          <li class="nav-item"><a class="nav-link" href="/admin/firms"><i class="bi bi-buildings"></i> Firma Yönetimi</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <?php if ($u): ?>
          <span class="text-secondary small d-none d-md-inline">Merhaba,</span>
          <span class="fw-semibold"><?= e($u['fullname']) ?></span>
          <span class="badge text-bg-info-subtle text-info-emphasis">
            <?= e($roleText((int)$u['role_id'])) ?>
          </span>
          <span class="badge text-bg-success-subtle text-success-emphasis">
            Bakiye: <strong><?= e(number_format($balance,2,',','.')) ?> ₺</strong>
          </span>
          <form method="post" action="/auth/logout" class="ms-2">
            <?= csrf_field() ?>
            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-right"></i> Çıkış</button>
          </form>
        <?php else: ?>
          <a class="btn btn-outline-primary btn-sm" href="/auth/login">Giriş</a>
          <a class="btn btn-primary btn-sm" href="/auth/register">Kayıt Ol</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
