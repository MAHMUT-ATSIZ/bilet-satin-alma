<?php
namespace App\Controllers;

use App\Core\DB;

class PurchaseController
{
    private \PDO $pdo;

    public function __construct()
    {
        require_login();           // sadece giriş yapanlar
        $this->pdo = DB::pdo();
    }

    
    public function form(): void
    {
        $tripId   = (int)($_GET['trip_id'] ?? 0);
        $seatNo   = (int)($_GET['seat_no'] ?? 0);
        $coupon   = $this->normalizeCoupon((string)($_GET['coupon'] ?? ''));
        $applyErr = null;

        if ($tripId <= 0) { http_response_code(400); echo 'Geçersiz sefer.'; return; }

        // Sefer + firma + şehirler
        $st = $this->pdo->prepare(
            'SELECT t.*, c.name AS company_name, cd.name AS dep_name, ca.name AS arr_name
             FROM trips t
             JOIN companies c ON c.id=t.company_id
             JOIN cities cd ON cd.id=t.departure_city_id
             JOIN cities ca ON ca.id=t.arrival_city_id
             WHERE t.id=?'
        );
        $st->execute([$tripId]);
        $trip = $st->fetch();
        if (!$trip) { http_response_code(404); echo 'Sefer bulunamadı.'; return; }

        // Geçmiş sefer satılamaz
        if (strtotime($trip['departure_time']) < time()) {
            $_SESSION['flash_error'] = 'Geçmiş bir sefere bilet alınamaz.';
            redirect('/');
        }

        // Dolu koltuklar (PAID durumda olanlar dolu gözükür)
        $taken = $this->pdo->prepare('SELECT seat_no FROM tickets WHERE trip_id=? AND status="PAID"');
        $taken->execute([$tripId]);
        $seatsTaken = array_map('intval', array_column($taken->fetchAll(), 'seat_no'));

        // Kullanıcı bakiyesi
        $me = current_user();
        $bal = $this->pdo->prepare('SELECT balance FROM users WHERE id=?');
        $bal->execute([(int)$me['id']]);
        $balance = (float)$bal->fetchColumn();

        // Kupon indirimi 
        $discount = 0.0;
        $couponRow = null;
        if ($coupon !== '') {
            $couponRow = $this->findValidCoupon($coupon, (int)$trip['company_id']);
            if ($couponRow) {
                $discount = round(((float)$trip['price']) * ((int)$couponRow['discount_percent']) / 100, 2);
            } else {
                $applyErr = 'Kupon kodu geçersiz / aktif değil.';
            }
        }
        $final = max(0.0, (float)$trip['price'] - $discount);

        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        include VIEW_PATH . '/buy/form.php';
    }

    
    public function purchase(): void
    {
        require_login();
        csrf_validate();

        $me     = current_user();
        $tripId = (int)($_POST['trip_id'] ?? 0);
        $seat   = (int)($_POST['seat_no'] ?? 0);
        $coupon = $this->normalizeCoupon((string)($_POST['coupon'] ?? ''));

        if ($tripId <= 0 || $seat <= 0) {
            $_SESSION['flash_error'] = 'Sefer ve koltuk seçimi zorunlu.';
            redirect('/');
        }

        // Sefer
        $st = $this->pdo->prepare(
            'SELECT t.*, c.name AS company_name, cd.name AS dep_name, ca.name AS arr_name
             FROM trips t
             JOIN companies c ON c.id=t.company_id
             JOIN cities cd ON cd.id=t.departure_city_id
             JOIN cities ca ON ca.id=t.arrival_city_id
             WHERE t.id=?'
        );
        $st->execute([$tripId]);
        $trip = $st->fetch();
        if (!$trip) { http_response_code(404); echo 'Sefer bulunamadı.'; return; }

        // Kontroller
        if ($seat < 1 || $seat > (int)$trip['seat_capacity']) {
            $_SESSION['flash_error'] = 'Koltuk numarası bu sefer için geçerli değil.';
            redirect('/buy?trip_id=' . $tripId . '&seat_no=' . $seat . '&coupon=' . urlencode($coupon));
        }
        if (strtotime($trip['departure_time']) < time()) {
            $_SESSION['flash_error'] = 'Geçmiş bir sefere bilet alınamaz.';
            redirect('/');
        }
        // Koltuk dolu mu
        $ck = $this->pdo->prepare('SELECT 1 FROM tickets WHERE trip_id=? AND seat_no=? AND status="PAID"');
        $ck->execute([$tripId, $seat]);
        if ($ck->fetchColumn()) {
            $_SESSION['flash_error'] = 'Seçilen koltuk az önce satıldı. Lütfen başka bir koltuk seçin.';
            redirect('/buy?trip_id=' . $tripId);
        }

        // Kupon 
        $discount = 0.0; $couponId = null; $couponRow = null;
        if ($coupon !== '') {
            $couponRow = $this->findValidCoupon($coupon, (int)$trip['company_id']);
            if (!$couponRow) {
                $_SESSION['flash_error'] = 'Kupon kodu geçersiz / aktif değil.';
                redirect('/buy?trip_id=' . $tripId . '&seat_no=' . $seat);
            }
            $discount = round(((float)$trip['price']) * ((int)$couponRow['discount_percent']) / 100, 2);
            $couponId = (int)$couponRow['id'];
        }

        // Yalnız bakiye ile ödeme
        $final = max(0.0, (float)$trip['price'] - $discount);

        $bal = $this->pdo->prepare('SELECT balance FROM users WHERE id=?');
        $bal->execute([(int)$me['id']]);
        $balance = (float)$bal->fetchColumn();

        if ($balance < $final) {
            $_SESSION['flash_error'] = 'Bakiye yetersiz. Lütfen bakiye ekleyin.';
            redirect('/buy?trip_id=' . $tripId . '&seat_no=' . $seat . '&coupon=' . urlencode($coupon));
        }

        // PNR üretimi
        $pnr = null;
        for ($i=0; $i<6; $i++) {
            $try = $this->generatePNR(6);
            $chk = $this->pdo->prepare('SELECT 1 FROM tickets WHERE pnr=?');
            $chk->execute([$try]);
            if (!$chk->fetchColumn()) { $pnr=$try; break; }
        }
        if (!$pnr) { http_response_code(500); echo 'PNR üretilemedi.'; return; }

        // İşlem
        try {
            $this->pdo->beginTransaction();

            // Bilet
            $ins = $this->pdo->prepare(
                'INSERT INTO tickets(user_id, trip_id, seat_no, price, discount_amount, coupon_id, status, pnr, paid_at)
                 VALUES(?,?,?,?,?,?, "PAID", ?, datetime("now"))'
            );
            $ins->execute([
                (int)$me['id'], $tripId, $seat,
                (float)$trip['price'], $discount, $couponId, $pnr
            ]);
            $ticketId = (int)$this->pdo->lastInsertId();

            // Bakiyeden düş
            $this->pdo->prepare('UPDATE users SET balance = balance - ? WHERE id=?')
                      ->execute([$final, (int)$me['id']]);
            $this->pdo->prepare(
                'INSERT INTO wallet_transactions(user_id, amount, type, ticket_id, note)
                 VALUES(?, ?, "SPEND", ?, "Bilet alımı (bakiye)")'
            )->execute([(int)$me['id'], $final, $ticketId]);

            // Kupon sayaç
            if ($couponId) {
                $this->pdo->prepare('UPDATE coupons SET used_count = used_count + 1 WHERE id = ?')
                          ->execute([$couponId]);
            }

            $this->pdo->commit();
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            if (str_contains($e->getMessage(), 'ux_tickets_trip_seat') || str_contains($e->getMessage(), 'UNIQUE')) {
                $_SESSION['flash_error'] = 'Seçilen koltuk az önce satıldı. Lütfen başka bir koltuk seçiniz.';
                redirect('/buy?trip_id=' . $tripId);
            }
            $_SESSION['flash_error'] = 'Satın alma hatası: ' . e($e->getMessage());
            redirect('/buy?trip_id=' . $tripId);
        }

        // Başarılı
        $finalPaid = $final;
        $seat = $seat; 
        include VIEW_PATH . '/buy/success.php';
    }

