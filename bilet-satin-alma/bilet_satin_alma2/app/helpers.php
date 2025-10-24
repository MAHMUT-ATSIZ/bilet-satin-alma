<?php
use App\Core\DB;


function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }


function csrf_token(): string {
if (empty($_SESSION['csrf'])) {
$_SESSION['csrf'] = bin2hex(random_bytes(32));
}
return $_SESSION['csrf'];
}


function csrf_field(): string {
return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}


function csrf_validate(): void {
if (($_POST['csrf'] ?? '') !== ($_SESSION['csrf'] ?? '')) {
http_response_code(419);
exit('CSRF doğrulaması başarısız.');
}
}


function redirect(string $to): void {
header('Location: ' . $to);
exit;
}


function current_user(): ?array { return $_SESSION['user'] ?? null; }


function require_login(array $allowed_roles = null): void {
    if (empty($_SESSION['user'])) {
        redirect('/auth/login');
    }
    if ($allowed_roles !== null) {
        $role_id = (int)($_SESSION['user']['role_id'] ?? 0);
        if (!in_array($role_id, $allowed_roles, true)) {
            http_response_code(403);
            echo 'Yetkisiz erişim.';
            exit;
        }
    }
}
function role_name(int $role_id): string {
    return match ($role_id) {
        1 => 'Admin',
        2 => 'Firma Admin',
        default => 'Kullanıcı',
    };}
function generate_pnr(int $len = 6): string {
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$s = '';
for ($i=0; $i<$len; $i++) {
$s .= $alphabet[random_int(0, strlen($alphabet)-1)];
}
return $s;
}