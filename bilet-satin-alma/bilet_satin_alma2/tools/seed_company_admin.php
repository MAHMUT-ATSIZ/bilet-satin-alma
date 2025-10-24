<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

use App\Core\DB;

$pdo = DB::pdo();


$email = $argv[1] ?? null;
$pass  = $argv[2] ?? null;
$name  = $argv[3] ?? 'Firma Admin';
$comp  = $argv[4] ?? 'Örnek Turizm';

if (!$email || !$pass) {
    fwrite(STDERR, "Kullanım: php tools/seed_company_admin.php email@example.com Parola [\"Ad Soyad\"] [\"Şirket Adı\"]\n");
    exit(1);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Hata: Geçersiz e-posta.\n");
    exit(1);
}

// 1) Şirketi bul/oluştur
$stmt = $pdo->prepare('SELECT id FROM companies WHERE name = ?');
$stmt->execute([$comp]);
$companyId = $stmt->fetchColumn();

if (!$companyId) {
    $ins = $pdo->prepare('INSERT INTO companies(name) VALUES (?)');
    $ins->execute([$comp]);
    $companyId = (int)$pdo->lastInsertId();
    echo "Şirket oluşturuldu: {$comp} (id={$companyId})\n";
} else {
    $companyId = (int)$companyId;
    echo "Şirket bulundu: {$comp} (id={$companyId})\n";
}

// 2) Kullanıcı var mı?
$sel = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$sel->execute([$email]);
$userId = $sel->fetchColumn();

if ($userId) {
    // Mevcut kullanıcıyı Firma Admin yap
    $upd = $pdo->prepare('UPDATE users SET role_id = 2, company_id = ? WHERE id = ?');
    $upd->execute([$companyId, (int)$userId]);
    echo "Mevcut kullanıcı Firma Admin yapıldı: {$email}\n";
} else {
    // Yeni kullanıcı oluştur 
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $insUser = $pdo->prepare('INSERT INTO users(fullname, email, password_hash, role_id, company_id) VALUES (?, ?, ?, 2, ?)');
    $insUser->execute([$name, $email, $hash, $companyId]);
    $userId = (int)$pdo->lastInsertId();
    echo "Firma Admin oluşturuldu: {$email} (id={$userId})\n";
}

echo "Tamam.\n";
