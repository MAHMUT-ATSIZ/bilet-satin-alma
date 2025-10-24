<?php
namespace App\Controllers;

use App\Core\DB;

class CompanyCouponController
{
    private \PDO $pdo;

    public function __construct()
    {
        require_login([1,2]); // 1: Sistem Admini, 2: Firma Admini
        $this->pdo = DB::pdo();
    }

    public function index(): void
    {
        $me = current_user();
        $isAdmin = ((int)$me['role_id'] === 1);

        if ($isAdmin) {
            $sql = 'SELECT c.*, co.name AS company_name
                    FROM coupons c
                    LEFT JOIN companies co ON co.id = c.company_id
                    ORDER BY c.created_at DESC, c.id DESC';
            $coupons = $this->pdo->query($sql)->fetchAll();

        } else {
            $sql = 'SELECT c.*, co.name AS company_name
                    FROM coupons c
                    JOIN companies co ON co.id = c.company_id
                    WHERE c.company_id = ?
                    ORDER BY c.created_at DESC, c.id DESC';
            $st = $this->pdo->prepare($sql);
            $st->execute([(int)$me['company_id']]);
            $coupons = $st->fetchAll();
        }

        include VIEW_PATH . '/company/coupons/index.php';
    }

    public function createForm(): void
    {
        $me = current_user();
        $isAdmin = ((int)$me['role_id'] === 1);
        $companies = [];

        if ($isAdmin) {
            $companies = $this->pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();
        }

        include VIEW_PATH . '/company/coupons/form.php';
    }

    public function create(): void
    {
        require_login([1,2]);
        csrf_validate();

        $me = current_user();
        $isAdmin = ((int)$me['role_id'] === 1);

        // company_id: admin seçer, firma admini kendi firması
        if ($isAdmin) {
    $raw = trim((string)($_POST['company_id'] ?? ''));
    if ($raw === '__ALL__') {
        $companyId = null; 
    } else {
        $companyId = (int)$raw;
        if ($companyId <= 0) { $_SESSION['flash_error']='Lütfen firma seçin.'; redirect('/firma/kupon/ekle'); }
        $exists = $this->pdo->prepare('SELECT 1 FROM companies WHERE id=?');
        $exists->execute([$companyId]);
        if (!$exists->fetchColumn()) { $_SESSION['flash_error']='Firma bulunamadı.'; redirect('/firma/kupon/ekle'); }
    }
} else {
    $companyId = (int)$me['company_id'];
}


        // normalize
        $code     = $this->normalizeCoupon($_POST['code'] ?? '');
        $percent  = (int)($_POST['discount_percent'] ?? 0);
        $startsAt = $this->normalizeLocalDateTime($_POST['starts_at'] ?? '', false);
        $endsAt   = $this->normalizeLocalDateTime($_POST['ends_at']   ?? '', true);
        $maxUses  = trim((string)($_POST['max_uses'] ?? '')) === '' ? null : (int)$_POST['max_uses'];
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if ($code === '' || $percent < 1 || $percent > 90) {
            $_SESSION['flash_error'] = 'Kod zorunlu; indirim %1-%90 arası olmalı.';
            redirect('/firma/kupon/ekle');
        }

        // aynı firmada aynı kod tekrar etmesin
        $dupe = $this->pdo->prepare('SELECT 1 FROM coupons WHERE company_id=? AND code=?');
        $dupe->execute([$companyId, $code]);
        if ($dupe->fetchColumn()) {
            $_SESSION['flash_error'] = 'Bu kod zaten mevcut.';
            redirect('/firma/kupon/ekle');
        }

        $st = $this->pdo->prepare('INSERT INTO coupons(company_id, code, discount_percent, starts_at, ends_at, max_uses, used_count, is_active, created_at)
                                   VALUES(?,?,?,?,?,?,0,?, datetime("now"))');
        $st->execute([$companyId, $code, $percent, $startsAt, $endsAt, $maxUses, $isActive]);

        $_SESSION['flash_success'] = 'Kupon oluşturuldu.';
        redirect('/firma/kuponlar');
    }

    public function toggle(): void
    {
        csrf_validate();
        $me = current_user();
        $isAdmin = ((int)$me['role_id'] === 1);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/firma/kuponlar'); }

        $row = $this->pdo->prepare('SELECT * FROM coupons WHERE id=?');
        $row->execute([$id]);
        $c = $row->fetch();
        if (!$c) { redirect('/firma/kuponlar'); }

        if (!$isAdmin && (int)$c['company_id'] !== (int)$me['company_id']) {
            $_SESSION['flash_error'] = 'Yetkiniz yok.';
            redirect('/firma/kuponlar');
        }

        $new = ((int)$c['is_active'] ? 0 : 1);
        $this->pdo->prepare('UPDATE coupons SET is_active=? WHERE id=?')->execute([$new, $id]);

        redirect('/firma/kuponlar');
    }

    public function delete(): void
    {
        csrf_validate();
        $me = current_user();
        $isAdmin = ((int)$me['role_id'] === 1);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/firma/kuponlar'); }

        $row = $this->pdo->prepare('SELECT * FROM coupons WHERE id=?');
        $row->execute([$id]);
        $c = $row->fetch();
        if (!$c) { redirect('/firma/kuponlar'); }

        if (!$isAdmin && (int)$c['company_id'] !== (int)$me['company_id']) {
            $_SESSION['flash_error'] = 'Yetkiniz yok.';
            redirect('/firma/kuponlar');
        }

        $this->pdo->prepare('DELETE FROM coupons WHERE id=?')->execute([$id]);
        redirect('/firma/kuponlar');
    }

    /* ===== helpers ===== */

    private function normalizeCoupon(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        return preg_replace('/[^A-Z0-9]/', '', $raw);
    }

    /** $isEnd=true ise tarih-only için 23:59:59 ekler. */
    private function normalizeLocalDateTime(?string $in, bool $isEnd=false): ?string
    {
        $in = trim((string)$in);
        if ($in === '') return null;
        $in = str_replace('T',' ',$in);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $in)) {
            return $in . ($isEnd ? ' 23:59:59' : ' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $in)) {
            $in .= ':00';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $in)) {
            return null;
        }
        return $in;
    }
}
