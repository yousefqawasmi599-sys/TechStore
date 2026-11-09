<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/config.php';
$email = getenv('ADMIN_EMAIL');
$password = getenv('ADMIN_PASSWORD');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100 || !is_string($password) || strlen($password) < 12 || strlen($password) > 72) exit("Set ADMIN_EMAIL and ADMIN_PASSWORD (12 to 72 bytes).\n");
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type, role) VALUES (?, ?, ?, 'admin', 'admin')");
$stmt->execute(['المدير', $email, password_hash($password, PASSWORD_DEFAULT)]);
echo "Admin account created.\n";
