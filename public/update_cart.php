<?php
require __DIR__ . '/bootstrap.php';
require_post();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action = $_POST['action'] ?? '';
if (!$id || !in_array($action, ['increase', 'decrease'], true)) fail_request('طلب غير صالح.');
if (isset($_SESSION['cart'][$id])) {
    $qty = (int)$_SESSION['cart'][$id] + ($action === 'increase' ? 1 : -1);
    if ($qty <= 0) unset($_SESSION['cart'][$id]);
    else $_SESSION['cart'][$id] = min(99, $qty);
}
header('Location: cart.php');
exit;
