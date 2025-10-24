<?php
namespace App\Controllers;

use App\Core\DB;

class CompanyTripController
{
    private \PDO $pdo;

    public function __construct()
    {
        
        require_login();
        $this->pdo = DB::pdo();
    }

    
    public function index(): void
    {
        $me = current_user();

        if ((int)$me['role_id'] === 2) {
            $sql = 'SELECT t.*, cd.name dep_name, ca.name arr_name, c.name company_name
                    FROM trips t
                    JOIN cities cd ON cd.id = t.departure_city_id
                    JOIN cities ca ON ca.id = t.arrival_city_id
                    JOIN companies c ON c.id = t.company_id
                    WHERE t.company_id = ?
                    ORDER BY t.departure_time DESC';
            $st = $this->pdo->prepare($sql);
            $st->execute([(int)$me['company_id']]);
            $trips = $st->fetchAll();
        } else {
            // Sistem admini: hepsini görür (read-only)
            require_login([1]);
            $sql = 'SELECT t.*, cd.name dep_name, ca.name arr_name, c.name company_name
                    FROM trips t
                    JOIN cities cd ON cd.id = t.departure_city_id
                    JOIN cities ca ON ca.id = t.arrival_city_id
                    JOIN companies c ON c.id = t.company_id
                    ORDER BY t.departure_time DESC';
            $trips = $this->pdo->query($sql)->fetchAll();
        }

        include VIEW_PATH . '/company/trips/index.php';
    }

    /** GET /firma/sefer/ekle – sadece firma admini */
    public function createForm(): void
    {
        require_login([2]);
        $cities = $this->pdo->query('SELECT id,name FROM cities ORDER BY name')->fetchAll();
        include VIEW_PATH . '/company/trips/form.php';
    }

