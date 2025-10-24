<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';


use App\Core\DB;


$pdo = DB::pdo();
$email = $argv[1] ?? null;
$pass = $argv[2] ?? null;
$name = $argv[3] ?? 'Yönetici';
if (!$email || !$pass) { exit("Kullanım: php tools/seed_admin.php email@example.com Parola [AdSoyad]\n"); }


$exists = $pdo->prepare('SELECT id FROM users WHERE email=?');
$exists->execute([$email]);
if ($exists->fetchColumn()) { exit("Bu e-posta zaten kayıtlı.\n"); }


$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins = $pdo->prepare('INSERT INTO users(fullname,email,password_hash,role_id,company_id) VALUES(?,?,?,?,NULL)');
$ins->execute([$name, $email, $hash, 1]); // 1 = Admin


echo "Admin oluşturuldu: {$email}\n";