<?php
return [
  'db_path'  => __DIR__ . '/../storage/database.sqlite',
  'base_url' => '/',         
  'app_env'  => 'dev',       
  'timezone' => 'Europe/Istanbul',
  'session'  => [
    'name'            => 'bilet_sid',
    'cookie_lifetime' => 0,
    'cookie_secure'   => false,   // HTTPS kullanınca true yap
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
  ],
];
