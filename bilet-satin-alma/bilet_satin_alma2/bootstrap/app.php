<?php
$config = require __DIR__ . '/../config/config.php';

define('BASE_PATH', dirname(__DIR__));           
define('APP_PATH',  BASE_PATH . '/app');         
define('VIEW_PATH', APP_PATH . '/Views');        

// Saat dilimini ayarlıyoruz
date_default_timezone_set($config['timezone'] ?? 'Europe/Istanbul');

// Hata var ise burada gösteriyoruz
if (($config['app_env'] ?? 'dev') === 'dev') {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// Güvenli oturum için koşul sorgumuz
if (session_status() === PHP_SESSION_NONE) {
  $s = $config['session'] ?? [];
  session_set_cookie_params([
    'lifetime' => $s['cookie_lifetime'] ?? 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $s['cookie_secure'] ?? false,
    'httponly' => $s['cookie_httponly'] ?? true,
    'samesite' => $s['cookie_samesite'] ?? 'Strict'
  ]);
  if (!empty($s['name'])) {
    session_name($s['name']);
  }
  session_start();

  // CSRF token üretiyoruz
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
}
