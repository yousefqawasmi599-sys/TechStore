<?php
include 'config.php';
include 'header.php';
?>

<section class="hero">
    <div class="container hero-content">
        
        <div class="hero-text">
            <span class="badge-new">مختارات TechStore ✨</span>
            <h1>اكتشف التكنولوجيا <br> <span class="text-gradient">بين يديك</span></h1>
            <p>اكتشف هاتفك القادم من مجموعة مختارة من الهواتف الذكية. قارن المواصفات، تصفح العروض، وتسوق بسهولة.</p>
            
            <div class="hero-buttons">
                <a href="shop.php" class="btn-primary">تصفح المنتجات <i class="fa-solid fa-arrow-left"></i></a>
                <a href="#brands" class="btn-outline">الماركات <i class="fa-solid fa-layer-group"></i></a>
            </div>
        </div>

        <div class="hero-image">
            <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://assets.swappie.com/cdn-cgi/image/width=600,height=600,fit=contain,format=auto/swappie-iphone-15-pro-max-natural-titanium-back.png" alt="iPhone 15 Pro">
            
            <div class="floating-card">
                <i class="fa-solid fa-microchip"></i>
                <div>
                    <span>أداء احترافي</span>
                    <strong>A17 Pro</strong>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="brands-section" id="brands">
    <div class="container">
        <h3 style="text-align: center; margin-bottom: 40px; color: #cbd5e1;">تسوق حسب الماركة</h3>
        
        <div class="brands-grid" style="display: flex; justify-content: center; gap: 50px; flex-wrap: wrap;">
            <a href="shop.php?brand_id=1" class="brand-item">
                <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://cdn.simpleicons.org/apple/000000" alt="Apple" style="height: 50px;">
            </a>
            
            <a href="shop.php?brand_id=2" class="brand-item">
                <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://cdn.simpleicons.org/samsung/1428A0" alt="Samsung" style="height: 50px;">
            </a>
            
            <a href="shop.php?brand_id=3" class="brand-item">
                <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://cdn.simpleicons.org/xiaomi/FF6900" alt="Xiaomi" style="height: 50px;">
            </a>
            
            <a href="shop.php?brand_id=4" class="brand-item">
                <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://cdn.simpleicons.org/huawei/C22E2D" alt="Huawei" style="height: 50px;">
            </a>
            
            <a href="shop.php?brand_id=5" class="brand-item">
                <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="https://cdn.simpleicons.org/google/4285F4" alt="Google" style="height: 50px;">
            </a>
        </div>
    </div>
</section>

<section class="products-section container">
    <div class="section-header">
        <h2>مختارات <span class="highlight">المتجر</span></h2>
        <p>هواتف مميزة تجمع التصميم والأداء، في مكان واحد</p>
    </div>

    <div class="products-grid">
        <?php
        // جلب أحدث 4 منتجات
        $stmt = $pdo->query("SELECT products.*, brands.name as brand_name FROM products JOIN brands ON products.brand_id = brands.id WHERE products.id IN (SELECT MIN(id) FROM products GROUP BY name) ORDER BY products.price DESC, products.id DESC LIMIT 4");
        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
            <div class="product-card">
                <?php if((float)$product['old_price'] > (float)$product['price']): ?>
                    <span class="discount-badge">توفير</span>
                <?php endif; ?>

                <div class="card-image">
                    <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="<?php echo e(product_image($product['image_url'])); ?>" alt="<?php echo e($product['name']); ?>">
                </div>

                <div class="card-details">
                    <span class="brand-name"><?php echo e($product['brand_name']); ?></span>
                    <h3><a href="product.php?id=<?php echo (int)$product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
                    
                    <div class="specs-row">
                        <?php if (!empty($product['ram'])): ?><span><i class="fa-solid fa-memory"></i> <?php echo e($product['ram']); ?></span><?php endif; ?>
                        <?php if (!empty($product['storage'])): ?><span><i class="fa-solid fa-hard-drive"></i> <?php echo e($product['storage']); ?></span><?php endif; ?>
                    </div>

                    <div class="price-row">
                        <div class="price">
                            <span class="current-price">$<?php echo e($product['price']); ?></span>
                            <?php if((float)$product['old_price'] > (float)$product['price']): ?>
                                <span class="old-price">$<?php echo e($product['old_price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <form action="add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>"><input type="hidden" name="redirect" value="1"><button type="submit" class="add-cart-btn" aria-label="إضافة للسلة">
                            <i class="fa-solid fa-plus"></i>
                        </button></form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>



<?php include 'footer.php'; ?>
