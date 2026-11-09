<?php
require __DIR__ . '/config.php';
require_post();
$name = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
$phone = trim(is_string($_POST['phone'] ?? null) ? $_POST['phone'] : '');
$address = trim(is_string($_POST['address'] ?? null) ? $_POST['address'] : '');
if ($name === '' || mb_strlen($name) > 100 || !preg_match('/^[+0-9 ()-]{7,20}$/', $phone) || $address === '' || mb_strlen($address) > 2000) fail_request('تحقق من الاسم ورقم الهاتف والعنوان.');
try {
    $pdo->beginTransaction();
    $products = cart_products($pdo);
    if (!$products || count($products) !== count($_SESSION['cart'])) {
        $pdo->rollBack();
        fail_request('بعض المنتجات لم تعد متاحة. راجع السلة.');
    }
    $totalCents = 0;
    foreach ($products as $product) $totalCents += (int)round((float)$product['price'] * 100) * $_SESSION['cart'][$product['id']];
    if ($totalCents <= 0 || $totalCents > 9999999999) {
        $pdo->rollBack();
        fail_request('قيمة الطلب غير صالحة. راجع السلة.');
    }
    $stmt = $pdo->prepare("INSERT INTO orders (name, phone, address, total_amount, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$name, $phone, $address, number_format($totalCents / 100, 2, '.', '')]);
    $orderId = $pdo->lastInsertId();
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
    foreach ($products as $product) $stmt->execute([$orderId, $product['id'], $_SESSION['cart'][$product['id']], $product['price']]);
    $pdo->commit();
    unset($_SESSION['cart']);
    $_SESSION['last_order_id'] = $orderId;
    header('Location: thank_you.php?order_id=' . $orderId);
    exit;
} catch (Throwable $error) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($error->getMessage());
    fail_request('تعذر حفظ الطلب. لم نفرغ سلتك؛ حاول مجددًا.', 500);
}
