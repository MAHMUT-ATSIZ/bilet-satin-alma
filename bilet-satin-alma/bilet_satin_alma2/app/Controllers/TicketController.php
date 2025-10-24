<?php
namespace App\Controllers;

use App\Core\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class TicketController
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::pdo();
    }

    /** Kullanıcının kendi biletleri */
    public function myTickets(): void
    {
        require_login(); // herkes (admin dâhil) kendi biletlerini görebilir
        $me = current_user();

        $sql = 'SELECT
                    t.pnr, t.status, t.seat_no, t.price, t.discount_amount,
                    tr.departure_time, tr.arrival_time,
                    cd.name AS dep_name, ca.name AS arr_name, c.name AS company_name
                FROM tickets t
                JOIN trips tr ON tr.id = t.trip_id
                JOIN cities cd ON cd.id = tr.departure_city_id
                JOIN cities ca ON ca.id = tr.arrival_city_id
                JOIN companies c ON c.id = tr.company_id
                WHERE t.user_id = ?
                ORDER BY tr.departure_time DESC, t.id DESC';
        $st = $this->pdo->prepare($sql);
        $st->execute([(int)$me['id']]);
        $tickets = $st->fetchAll();

        include VIEW_PATH . '/account/tickets.php';
    }

    private function cancelColumn(): string {
    $cols  = $this->pdo->query("PRAGMA table_info(tickets)")->fetchAll();
    $names = array_map(fn($r)=>strtolower($r['name']), $cols);
    if (in_array('canceled_at',$names,true))  return 'canceled_at';
    if (in_array('cancelled_at',$names,true)) return 'cancelled_at';
    
    $this->pdo->exec("ALTER TABLE tickets ADD COLUMN canceled_at TEXT");
    return 'canceled_at';
    }

    /** POST Bilet İptal  */
    public function cancel(): void
    {
        require_login([2,3]); // Firma Admini ve User
        csrf_validate();

        $me  = current_user();
        $pnr = trim((string)($_POST['pnr'] ?? ''));
        if ($pnr === '') { http_response_code(400); echo 'PNR gerekli.'; return; }

        // Bilet + sefer + firma
        $sql = 'SELECT t.*, tr.departure_time, tr.company_id
                FROM tickets t
                JOIN trips tr ON tr.id = t.trip_id
                WHERE t.pnr = ?';
        $st = $this->pdo->prepare($sql);
        $st->execute([$pnr]);
        $t = $st->fetch();

        if (!$t) { http_response_code(404); echo 'Bilet bulunamadı.'; return; }

        // Yetki kontrolü
        if ((int)$me['role_id'] === 3) {
            if ((int)$t['user_id'] !== (int)$me['id']) { http_response_code(403); echo 'Yetkiniz yok.'; return; }
        } elseif ((int)$me['role_id'] === 2) {
            if ((int)$t['company_id'] !== (int)$me['company_id']) { http_response_code(403); echo 'Yetkiniz yok.'; return; }
        }

        // Zaman kuralı: kalkışa 1 saat kala iptal edilemez kuralı
        if (strtotime($t['departure_time']) - time() < 3600) {
            $_SESSION['flash_error'] = 'Sefer saatine 1 saatten az kaldığı için iptal edilemez.';
            redirect('/account/tickets');
        }

        if ($t['status'] !== 'PAID') {
            $_SESSION['flash_error'] = 'Yalnızca ödenmiş bilet iptal edilebilir.';
            redirect('/account/tickets');
        }

        // İade edilecek tutar
        $refund = max(0.0, (float)$t['price'] - (float)$t['discount_amount']);

        try {
            $this->pdo->beginTransaction();

            // Durum değişikliği
            $col = $this->cancelColumn();
$this->pdo->prepare("UPDATE tickets SET status='CANCELLED', $col = datetime('now') WHERE id = ?")
          ->execute([(int)$t['id']]);

            // Kullanıcı bakiyesine iade
            $this->pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
                      ->execute([$refund, (int)$t['user_id']]);

            
            $this->pdo->prepare('INSERT INTO wallet_transactions(user_id, amount, type, ticket_id, note)
                                 VALUES(?, ?, "REFUND", ?, "Ticket refund")')
                      ->execute([(int)$t['user_id'], $refund, (int)$t['id']]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $_SESSION['flash_error'] = 'İptal hatası: ' . e($e->getMessage());
            redirect('/account/tickets');
        }

        $_SESSION['flash_success'] = 'Bilet iptal edildi. ' . number_format($refund,2,',','.') . ' ₺ iade edildi.';
        redirect('/account/tickets');
    }

    public function showPNRForm(): void
{
    $ticket = null; $error = null;
    include VIEW_PATH . '/pnr/form.php';
}

public function pnrLookup(): void
{
    csrf_validate();

    $pnr   = strtoupper(trim((string)($_POST['pnr'] ?? '')));
    $email = trim((string)($_POST['email'] ?? ''));

    if ($pnr === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'PNR ve geçerli bir e-posta zorunludur.';
        $ticket = null;
        include VIEW_PATH . '/pnr/form.php';
        return;
    }

    $sql = 'SELECT
                t.pnr, t.status, t.seat_no, t.price, t.discount_amount,
                tr.departure_time, tr.arrival_time,
                cd.name AS dep_name, ca.name AS arr_name, c.name AS company_name
            FROM tickets t
            JOIN trips tr ON tr.id = t.trip_id
            JOIN cities cd ON cd.id = tr.departure_city_id
            JOIN cities ca ON ca.id = tr.arrival_city_id
            JOIN companies c ON c.id = tr.company_id
            JOIN users u ON u.id = t.user_id
            WHERE t.pnr = ? AND u.email = ?';
    $st = $this->pdo->prepare($sql);
    $st->execute([$pnr, $email]);
    $ticket = $st->fetch();

    if (!$ticket) {
        $error = 'Bilet bulunamadı. PNR ve e-postayı kontrol edin.';
        include VIEW_PATH . '/pnr/form.php';
        return;
    }

    include VIEW_PATH . '/pnr/form.php';
}

    /**Yalnız User PDF indirebilir */
    public function downloadPdf(): void
    {
        require_login([3]); // sadece user
        $me  = current_user();
        $pnr = trim((string)($_GET['pnr'] ?? ''));
        if ($pnr === '') { http_response_code(400); echo 'PNR gerekli.'; return; }

        $sql = 'SELECT
                    t.pnr, t.status, t.seat_no, t.price, t.discount_amount,
                    tr.departure_time, tr.arrival_time,
                    cd.name AS dep_name, ca.name AS arr_name, c.name AS company_name
                FROM tickets t
                JOIN trips tr ON tr.id = t.trip_id
                JOIN cities cd ON cd.id = tr.departure_city_id
                JOIN cities ca ON ca.id = tr.arrival_city_id
                JOIN companies c ON c.id = tr.company_id
                WHERE t.pnr = ? AND t.user_id = ?';
        $st = $this->pdo->prepare($sql);
        $st->execute([$pnr, (int)$me['id']]);
        $row = $st->fetch();

        if (!$row) { http_response_code(404); echo 'Bilet bulunamadı.'; return; }

        $final = max(0.0, (float)$row['price'] - (float)$row['discount_amount']);

        
        $html = '
        <html><head><meta charset="UTF-8">
        <style>
            body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;}
            .box{border:1px solid #444;padding:12px;border-radius:6px;}
            .h1{font-size:18px;font-weight:700;margin-bottom:6px}
            .row{display:flex;gap:12px}
            .col{flex:1}
            .muted{color:#666}
            .mt{margin-top:8px}
            .right{text-align:right}
            .hr{border-top:1px solid #ddd;margin:8px 0}
        </style></head><body>
          <div class="box">
            <div class="h1">ATSBİLET • Elektronik Bilet</div>
            <div class="row">
              <div class="col">
                <div>PNR: <strong>'.htmlspecialchars($row["pnr"]).'</strong></div>
                <div class="muted">Durum: '.htmlspecialchars($row["status"]).'</div>
              </div>
              <div class="col right">
                <div>Firma: <strong>'.htmlspecialchars($row["company_name"]).'</strong></div>
              </div>
            </div>

            <div class="hr"></div>

            <div class="row">
              <div class="col">
                <div class="muted">Güzergâh</div>
                <div><strong>'.htmlspecialchars($row["dep_name"]).' → '.htmlspecialchars($row["arr_name"]).'</strong></div>
              </div>
              <div class="col">
                <div class="muted">Koltuk</div>
                <div><strong>#'.(int)$row["seat_no"].'</strong></div>
              </div>
            </div>

            <div class="row mt">
              <div class="col">
                <div class="muted">Kalkış</div>
                <div><strong>'.date('d.m.Y H:i', strtotime($row["departure_time"])).'</strong></div>
              </div>
              <div class="col">
                <div class="muted">Varış</div>
                <div><strong>'.date('d.m.Y H:i', strtotime($row["arrival_time"])).'</strong></div>
              </div>
            </div>

            <div class="hr"></div>

            <div class="row">
              <div class="col">
                <div class="muted">Ödenen</div>
                <div><strong>'.number_format($final,2,',','.').' ₺</strong></div>';
        if ((float)$row["discount_amount"] > 0) {
            $html .= '<div class="muted">(<s>'.number_format((float)$row["price"],2,',','.').' ₺</s> − '.number_format((float)$row["discount_amount"],2,',','.').' ₺ kupon)</div>';
        }
        $html .= '
              </div>
              <div class="col right">
                <div class="muted">Oluşturma</div>
                <div>'.date('d.m.Y H:i').'</div>
              </div>
            </div>
          </div>
        </body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('bilet-' . $row['pnr'] . '.pdf', ['Attachment' => true]);
    }
}
