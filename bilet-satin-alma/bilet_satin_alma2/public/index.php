<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap/app.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\TripController;
use App\Controllers\PurchaseController;
use App\Controllers\TicketController;
use App\Controllers\CompanyTripController;
use App\Controllers\AccountController;
use App\Controllers\CompanyCouponController;
use App\Controllers\CompanyTicketController;
use App\Controllers\CompanyUserController;
use App\Controllers\AdminUserController;
use App\Controllers\AdminCompanyController;

$router = new Router();



$router->get('/firma/seferler', [CompanyTripController::class, 'index']);
$router->get('/firma/sefer/ekle', [CompanyTripController::class, 'createForm']);
$router->post('/firma/sefer/ekle', [CompanyTripController::class, 'create']);
$router->get('/firma/sefer/duzenle', [CompanyTripController::class, 'editForm']);
$router->post('/firma/sefer/duzenle', [CompanyTripController::class, 'update']);
$router->post('/firma/sefer/sil', [CompanyTripController::class, 'delete']);

$router->get('/account/tickets', [TicketController::class, 'myTickets']);
$router->get('/account/ticket/pdf', [TicketController::class, 'downloadPdf']);


$router->get('/pnr', [TicketController::class, 'showPNRForm']);
$router->post('/pnr', [TicketController::class, 'pnrLookup']);

$router->post('/ticket/cancel', [TicketController::class, 'cancel']);
// --- Sefer arama / listeleme (anasayfa) ---
$router->get('/',         [TripController::class, 'searchForm']);
$router->get('/seferler', [TripController::class, 'list']);
$router->get('/account/profile',     [AccountController::class, 'profile']);
$router->post('/account/balance/add',[AccountController::class, 'addBalance']);
$router->get('/firma/kuponlar',     [CompanyCouponController::class, 'index']);
$router->get('/firma/kupon/ekle',   [CompanyCouponController::class, 'createForm']);
$router->post('/firma/kupon/ekle',  [CompanyCouponController::class, 'create']);
$router->post('/firma/kupon/toggle',[CompanyCouponController::class, 'toggle']);
$router->post('/firma/kupon/sil',   [CompanyCouponController::class, 'delete']);
// Kullanıcı arama
$router->get('/firma/kullanicilar', [CompanyUserController::class, 'index']);
// Firma biletleri listesi + iptal 
$router->get('/firma/biletler',     [CompanyTicketController::class, 'index']);
$router->post('/firma/bilet/iptal', [CompanyTicketController::class, 'cancel']);
// Sistem admini – kullanıcı yönetimi
$router->get('/admin/users',      [AdminUserController::class, 'index']);
$router->post('/admin/users/role',[AdminUserController::class, 'updateRole']);
// Sistem admini – firma yönetimi
$router->get('/admin/firms',        [AdminCompanyController::class, 'index']);
$router->post('/admin/firms/create',[AdminCompanyController::class, 'create']);
$router->post('/admin/firms/delete',[AdminCompanyController::class, 'delete']);





$router->get('/health', function () {
    header('Content-Type: application/json');
    try {
        $pdo = \App\Core\DB::pdo();
        $pdo->query('SELECT 1;');
        echo json_encode(['ok' => true, 'db' => 'connected']);
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    }
});

// --- Satın alma ---
$router->get('/buy',  [PurchaseController::class, 'form']);      
$router->post('/buy', [PurchaseController::class, 'purchase']);  

// --- Kimlik doğrulama ---
$router->get('/auth/login',    [AuthController::class, 'showLogin']);
$router->post('/auth/login',   [AuthController::class, 'login']);
$router->get('/auth/register', [AuthController::class, 'showRegister']);
$router->post('/auth/register',[AuthController::class, 'register']);
$router->post('/auth/logout',  [AuthController::class, 'logout']);


$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($method, $uri);
