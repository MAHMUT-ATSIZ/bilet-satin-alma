<?php
namespace App\Controllers;

use App\Core\DB;

class AdminCompanyController
{
    private \PDO $pdo;
    public function __construct() { $this->pdo = DB::pdo(); }

    /** Sadece sistem admini */
    private function requireSystemAdmin(): array
    {
        require_login([1]); // 1 = Sistem Admini
        return current_user();
    }

    /** GET Firma listesi  */
    public function index(): void
    {
        $this->requireSystemAdmin();

        $companies = $this->pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();

        include VIEW_PATH . '/admin/companies/index.php';
    }

    /** POST Firma ekle */
    public function create(): void
    {
        $this->requireSystemAdmin();
        csrf_validate();

        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            $_SESSION['flash_error'] = 'Firma adı zorunludur.';
            redirect('/admin/firms');
        }

        // Aynı ad var mı?
        $chk = $this->pdo->prepare('SELECT 1 FROM companies WHERE name = ?');
        $chk->execute([$name]);
        if ($chk->fetchColumn()) {
            $_SESSION['flash_error'] = 'Bu adla bir firma zaten var.';
            redirect('/admin/firms');
        }

        try {
            $ins = $this->pdo->prepare('INSERT INTO companies(name) VALUES(?)');
            $ins->execute([$name]);
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Ekleme hatası: ' . e($e->getMessage());
            redirect('/admin/firms');
        }

        $_SESSION['flash_success'] = 'Firma eklendi.';
        redirect('/admin/firms');
    }

    /** POST Firma sil */
    public function delete(): void
{
    $this->requireSystemAdmin();
    csrf_validate();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { $_SESSION['flash_error'] = 'Geçersiz firma.'; redirect('/admin/firms'); }

    // Gelecek sefer var mı?
    $now = date('Y-m-d H:i:s');
    $hasFuture = $this->pdo->prepare('SELECT 1 FROM trips WHERE company_id = ? AND departure_time >= ? LIMIT 1');
    $hasFuture->execute([$id, $now]);
    if ($hasFuture->fetchColumn()) {
        $_SESSION['flash_error'] = 'Bu firmaya ait gelecekte sefer var. Silinemez.';
        redirect('/admin/firms');
    }

    try {
        $this->pdo->beginTransaction();

        // Firma adminlerini kullanıcı yap
        $this->pdo->prepare('UPDATE users SET role_id=3, company_id=NULL WHERE company_id=?')->execute([$id]);

        // Bu firmanın tüm biletleri
        $this->pdo->prepare('DELETE FROM tickets WHERE trip_id IN (SELECT id FROM trips WHERE company_id=?)')
                  ->execute([$id]);
        $this->pdo->prepare('DELETE FROM trips WHERE company_id=?')
                  ->execute([$id]);

        // Firma kuponları
        $this->pdo->prepare('DELETE FROM coupons WHERE company_id=?')->execute([$id]);

        // Firmalar
        $this->pdo->prepare('DELETE FROM companies WHERE id=?')->execute([$id]);

        $this->pdo->commit();
    } catch (\Throwable $e) {
        $this->pdo->rollBack();
        $_SESSION['flash_error'] = 'Silme hatası: ' . e($e->getMessage());
        redirect('/admin/firms');
    }

    $_SESSION['flash_success'] = 'Firma ve ilişkili verileri temizlendi.';
    redirect('/admin/firms');
}

}
