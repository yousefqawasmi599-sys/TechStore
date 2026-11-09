<?php
if (PHP_SAPI !== 'cli') exit;
$root = dirname(__DIR__);
$admin = json_decode(file_get_contents($root . '/.runtime/admin.json'), true, 512, JSON_THROW_ON_ERROR);
$app = json_decode(file_get_contents($root . '/.runtime/database.json'), true, 512, JSON_THROW_ON_ERROR);
$pdo = new PDO("mysql:host={$admin['host']};port={$admin['port']};charset=utf8mb4", $admin['username'], $admin['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$exists = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.schemata WHERE schema_name='tech_store'")->fetchColumn();
if (!$exists) {
    $backup = $root . '/database/schema.sql';
    if (!is_file($backup)) throw new RuntimeException('database/schema.sql is missing. No database was created.');
    $pdo->exec('CREATE DATABASE tech_store CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $pdo->exec('USE tech_store');
    $pdo->exec(file_get_contents($backup));
    $pdo->exec(file_get_contents($root . '/database/seed.sql'));
} else {
    $pdo->exec('USE tech_store');
    foreach (['brands','products','orders','order_items','users'] as $table) $pdo->query("SELECT id FROM $table LIMIT 1");
    echo "Existing database verified; no data overwritten.\n";
}
$user = $pdo->quote($app['username']);
$password = $pdo->quote($app['password']);
$pdo->exec("CREATE USER IF NOT EXISTS $user@'127.0.0.1' IDENTIFIED BY $password");
$pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON tech_store.* TO $user@'127.0.0.1'");
echo "Imported recovered data and created a restricted application account.\n";
