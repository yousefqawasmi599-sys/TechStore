<?php
include '../config.php';
include 'header.php'; // تأكد أن لديك هيدر للأدمن

// التحقق من وجود ID المنتج
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); if (!$id) fail_request('معرّف غير صالح.');

// جلب بيانات المنتج
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    fail_request('المنتج غير موجود.', 404);
    exit;
}

// معالجة التحديث عند الضغط على زر الحفظ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_product($pdo);
    $name = $_POST['name'];
    $price = $_POST['price'];
    $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : NULL;
    $brand_id = $_POST['brand_id'];
    $screen_size = $_POST['screen_size'] ?? '';
    $battery = $_POST['battery'] ?? '';
    $ram = $_POST['ram'] ?? '';
    $storage = $_POST['storage'] ?? '';
    $description = $_POST['description'] ?? ($product['description'] ?? '');
    
    // الاحتفاظ بالصورة القديمة افتراضياً
    $image_url = $product['image_url'];

    // إذا تم رفع صورة جديدة
    if (!empty($_POST['image_url'])) {
        $image_url = $_POST['image_url'];
    }

    // تحديث البيانات
    $sql = "UPDATE products SET name=?, price=?, old_price=?, brand_id=?, image_url=?, ram=?, storage=?, description=?, screen_size=?, battery=? WHERE id=?";
    $stmt_update = $pdo->prepare($sql);
    $stmt_update->execute([$name, $price, $old_price, $brand_id, $image_url, $ram, $storage, $description, $screen_size, $battery, $id]);

    header('Location: products.php?saved=1');
    exit;
}
?>

<div class="container" style="margin-top: 50px;">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--secondary-color);">تعديل المنتج ✏️</h2>
    
    <form method="POST" class="checkout-form" style="max-width: 600px; margin: 0 auto;">
        
        <div class="form-group">
            <label>اسم المنتج</label>
            <input type="text" name="name"  aria-label="الاسم" value="<?php echo e($product['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>الماركة</label>
            <select name="brand_id"  aria-label="الماركة" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                <?php
                $brands = $pdo->query("SELECT * FROM brands");
                while ($brand = $brands->fetch()) {
                    $selected = ($brand['id'] == $product['brand_id']) ? 'selected' : '';
                    echo "<option value='" . (int)$brand['id'] . "' $selected>" . e($brand['name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group" style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <label>السعر الحالي ($)</label>
                <input type="number" step="0.01" name="price"  aria-label="السعر" value="<?php echo e($product['price']); ?>" required>
            </div>
            <div style="flex: 1;">
                <label>السعر القديم (اختياري)</label>
                <input type="number" step="0.01" name="old_price"  aria-label="السعر القديم" value="<?php echo e($product['old_price']); ?>">
            </div>
        </div>

        <div class="form-group" style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <label>الرام (RAM)</label>
                <input type="text" name="ram"  aria-label="الذاكرة" value="<?php echo e($product['ram']); ?>" required>
            </div>
            <div style="flex: 1;">
                <label>التخزين (Storage)</label>
                <input type="text" name="storage"  aria-label="التخزين" value="<?php echo e($product['storage']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>رابط الصورة</label>
            <input type="text" name="image_url"  aria-label="رابط الصورة" value="<?php echo e($product['image_url']); ?>" required>
            <div style="margin-top: 10px; text-align: center;">
                <img loading="lazy" onerror="this.onerror=null;this.src='../image-fallback.svg';" src="<?php echo e(product_image($product['image_url'])); ?>" width="100" style="border-radius: 10px; border: 1px solid #ddd;">
                <p style="font-size: 12px; color: #888;">الصورة الحالية</p>
            </div>
        </div>

        <div class="form-group"><label for="screen_size">الشاشة</label><input id="screen_size" name="screen_size" maxlength="50" value="<?php echo e($product['screen_size'] ?? ''); ?>"></div><div class="form-group"><label for="battery">البطارية</label><input id="battery" name="battery" maxlength="50" value="<?php echo e($product['battery'] ?? ''); ?>"></div><div class="form-group"><label for="description">وصف المنتج</label><textarea id="description" name="description"  aria-label="الوصف" rows="4" maxlength="10000"><?php echo e($product['description'] ?? ''); ?></textarea></div><button type="submit" class="btn-confirm" style="background: #f59e0b;">حفظ التعديلات</button>
        <a href="products.php" style="display: block; text-align: center; margin-top: 15px; color: #666;">إلغاء</a>
    </form>
</div>
</div></body></html>
