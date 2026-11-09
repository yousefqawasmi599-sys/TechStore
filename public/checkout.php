<?php
require_once __DIR__ . '/bootstrap.php';
include 'config.php';


// إذا كانت السلة فارغة، نرجع المستخدم للرئيسية
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// حساب المجموع الكلي للعرض فقط
$total = 0;
$products = cart_products($pdo);
if (!$products) { header('Location: cart.php'); exit; }

foreach($products as $p) {
    $total += $p['price'] * $_SESSION['cart'][$p['id']];
}
include 'header.php';
?>

<div class="container checkout-page">
    <h2>إتمام <span class="highlight">الطلب</span></h2>
    
    <div class="checkout-container">
        <form action="place_order.php" method="POST" class="checkout-form">
            <h3>بيانات التوصيل</h3>
            
            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="name" maxlength="100" autocomplete="name"  aria-label="الاسم" placeholder="مثال: محمد أحمد" required>
            </div>

            <div class="form-group">
                <label>رقم الهاتف</label>
                <input type="tel" name="phone" maxlength="20" autocomplete="tel"  aria-label="رقم الهاتف" placeholder="079xxxxxxx" required>
            </div>

            <div class="form-group">
                <label>العنوان بالتفصيل</label>
                <textarea name="address" maxlength="2000" autocomplete="street-address"  aria-label="العنوان" rows="3" placeholder="المدينة، الشارع، رقم البناية..." required></textarea>
            </div>

            <p class="payment-note">طريقة الدفع: نقدًا عند الاستلام. الشحن مجاني.</p>">
            
            <button type="submit" name="place_order" class="btn-confirm">
                تأكيد الطلب ($<?php echo $total; ?>) <i class="fa-solid fa-arrow-left"></i>
            </button>
        </form>

        <div class="order-summary">
            <h3>ملخص الطلبية</h3>
            <div class="summary-items">
                <?php foreach($products as $item): ?>
                    <div class="summary-item">
                        <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="<?php echo e(product_image($item['image_url'])); ?>" width="50">
                        <div>
                            <h4><?php echo e($item['name']); ?></h4>
                            <small>الكمية: <?php echo $_SESSION['cart'][$item['id']]; ?></small>
                        </div>
                        <span>$<?php echo $item['price'] * $_SESSION['cart'][$item['id']]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
            <div class="total-row">
                <span>الإجمالي النهائي</span>
                <span>$<?php echo $total; ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>