<?php
namespace App\Controllers;

use App\Core\DB;

class AccountController
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::pdo();
    }

    // GET /account/profile
    public function profile(): void
    {
        require_login();
        $me = current_user();

        
        $st = $this->pdo->prepare('SELECT id, fullname, email, role_id, balance FROM users WHERE id = ?');
        $st->execute([(int)$me['id']]);
        $user = $st->fetch();

        if (!$user) { http_response_code(404); echo 'Kullanıcı bulunamadı.'; return; }

        include VIEW_PATH . '/account/profile.php';
    }

    // POST /account/balance/add
    public function addBalance(): void
    {
        require_login();
        csrf_validate();

        $u = current_user();
        $amount = (float)($_POST['amount'] ?? 0);

    
        $rawCard = (string)($_POST['card_number'] ?? '');
        $card    = preg_replace('/\D+/', '', $rawCard);
        $expiry  = (string)($_POST['expiry'] ?? '');
        $cvv     = (string)($_POST['cvv'] ?? '');

        if ($amount <= 0) {
            $_SESSION['flash_error'] = 'Eklenecek tutar pozitif olmalı.';
            redirect('/account/profile');
        }
        if (strlen($card) !== 16) {
            $_SESSION['flash_error'] = 'Kart numarası 16 hane olmalı.';
            redirect('/account/profile');
        }
        if (!preg_match('/^\d{4}-\d{2}$/', $expiry)) {
            $_SESSION['flash_error'] = 'Son kullanma tarihi geçersiz (YYYY-AA).';
            redirect('/account/profile');
        }
        $expTs  = strtotime($expiry . '-01 00:00:00');
        $nowMon = strtotime(date('Y-m-01 00:00:00'));
        if ($expTs <= $nowMon) {
            $_SESSION['flash_error'] = 'Son kullanma tarihi ileri bir ay olmalı.';
            redirect('/account/profile');
        }
        if (!preg_match('/^\d{3}$/', $cvv)) {
            $_SESSION['flash_error'] = 'CVV 3 hane olmalı.';
            redirect('/account/profile');
        }

        try {
            $this->pdo->beginTransaction();

            $upd = $this->pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
            $upd->execute([$amount, $u['id']]);

            $w = $this->pdo->prepare('INSERT INTO wallet_transactions(user_id, amount, type, note) VALUES(?, ?, "DEPOSIT", "Manual top-up")');
            $w->execute([$u['id'], $amount]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo 'Bakiye ekleme hatası: ' . e($e->getMessage());
            return;
        }

        $_SESSION['flash_success'] = 'Bakiye eklendi.';
        redirect('/account/profile');
    }
}
