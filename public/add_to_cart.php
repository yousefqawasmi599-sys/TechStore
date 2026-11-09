<?php
require __DIR__ . '/config.php';
require_post();
$id = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) fail_request('معرّف المنتج غير صالح.');
$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetchColumn()) fail_request('المنتج غير موجود.', 404);
cart_products($pdo);
$_SESSION['cart'][$id] = min(99, ($_SESSION['cart'][$id] ?? 0) + 1);
if (isset($_POST['redirect'])) { header('Location: cart.php'); exit; }
echo 'success';
