<?php
if (PHP_SAPI !== 'cli') exit;
$admin = json_decode(file_get_contents(dirname(__DIR__) . '/.runtime/admin.json'), true, 512, JSON_THROW_ON_ERROR);
$pdo = new PDO("mysql:host={$admin['host']};port={$admin['port']};dbname={$admin['name']};charset=utf8mb4",$admin['username'],$admin['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE IF NOT EXISTS auth_attempts (attempt_key CHAR(64) NOT NULL PRIMARY KEY, attempts INT UNSIGNED NOT NULL, reset_at BIGINT UNSIGNED NOT NULL, KEY reset_at(reset_at)) ENGINE=InnoDB');
echo "Local migrations applied.\n";
