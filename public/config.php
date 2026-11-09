<?php
require_once __DIR__ . '/bootstrap.php';
try {
    $localPath = dirname(__DIR__) . '/.runtime/database.json';
    $local = is_file($localPath) ? json_decode(file_get_contents($localPath), true, 512, JSON_THROW_ON_ERROR) : [];
    $host = getenv('DB_HOST') ?: ($local['host'] ?? '127.0.0.1');
    $port = getenv('DB_PORT') ?: ($local['port'] ?? '3310');
    $dbname = getenv('DB_NAME') ?: ($local['name'] ?? 'tech_store');
    $username = getenv('DB_USER') ?: ($local['username'] ?? 'tech_store_app');
    $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($local['password'] ?? '');
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
} catch (PDOException $error) {
    error_log($error->getMessage());
    fail_request('تعذر الاتصال بقاعدة البيانات. حاول لاحقًا.', 503);
}
