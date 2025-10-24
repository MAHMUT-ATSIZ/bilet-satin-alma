<?php
namespace App\Controllers;

use App\Core\DB;

class AuthController
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = DB::pdo();
    }

    // GET login
    public function showLogin(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        include VIEW_PATH . '/auth/login.php';
    }

    // POST login
    public function login(): void
    {
        csrf_validate();

        $now = time();
        $lim = $_SESSION['login_limit'] ?? ['count'=>0, 'reset_at'=>$now + 900];
        if ($now > ($lim['reset_at'] ?? 0)) $lim = ['count'=>0, 'reset_at'=>$now + 900];
        if ($lim['count'] >= 5) {
            $_SESSION['flash_error'] = 'Çok fazla deneme. Lütfen biraz sonra tekrar deneyin.';
            redirect('/auth/login');
        }

        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        if ($email === '' || $pass === '') {
            $_SESSION['flash_error'] = 'E-posta ve parola zorunludur.';
            redirect('/auth/login');
        }

        $stmt = $this->pdo->prepare('SELECT id, fullname, email, password_hash, role_id, company_id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($pass, $user['password_hash'])) {
            $lim['count']++;
            $_SESSION['login_limit'] = $lim;
            $_SESSION['flash_error'] = 'Geçersiz bilgiler.';
            redirect('/auth/login');
        }

        $_SESSION['login_limit'] = ['count'=>0, 'reset_at'=>time()+900];
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => (int)$user['id'],
            'fullname'   => $user['fullname'],
            'email'      => $user['email'],
            'role_id'    => (int)$user['role_id'],
            'company_id' => $user['company_id'] ? (int)$user['company_id'] : null,
        ];
        redirect('/');
    }

    // GET register
    public function showRegister(): void
    {
        $error = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_error']);

        include VIEW_PATH . '/auth/register.php';
    }

    // POST register
    public function register(): void
    {
        csrf_validate();

        $fullname = trim($_POST['fullname'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $pass     = $_POST['password']  ?? '';
        $pass2    = $_POST['password2'] ?? '';

        if ($fullname === '' || $email === '' || $pass === '' || $pass2 === '') {
            $_SESSION['flash_error'] = 'Tüm alanlar zorunludur.';
            redirect('/auth/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Geçerli bir e-posta girin.';
            redirect('/auth/register');
        }
        if ($pass !== $pass2 || strlen($pass) < 8) {
            $_SESSION['flash_error'] = 'Parolalar eşleşmeli ve en az 8 karakter olmalı.';
            redirect('/auth/register');
        }

        $exists = $this->pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $exists->execute([$email]);
        if ($exists->fetchColumn()) {
            $_SESSION['flash_error'] = 'Bu e-posta zaten kayıtlı.';
            redirect('/auth/register');
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $ins  = $this->pdo->prepare('INSERT INTO users(fullname, email, password_hash, role_id, company_id) VALUES(?,?,?,?,NULL)');
        $ins->execute([$fullname, $email, $hash, 3]); // 3=User

        $id = (int)$this->pdo->lastInsertId();
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => $id,
            'fullname'   => $fullname,
            'email'      => $email,
            'role_id'    => 3,
            'company_id' => null,
        ];
        redirect('/');
    }

    // POST logout
    public function logout(): void
    {
        csrf_validate();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        redirect('/');
    }
}
