<?php
namespace App\Controllers;

use App\Core\DB;

class CompanyTicketController
{
    private \PDO $pdo;
    public function __construct() { $this->pdo = DB::pdo(); }

    private function requireListAccess(): array {
        // Listeyi hem sistem admini hem firma admini görebilir
        require_login([1,2]);
        return current_user();
    }
    private function requireCompanyAdmin(): array {
        // İPTAL YETKİSİ SADECE FİRMA ADMİNİ (2 numaralı rol için geçerli)
        require_login([2]);
        return current_user();
    }

    // GET biletler
    public function index(): void
    {
        $u = $this->requireListAccess();

        $params = [];
        $where  = 'WHERE 1=1 ';

        if ((int)$u['role_id'] === 2) {
            $where .= 'AND tr.company_id = ? ';
            $params[] = (int)$u['company_id'];
        } else {
            $companyId = (int)($_GET['company_id'] ?? 0);
            if ($companyId > 0) { $where .= 'AND tr.company_id = ? '; $params[] = $companyId; }
        }

        $q = trim((string)($_GET['q'] ?? ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where .= 'AND (u.email LIKE ? OR u.fullname LIKE ?) ';
            $params[] = $like; $params[] = $like;
        }

        $sql = "SELECT
                    t.id AS ticket_id, t.pnr, t.seat_no, t.price, t.discount_amount, t.status, t.paid_at,
                    u.id AS user_id, u.email, u.fullname,
                    tr.id AS trip_id, tr.departure_time, tr.arrival_time,
                    cd.name AS dep_name, ca.name AS arr_name,
                    c.id AS company_id, c.name AS company_name
                FROM tickets t
                JOIN users u   ON u.id = t.user_id
                JOIN trips tr  ON tr.id = t.trip_id
                JOIN companies c ON c.id = tr.company_id
                JOIN cities cd ON cd.id = tr.departure_city_id
                JOIN cities ca ON ca.id = tr.arrival_city_id
                $where
                ORDER BY t.paid_at DESC NULLS LAST, t.id DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $tickets = $st->fetchAll();

        $companies = [];
        if ((int)$u['role_id'] === 1) {
            $companies = $this->pdo->query('SELECT id,name FROM companies ORDER BY name')->fetchAll();
        }

        include VIEW_PATH . '/company/tickets/index.php';
    }

    // POST iptal
    public function cancel(): void
    {
        $u = $this->requireCompanyAdmin();  //sadece firma admini iptal gerçekleştirebilir.
        csrf_validate();

        $pnr = strtoupper(trim((string)($_POST['pnr'] ?? '')));
        if ($pnr === '') { $_SESSION['flash_error'] = 'PNR eksik.'; redirect('/firma/biletler'); }

        $sql = 'SELECT t.id, t.user_id, t.status, t.price, t.discount_amount,
                       tr.departure_time, tr.company_id
                FROM tickets t
                JOIN trips tr ON tr.id = t.trip_id
                WHERE t.pnr = ?';
        $st = $this->pdo->prepare($sql);
        $st->execute([$pnr]);
        $row = $st->fetch();
        if (!$row) { $_SESSION['flash_error'] = 'Bilet bulunamadı.'; redirect('/firma/biletler'); }

        if ((int)$row['company_id'] !== (int)$u['company_id']) {
            http_response_code(403); echo 'Yetkisiz işlem'; return;
        }
        if ($row['status'] !== 'PAID') {
            $_SESSION['flash_error'] = 'Sadece ödenmiş biletler iptal edilir.';
            redirect('/firma/biletler');
        }
        if (strtotime($row['departure_time']) - time() < 3600) {
            $_SESSION['flash_error'] = 'Kalkışa 1 saat kala iptal edilemez.';
            redirect('/firma/biletler');
        }

        $refund = max(0.0, (float)$row['price'] - (float)$row['discount_amount']);

        try {
            $this->pdo->beginTransaction();

            $this->pdo->prepare('UPDATE tickets SET status="CANCELLED" WHERE id=?')
                      ->execute([(int)$row['id']]);

            $this->pdo->prepare('UPDATE users SET balance = balance + ? WHERE id=?')
                      ->execute([$refund, (int)$row['user_id']]);

            $this->pdo->prepare('INSERT INTO wallet_transactions(user_id, amount, type, ticket_id, note)
                                 VALUES(?, ?, "REFUND", ?, "Company cancel refund")')
                      ->execute([(int)$row['user_id'], $refund, (int)$row['id']]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo 'İptal hatası: ' . e($e->getMessage());
            return;
        }

        $_SESSION['flash_success'] = 'Bilet iptal edildi ve ücreti kullanıcı bakiyesine eklendi.';
        redirect('/firma/biletler');
    }
}
