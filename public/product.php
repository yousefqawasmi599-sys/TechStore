<?php
require __DIR__ . '/config.php';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$id) fail_request('معرّف المنتج غير صالح.');
$stmt=$pdo->prepare('SELECT products.*,brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_id=brands.id WHERE products.id=?');
$stmt->execute([$id]);
$product=$stmt->fetch();
if (!$product) fail_request('المنتج غير موجود.',404);
include 'header.php';
?>
<main class="container product-detail">
    <a href="shop.php">العودة إلى المنتجات</a>
    <div class="product-detail-grid">
        <div class="product-detail-image"><img src="<?php echo e(product_image($product['image_url'])); ?>" alt="<?php echo e($product['name']); ?>" onerror="this.onerror=null;this.src='image-fallback.svg';"></div>
        <section>
            <p class="brand-name"><?php echo e($product['brand_name'] ?? ''); ?></p>
            <h1><?php echo e($product['name']); ?></h1>
            <p class="detail-price">$<?php echo number_format((float)$product['price'],2); ?> <?php if ((float)$product['old_price'] > (float)$product['price']): ?><del>$<?php echo number_format((float)$product['old_price'],2); ?></del><?php endif; ?></p>
            <p><?php echo nl2br(e($product['description'] ?? '')); ?></p>
            <dl class="product-specs">
                <?php foreach (['ram'=>'الذاكرة','storage'=>'التخزين','screen_size'=>'الشاشة','battery'=>'البطارية'] as $key=>$label): if (!empty($product[$key])): ?>
                <div><dt><?php echo $label; ?></dt><dd><?php echo e($product[$key]); ?></dd></div>
                <?php endif; endforeach; ?>
            </dl>
            <form action="add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="<?php echo $id; ?>"><input type="hidden" name="redirect" value="1"><button class="btn-primary" type="submit">إضافة إلى السلة <i class="fa-solid fa-cart-plus"></i></button></form>
        </section>
    </div>
</main>
<?php include 'footer.php'; ?>
