<?php
namespace App\Controllers;

use App\Core\DB;

class AdminUserController
{
    private \PDO $pdo;
    public function __construct() { $this->pdo = DB::pdo(); }

    /** Sadece sistem adminine izin verilen kısım */
    private function requireSystemAdmin(): array
    {
        
        require_login([1]); 
        return current_user();
    }

    /** GET kullanıcıları listele ve ara */
    public function index(): void
    {
        $admin = $this->requireSystemAdmin();

        $q           = trim((string)($_GET['q'] ?? ''));
        $roleFilter  = (int)($_GET['role'] ?? 0);       
        $companyIdF  = (int)($_GET['company_id'] ?? 0); 

        $where  = 'WHERE 1=1 ';
        $params = [];

        if ($q !== '') {
            $where .= 'AND (u.email LIKE ? OR u.fullname LIKE ?) ';
            $like = '%'.$q.'%';
            $params[] = $like; $params[] = $like;
        }
        if (in_array($roleFilter, [2,3], true)) {
            $where .= 'AND u.role_id = ? ';
            $params[] = $roleFilter;
        }
        if ($companyIdF > 0) {
            $where .= 'AND u.company_id = ? ';
            $params[] = $companyIdF;
        }

        $sql = "SELECT u.id, u.fullname, u.email, u.role_id, u.company_id, u.balance,
                       c.name AS company_name
                FROM users u
                LEFT JOIN companies c ON c.id = u.company_id
                $where
                ORDER BY u.id DESC";
        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        $users = $st->fetchAll();

        $companies = $this->pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();

        include VIEW_PATH . '/admin/users/index.php';
    }

    /** POST Firma ve rol ataması */
    public function updateRole(): void
    {
        $admin = $this->requireSystemAdmin();
        csrf_validate();

        $userId    = (int)($_POST['user_id'] ?? 0);
        $newRole   = (int)($_POST['role_id'] ?? 0); 
        $companyId = $_POST['company_id'] ?? null;

        if ($userId <= 0 || !in_array($newRole, [2,3], true)) {
            $_SESSION['flash_error'] = 'Geçersiz istek.';
            redirect('/admin/users');
        }

        if ($userId === (int)$admin['id']) {
            $_SESSION['flash_error'] = 'Kendi rolünüzü değiştiremezsiniz.';
            redirect('/admin/users');
        }

        // Kullanıcı var mı?
        $u = $this->pdo->prepare('SELECT id FROM users WHERE id = ?');
        $u->execute([$userId]);
        if (!$u->fetchColumn()) {
            $_SESSION['flash_error'] = 'Kullanıcı bulunamadı.';
            redirect('/admin/users');
        }

        // Firma admini atanıyorsa ise şirket kontrolü zorunlu
        $companySet = null;
        if ($newRole === 2) {
            $cid = (int)($companyId ?? 0);
            if ($cid <= 0) {
                $_SESSION['flash_error'] = 'Firma admini atarken bir firma seçmelisiniz.';
                redirect('/admin/users');
            }
            $c = $this->pdo->prepare('SELECT id FROM companies WHERE id = ?');
            $c->execute([$cid]);
            if (!$c->fetchColumn()) {
                $_SESSION['flash_error'] = 'Firma bulunamadı.';
                redirect('/admin/users');
            }
            $companySet = $cid;
        }

        // Güncelle
        $sql = ($newRole === 2)
            ? 'UPDATE users SET role_id = 2, company_id = ? WHERE id = ?'
            : 'UPDATE users SET role_id = 3, company_id = NULL WHERE id = ?';

        try {
            if ($newRole === 2) {
                $st = $this->pdo->prepare($sql);
                $st->execute([$companySet, $userId]);
            } else {
                $st = $this->pdo->prepare($sql);
                $st->execute([$userId]);
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Güncelleme hatası: ' . e($e->getMessage());
            redirect('/admin/users');
        }

        $_SESSION['flash_success'] = 'Kullanıcı rolü güncellendi.';
        redirect('/admin/users');
    }
}