    /* ============== helpers ============== */

    private function normalizeCoupon(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        return preg_replace('/[^A-Z0-9]/', '', $raw);
    }

    private function findValidCoupon(string $code, int $companyId): ?array
{
    if ($code === '') return null;

    // Uygulamanın yerel saatine göre "şimdi"
    $now = date('Y-m-d H:i:s');

    // HEREDOC: tırnak karmaşası yok
    $sql = <<<SQL
SELECT *
FROM coupons
WHERE company_id = :cid
  AND code       = :code
  AND is_active  = 1
  AND (
        starts_at IS NULL
        OR datetime(
              CASE
                WHEN length(starts_at) = 10
                  THEN starts_at || ' 00:00:00'
                ELSE replace(starts_at, 'T', ' ')
              END
            ) <= :now
      )
  AND (
        ends_at IS NULL
        OR datetime(
              CASE
                WHEN length(ends_at) = 10
                  THEN ends_at || ' 23:59:59'
                ELSE replace(ends_at, 'T', ' ')
              END
            ) >= :now
      )
  AND (max_uses IS NULL OR used_count < max_uses)
LIMIT 1
SQL;

    $st = $this->pdo->prepare($sql);
    $st->execute([
        ':cid'  => $companyId,
        ':code' => $code,
        ':now'  => $now,
    ]);

    $row = $st->fetch();
    return $row ?: null;
}



    private function generatePNR(int $len=6): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $pnr = '';
        for ($i=0; $i<$len; $i++) {
            $pnr .= $alphabet[random_int(0, strlen($alphabet)-1)];
        }
        return $pnr;
    }
}
