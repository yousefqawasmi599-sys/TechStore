<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/public/config.php';
$email=$argv[1] ?? getenv('ADMIN_EMAIL');
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>100) exit("Usage: php tools/setup-admin.php your-email@example.com\n");
$stmt=$pdo->prepare('SELECT user_type FROM users WHERE email=?'); $stmt->execute([$email]);
$existing=$stmt->fetchColumn();
if ($existing) exit("Account already exists. No password or permission was changed.\n");
$password=bin2hex(random_bytes(12));
$stmt=$pdo->prepare("INSERT INTO users(name,email,password,user_type,role) VALUES (?,?,?,'admin','admin')");
$stmt->execute(['مدير العرض',$email,password_hash($password,PASSWORD_DEFAULT)]);
$private=dirname(__DIR__).'/.runtime';
if(!is_dir($private)) mkdir($private,0700,true);
file_put_contents($private.'/admin-login.txt',"TechStore local administrator\nEmail: $email\nPassword: $password\nLogin: http://127.0.0.1:8000/login.php\nKeep this file private. It is excluded from the GitHub package.\n");
echo "Admin created. Credentials saved privately in .runtime/admin-login.txt\n";
