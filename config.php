<?php
/**
 * Çelik Fabrikası Yönetim Sistemi - Temel Konfigürasyon
 * XAMPP ortamı için basitleştirilmiş versiyon
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Türkçe karakter desteği
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_NAME', 'kk_cansan');
define('DB_USER', 'root');
define('DB_PASS', '');

// Uygulama ayarları
define('APP_NAME', 'Çelik Fabrikası Yönetim Sistemi');
define('APP_URL', 'http://localhost/cansan/kk-cansan');
define('APP_DEBUG', true);

// Session ayarları
session_start();

// Veritabanı bağlantısı
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yardımcı fonksiyonlar
function redirect($url) {
    header("Location: $url");
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function now() {
    return date('Y-m-d H:i:s');
}

function today() {
    return date('Y-m-d');
}
?>
