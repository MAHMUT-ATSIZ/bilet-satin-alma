<?php
namespace App\Controllers;

use App\Core\DB;

class CompanyUserController
{
    private \PDO $pdo;
    public function __construct() { $this->pdo = DB::pdo(); }

    private function requireManager(): array {
        // 1 = Sistem admin, 2 = Firma admin
        require_login([1,2]);
        return current_user();
    }

    
    public function index(): void
    {
        $u = $this->requireManager();

        $q = trim((string)($_GET['q'] ?? ''));
        $users = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $st = $this->pdo->prepare('SELECT id, fullname, email, balance FROM users WHERE email LIKE ? OR fullname LIKE ? ORDER BY email');
            $st->execute([$like, $like]);
            $users = $st->fetchAll();
        }

        include VIEW_PATH . '/company/users/index.php';
    }
}
