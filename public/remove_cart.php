<?php
require __DIR__ . '/bootstrap.php';
require_post();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) fail_request('معرّف المنتج غير صالح.');
unset($_SESSION['cart'][$id]);
header('Location: cart.php');
exit;
