<?php
namespace App\Controllers;

use App\Core\DB;

class TripController
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::pdo();
    }

    
    public function searchForm(): void
    {
        $errors = []; 
        $cities = $this->pdo
            ->query('SELECT id, name FROM cities ORDER BY name')
            ->fetchAll();

        include VIEW_PATH . '/trips/search.php';
    }

    
    public function list(): void
    {
        $from = (int)($_GET['from'] ?? 0);
        $to   = (int)($_GET['to']   ?? 0);
        $date = trim((string)($_GET['date'] ?? ''));

        $errors = [];
        if ($from <= 0)                 { $errors[] = 'Kalkış şehri seçin.'; }
        if ($to   <= 0)                 { $errors[] = 'Varış şehri seçin.'; }
        if ($date === '')               { $errors[] = 'Tarih seçin.'; }
        if ($from > 0 && $from === $to) { $errors[] = 'Kalkış ve varış farklı olmalı.'; }

        if ($errors) {
            $cities = $this->pdo
                ->query('SELECT id, name FROM cities ORDER BY name')
                ->fetchAll();

            include VIEW_PATH . '/trips/search.php';
            return;
        }

        $sql = 'SELECT
                    t.id, t.company_id, t.departure_time, t.arrival_time, t.price, t.seat_capacity,
                    cd.name AS dep_name, ca.name AS arr_name, c.name AS company_name
                FROM trips t
                JOIN cities cd ON cd.id = t.departure_city_id
                JOIN cities ca ON ca.id = t.arrival_city_id
                JOIN companies c ON c.id = t.company_id
                WHERE t.departure_city_id = ?
                  AND t.arrival_city_id   = ?
                  AND date(t.departure_time) = date(?)
                ORDER BY t.departure_time';

        $stmt  = $this->pdo->prepare($sql);
        $stmt->execute([$from, $to, $date]);
        $trips = $stmt->fetchAll();

        include VIEW_PATH . '/trips/list.php';
    }

    
    public function buy(): void
    {
        if (!current_user()) {
            redirect('/auth/login');
        }

        $tripId = (int)($_GET['trip_id'] ?? 0);
        if ($tripId <= 0) {
            http_response_code(400);
            echo 'Geçersiz sefer.';
            return;
        }

        $chk = $this->pdo->prepare('SELECT id FROM trips WHERE id = ?');
        $chk->execute([$tripId]);
        if (!$chk->fetchColumn()) {
            http_response_code(404);
            echo 'Sefer bulunamadı.';
            return;
        }

        echo '<!doctype html><meta charset="utf-8">';
        echo '<h1>Satın Alma (Yer Tutucu)</h1>';
        echo '<p>trip_id: ' . $tripId . '</p>';
        echo '<p>Bir sonraki adımda koltuk seçimi ve bilet oluşturma akışını ekleyeceğiz.</p>';
        echo '<p><a href="/">Anasayfa</a></p>';
    }
}
