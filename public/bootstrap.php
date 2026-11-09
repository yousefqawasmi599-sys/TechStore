<?php
set_exception_handler(static function (Throwable $error): void {
    error_log((string)$error);
    fail_request('تعذر إتمام العملية. حاول مجددًا.', 500);
});
date_default_timezone_set('Asia/Dubai');
if (is_dir(dirname(__DIR__) . '/.runtime')) ini_set('error_log', dirname(__DIR__) . '/.runtime/application.log');
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0');
    header('X-Frame-Options: DENY');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    foreach (array_merge($_GET, $_POST) as $value) {
        if (!is_string($value)) { http_response_code(400); exit('بيانات الطلب غير صالحة.'); }
    }
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function product_image($value): string {
    $path = (string)$value;
    if (str_starts_with($path, 'images/products/') && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/')) return '../' . $path;
    return $path;
}
function fail_request(string $message, int $status = 400): never {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($status);
    header('Content-Type: text/html; charset=UTF-8');
    $home = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') ? '../shop.php' : 'shop.php';
    exit('<!doctype html><html lang="ar" dir="rtl"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TechStore</title><body style="margin:0;background:#f8fafc;font:18px system-ui;color:#0f172a"><main style="max-width:600px;margin:12vh auto;padding:32px;background:white;border-radius:20px"><h1>تعذر إتمام الطلب</h1><p role="alert">' . e($message) . '</p><a href="' . $home . '">العودة إلى المتجر</a></main></body></html>');
}
function require_admin(): void {
    if (($_SESSION['user_role'] ?? '') !== 'admin') fail_request('هذه الصفحة متاحة للمدير فقط.', 403);
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'admin'");
    $stmt->execute([$_SESSION['user_id'] ?? 0]);
    if (!$stmt->fetchColumn()) {
        unset($_SESSION['user_role']);
        fail_request('انتهت صلاحية الدخول إلى الإدارة.', 403);
    }
}
function require_post(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST');
        fail_request('يجب إرسال العملية من النموذج.', 405);
    }
}
function cart_products(PDO $pdo): array {
    $cart = $_SESSION['cart'] ?? [];
    if (!is_array($cart)) $cart = [];
    foreach ($cart as $id => $qty) {
        if (!ctype_digit((string)$id) || (int)$id < 1 || !is_int($qty) || $qty < 1 || $qty > 99) unset($cart[$id]);
    }
    $_SESSION['cart'] = $cart;
    if (!$cart) return [];
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id IN (' . implode(',', array_fill(0, count($cart), '?')) . ')' . ($pdo->inTransaction() ? ' FOR UPDATE' : ''));
    $stmt->execute(array_keys($cart));
    $products = $stmt->fetchAll();
    $_SESSION['cart'] = array_intersect_key($cart, array_flip(array_column($products, 'id')));
    return $products;
}
function validate_product(PDO $pdo): void {
    foreach (['name' => 100, 'ram' => 50, 'storage' => 50, 'screen_size' => 50, 'battery' => 50, 'image_url' => 255, 'description' => 10000] as $key => $max) {
        $value = $_POST[$key] ?? '';
        if (!is_string($value) || mb_strlen($value) > $max || ($key === 'name' && trim($value) === '')) fail_request('تحقق من بيانات المنتج.');
    }
    foreach (['price', 'old_price'] as $key) {
        $value = $_POST[$key] ?? '';
        if ($key === 'old_price' && $value === '') continue;
        if (!is_string($value) || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $value) || (float)$value <= 0) fail_request('أدخل سعرًا موجبًا بخانتين عشريتين كحد أقصى.');
    }
    if (($_POST['old_price'] ?? '') !== '' && (float)$_POST['old_price'] <= (float)$_POST['price']) fail_request('السعر القديم يجب أن يكون أعلى من السعر الحالي.');
    $url = $_POST['image_url'] ?? '';
    $localImage = preg_match('~^images/products/[a-zA-Z0-9_-]+\.(?:png|jpe?g|webp)$~', $url) && is_file(__DIR__ . '/' . $url);
    $remoteImage = filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http','https'], true);
    if (!$localImage && !$remoteImage) fail_request('أدخل رابط صورة صالحًا أو مسار صورة من مكتبة المنتجات المحلية.');
    $brand = filter_var($_POST['brand_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $stmt = $pdo->prepare('SELECT id FROM brands WHERE id = ?');
    $stmt->execute([$brand ?: 0]);
    if (!$stmt->fetchColumn()) fail_request('الماركة غير موجودة.');
}
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) fail_request('انتهت صلاحية النموذج. حدّث الصفحة وحاول مجددًا.', 403);
}
// Buffer HTML so redirects remain valid, and give every POST form a CSRF token.
$csrfToken = $_SESSION['csrf_token'];
ob_start(static function (string $html) use ($csrfToken): string {
    $field = '<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">';
    return preg_replace_callback('/<form\b[^>]*\bmethod\s*=\s*["\x27]?post\b[^>]*>/i', static fn($match) => $match[0] . $field, $html);
});
