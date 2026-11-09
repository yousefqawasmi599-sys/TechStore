<?php
require __DIR__ . '/config.php';
$search = trim($_GET['search'] ?? '');
if (mb_strlen($search) > 100) fail_request('عبارة البحث طويلة جدًا.');
$brandId = null;
if (isset($_GET['brand_id']) && $_GET['brand_id'] !== '') {
    $brandId = filter_var($_GET['brand_id'], FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if (!$brandId) fail_request('الماركة غير صالحة.');
}
$offers = ($_GET['show'] ?? '') === 'offers';
$sql = 'SELECT products.*, brands.name AS brand_name FROM products LEFT JOIN brands ON products.brand_id = brands.id WHERE 1=1';
$params = [];
$page_title = $offers ? 'عروضنا المميزة 🔥' : 'كل المنتجات';
if ($search !== '') {
    $sql .= ' AND products.name LIKE ?';
    $params[] = '%' . $search . '%';
    $page_title = 'نتائج البحث عن: "' . $search . '"';
}
if ($brandId) { $sql .= ' AND products.brand_id = ?'; $params[] = $brandId; }
if ($offers) $sql .= ' AND products.old_price > products.price';
$sort_option = $_GET['sort'] ?? 'new';
$sorts = ['new'=>'products.id DESC','price_asc'=>'products.price ASC, products.id DESC','price_desc'=>'products.price DESC, products.id DESC'];
if (!isset($sorts[$sort_option])) $sort_option='new';
$sql .= ' ORDER BY ' . $sorts[$sort_option];
$stmt=$pdo->prepare($sql); $stmt->execute($params); $products=$stmt->fetchAll();
$brands=$pdo->query('SELECT id,name FROM brands ORDER BY name')->fetchAll();
include 'header.php';
?>


<div class="container" style="margin-top: 50px; min-height: 60vh;">
    
    <div class="shop-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;">
        <h2 class="section-title" style="margin-bottom: 0;"><?php echo e($page_title); ?> 📱</h2>
        
        <div class="sort-box">
            <label style="font-weight: bold; margin-left: 10px;">رتب حسب:</label>
            <select onchange="updateSort(this.value)" style="padding: 8px 15px; border-radius: 8px; border: 1px solid #ddd; outline: none; cursor: pointer;">
                <option value="new" <?php if($sort_option == 'new') echo 'selected'; ?>>الأحدث مضافاً</option>
                <option value="price_asc" <?php if($sort_option == 'price_asc') echo 'selected'; ?>>السعر: من الأقل للأكثر</option>
                <option value="price_desc" <?php if($sort_option == 'price_desc') echo 'selected'; ?>>السعر: من الأكثر للأقل</option>
            </select>
        </div>
    </div>

<form action="shop.php" method="GET" class="catalog-filters">
    <label>ابحث عن هاتف<input type="search" name="search" maxlength="100" value="<?php echo e($search); ?>" placeholder="اسم المنتج"></label>
    <label>الماركة<select name="brand_id" aria-label="الماركة" ><option value="">جميع الماركات</option><?php foreach($brands as $brand): ?><option value="<?php echo (int)$brand['id']; ?>" <?php echo $brandId === (int)$brand['id'] ? 'selected' : ''; ?>><?php echo e($brand['name']); ?></option><?php endforeach; ?></select></label>
    <label class="offers-filter"><input type="checkbox" name="show" value="offers" <?php echo $offers ? 'checked' : ''; ?>> العروض فقط</label>
    <input type="hidden" name="sort" value="<?php echo e($sort_option); ?>">
    <button class="btn-primary" type="submit">تطبيق</button><a href="shop.php">مسح الفلاتر</a>
</form>
<p class="result-count" role="status"><?php echo count($products); ?> منتج</p>
    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
            <div class="product-card">
                <div class="card-image">
                    <img loading="lazy" onerror="this.onerror=null;this.src='image-fallback.svg';" src="<?php echo e(product_image($product['image_url'])); ?>" alt="<?php echo e($product['name']); ?>">
                </div>
                
                <div class="card-details">
                    <span class="brand-name"><?php echo e($product['brand_name']); ?></span>
                    <h3><a href="product.php?id=<?php echo (int)$product['id']; ?>"><?php echo e($product['name']); ?></a></h3>
                    
                    <div class="price-row">
                        <span class="current-price">$<?php echo e($product['price']); ?></span>
                        <?php if((float)$product['old_price'] > (float)$product['price']): ?>
                            <span class="old-price">$<?php echo e($product['old_price']); ?></span>
                        <?php endif; ?>
                        
                        <form action="add_to_cart.php" method="POST"><input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>"><input type="hidden" name="redirect" value="1"><button type="submit" class="add-cart-btn" aria-label="إضافة للسلة">
                            <i class="fa-solid fa-cart-plus"></i>
                        </button></form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: #64748b;">
            <i class="fa-regular fa-face-frown" style="font-size: 50px; margin-bottom: 20px;"></i>
            <h3>لا توجد منتجات حالياً.</h3>
            <a href="shop.php" class="btn-primary" style="margin-top: 20px; display: inline-block;">عرض الكل</a>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateSort(sortValue) {
        // نأخذ رابط الصفحة الحالي مع كل الباراميترات (بحث، ماركة، إلخ)
        const urlParams = new URLSearchParams(window.location.search);
        
        // نحدث قيمة الـ sort أو نضيفها إذا لم تكن موجودة
        urlParams.set('sort', sortValue);
        
        // نعيد تحميل الصفحة بالرابط الجديد
        window.location.search = urlParams.toString();
    }
</script>

<?php include 'footer.php'; ?>