    /** POST /firma/sefer/ekle – sadece firma admini */
    public function create(): void
    {
        require_login([2]);
        csrf_validate();

        $me = current_user();
        $companyId = (int)$me['company_id'];

        $depId = $this->intFromPost(['departure_city_id','departure_city','from']);
        $arrId = $this->intFromPost(['arrival_city_id','arrival_city','to']);
        $depAt = trim((string)($_POST['departure_time'] ?? ''));
        $arrAt = trim((string)($_POST['arrival_time'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $cap   = (int)($_POST['seat_capacity'] ?? 0);

        $errors = $this->validateTrip($depId,$arrId,$depAt,$arrAt,$price,$cap);
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $cities = $this->pdo->query('SELECT id,name FROM cities ORDER BY name')->fetchAll();
            include VIEW_PATH . '/company/trips/form.php';
            return;
        }

        $st = $this->pdo->prepare(
            'INSERT INTO trips(company_id, departure_city_id, arrival_city_id, departure_time, arrival_time, price, seat_capacity)
             VALUES(?,?,?,?,?,?,?)'
        );
        $st->execute([$companyId,$depId,$arrId,$depAt,$arrAt,$price,$cap]);

        $_SESSION['flash_success'] = 'Sefer eklendi.';
        redirect('/firma/seferler');
    }

    /** GET /firma/sefer/duzenle – sadece firma admini */
    public function editForm(): void
    {
        require_login([2]);
        $id = (int)($_GET['id'] ?? 0);
        $trip = $this->fetchOwnedTrip($id);
        if (!$trip) { http_response_code(404); echo 'Sefer bulunamadı.'; return; }

        $cities = $this->pdo->query('SELECT id,name FROM cities ORDER BY name')->fetchAll();
        include VIEW_PATH . '/company/trips/form.php';
    }

    /** POST /firma/sefer/duzenle – sadece firma admini */
    public function update(): void
    {
        require_login([2]);
        csrf_validate();

        $id   = (int)($_POST['id'] ?? 0);
        $trip = $this->fetchOwnedTrip($id);
        if (!$trip) { http_response_code(404); echo 'Sefer bulunamadı.'; return; }

        $depId = $this->intFromPost(['departure_city_id','departure_city','from']);
        $arrId = $this->intFromPost(['arrival_city_id','arrival_city','to']);
        $depAt = trim((string)($_POST['departure_time'] ?? ''));
        $arrAt = trim((string)($_POST['arrival_time'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $cap   = (int)($_POST['seat_capacity'] ?? 0);

        $errors = $this->validateTrip($depId,$arrId,$depAt,$arrAt,$price,$cap);
        if ($errors) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $cities = $this->pdo->query('SELECT id,name FROM cities ORDER BY name')->fetchAll();
            $trip = array_merge($trip, [
                'departure_city_id'=>$depId,
                'arrival_city_id'  =>$arrId,
                'departure_time'   =>$depAt,
                'arrival_time'     =>$arrAt,
                'price'            =>$price,
                'seat_capacity'    =>$cap,
            ]);
            include VIEW_PATH . '/company/trips/form.php';
            return;
        }

        $st = $this->pdo->prepare(
            'UPDATE trips SET departure_city_id=?, arrival_city_id=?, departure_time=?, arrival_time=?, price=?, seat_capacity=? WHERE id=?'
        );
        $st->execute([$depId,$arrId,$depAt,$arrAt,$price,$cap,$id]);

        $_SESSION['flash_success'] = 'Sefer güncellendi.';
        redirect('/firma/seferler');
    }

    /** POST /firma/sefer/sil – sadece firma admini */
    public function delete(): void
    {
        require_login([2]);
        csrf_validate();

        $id = (int)($_POST['id'] ?? 0);
        $trip = $this->fetchOwnedTrip($id);
        if (!$trip) { http_response_code(404); echo 'Sefer bulunamadı.'; return; }

        $this->pdo->prepare('DELETE FROM trips WHERE id=?')->execute([$id]);
        $_SESSION['flash_success'] = 'Sefer silindi.';
        redirect('/firma/seferler');
    }

    /* helpers */
    private function intFromPost(array $names): int
    {
        foreach ($names as $n) {
            if (isset($_POST[$n]) && $_POST[$n] !== '') return (int)$_POST[$n];
        }
        return 0;
    }

    private function cityExists(int $id): bool
    {
        $st = $this->pdo->prepare('SELECT 1 FROM cities WHERE id=?');
        $st->execute([$id]);
        return (bool)$st->fetchColumn();
    }

    private function validateTrip(int $depId, int $arrId, string $depAt, string $arrAt, float $price, int $cap): array
    {
        $errors = [];
        if ($depId <= 0) $errors[] = 'Kalkış şehri zorunlu.';
        if ($arrId <= 0) $errors[] = 'Varış şehri zorunlu.';
        if ($depId > 0 && $arrId > 0 && $depId === $arrId) $errors[] = 'Kalkış ve varış aynı olamaz.';
        if ($depAt === '' || $arrAt === '') $errors[] = 'Kalkış/varış zamanı zorunludur.';
        if ($depAt && $arrAt && strtotime($arrAt) <= strtotime($depAt)) $errors[] = 'Varış, kalkıştan sonra olmalı.';
        if ($price <= 0) $errors[] = 'Fiyat 0’dan büyük olmalı.';
        if ($cap   <= 0) $errors[] = 'Koltuk kapasitesi 1’den büyük olmalı.';
        if ($depId > 0 && !$this->cityExists($depId)) $errors[] = 'Kalkış şehri bulunamadı.';
        if ($arrId > 0 && !$this->cityExists($arrId)) $errors[] = 'Varış şehri bulunamadı.';
        return $errors;
    }

    private function fetchOwnedTrip(int $id): ?array
    {
        if ($id <= 0) return null;
        $me = current_user();
        $st = $this->pdo->prepare('SELECT * FROM trips WHERE id=? AND company_id=?');
        $st->execute([$id, (int)$me['company_id']]);
        $row = $st->fetch();
        return $row ?: null;
    }
}
