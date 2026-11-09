<?php
if (PHP_SAPI !== 'cli') exit;
$runtime = dirname(__DIR__) . '/.runtime';
if (!is_dir($runtime)) mkdir($runtime, 0700, true);
if (is_file($runtime . '/admin.json')) exit("Local credentials already prepared.\n");
$root = ['host'=>'127.0.0.1','port'=>3310,'name'=>'tech_store','username'=>'root','password'=>bin2hex(random_bytes(24))];
$app = $root;
$app['username'] = 'tech_store_app';
$app['password'] = bin2hex(random_bytes(24));
file_put_contents($runtime . '/admin.json', json_encode($root, JSON_PRETTY_PRINT));
file_put_contents($runtime . '/database.json', json_encode($app, JSON_PRETTY_PRINT));
file_put_contents($runtime . '/admin-client.ini', "[client]\nhost=127.0.0.1\nport=3310\nuser=root\npassword=" . $root['password'] . "\n");
echo "Local credentials prepared outside the web root.\n";
