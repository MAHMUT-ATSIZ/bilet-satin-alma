<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

use App\Core\DB;

$pdo = DB::pdo();


$pdo->exec("CREATE TABLE IF NOT EXISTS _migrations (
  id INTEGER PRIMARY KEY,
  filename TEXT UNIQUE,
  run_at TEXT
);");

$path  = __DIR__ . '/../database/migrations';
$files = glob($path . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);
    $stmt = $pdo->prepare("SELECT 1 FROM _migrations WHERE filename = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        continue; 
    }
    $sql = file_get_contents($file);
    $pdo->exec($sql);
    $ins = $pdo->prepare("INSERT INTO _migrations (filename, run_at) VALUES (?, datetime('now'))");
    $ins->execute([$name]);
    echo "Applied: {$name}\n";
}

echo "Migrations completed.\n";
