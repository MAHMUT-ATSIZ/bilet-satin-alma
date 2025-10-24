<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';


use App\Core\DB;


$pdo = DB::pdo();



$pdo->exec("INSERT OR IGNORE INTO companies(id,name) VALUES (1,'Örnek Turizm'),(2,'HızlıEkspres')");


$today = date('Y-m-d');



$ins = $pdo->prepare('INSERT INTO trips(company_id, departure_city_id, arrival_city_id, departure_time, arrival_time, price, seat_capacity) VALUES(?,?,?,?,?,?,?)');


$rows = [
[1, 1, 2, "$today 09:00:00", "$today 13:30:00", 550.0, 40],
[2, 1, 2, "$today 15:00:00", "$today 19:15:00", 520.0, 40],
[1, 2, 3, "$today 10:00:00", "$today 16:30:00", 700.0, 40],
[2, 3, 1, "$today 08:30:00", "$today 15:30:00", 780.0, 40]
];


foreach ($rows as $r) { $ins->execute($r); }


echo "Seed tamam.\n";