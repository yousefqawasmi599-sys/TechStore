<?php
require __DIR__ . '/../config.php';
require_admin();
require_post();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) fail_request('معرّف المنتج غير صالح.');
try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
} catch (PDOException $error) {
    if ($error->getCode() === '23000') fail_request('لا يمكن حذف منتج مرتبط بطلبات سابقة.', 409);
    throw $error;
}
header('Location: products.php');
exit;
