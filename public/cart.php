<?php
require_once __DIR__ . '/bootstrap.php';
include 'config.php';
include 'header.php';

// التحقق من وجود سلة
$cartItems = [];
$totalPrice = 0;

$cartItems = cart_products($pdo);
?>

<div class="container cart-page">
    <h2>سلة <span class="highlight">مشترياتك</span></h2>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="fa-solid fa-cart-arrow-down"></i>
            <p>سلتك فارغة حالياً!</p>
            <a href="index.php" class="btn-primary">تصفح المنتجات</a>
        </div>
    <?php else: ?>

        <div class="cart-content">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): 
                    $qty = $_SESSION['cart'][$item['id']];
                    $lineTotal = $item['price'] * $qty;
                    $totalPrice += $lineTotal;
                ?>
                <div class="cart-item">
                    <div class="item-img">
                        <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="<?php echo e(product_image($item['image_url'])); ?>" alt="صورة">
                    </div>
                    
                    <div class="item-info">
                        <h3><?php echo e($item['name']); ?></h3>
                        <span class="price">$<?php echo e($item['price']); ?></span>
                    </div>

                    <div class="item-controls">
                        <div class="qty-box">
                            <form action="update_cart.php" method="POST"><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><input type="hidden" name="action" value="decrease"><button class="qty-btn" type="submit">-</button></form>
                            
                            <span><?php echo $qty; ?></span>
                            
                            <form action="update_cart.php" method="POST"><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><input type="hidden" name="action" value="increase"><button class="qty-btn" type="submit">+</button></form>
                        </div>
                        
                        <form action="remove_cart.php" method="POST"><input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>"><button class="remove-btn" type="submit"><i class="fa-solid fa-trash"></i> حذف</button></form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h3>ملخص الطلب</h3>
                <div class="summary-row">
                    <span>المجموع الفرعي</span>
                    <span>$<?php echo $totalPrice; ?></span>
                </div>
                <div class="summary-row">
                    <span>الشحن</span>
                    <span class="free">مجاني</span>
                </div>
                <hr>
                <div class="summary-row total">
                    <span>الإجمالي</span>
                    <span>$<?php echo $totalPrice; ?></span>
                </div>
                
                <a href="checkout.php" class="btn-checkout">إتمام الشراء <i class="fa-solid fa-check"></i></a>
            </div>
        </div>

    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>