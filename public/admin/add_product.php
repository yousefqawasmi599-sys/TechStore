<?php
include 'header.php';

// كود حفظ المنتج عند الضغط على الزر
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_product($pdo);
    $name = $_POST['name'];
    $brand_id = $_POST['brand_id'];
    $screen_size = $_POST['screen_size'] ?? '';
    $battery = $_POST['battery'] ?? '';
    $price = $_POST['price'];
    $old_price = !empty($_POST['old_price']) ? $_POST['old_price'] : NULL;
    $image_url = $_POST['image_url'];
    $ram = $_POST['ram'] ?? '';
    $storage = $_POST['storage'] ?? '';
    $description = $_POST['description'] ?? '';

    // إدخال البيانات للقاعدة
    $sql = "INSERT INTO products (name, brand_id, price, old_price, image_url, ram, storage, description, screen_size, battery) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$name, $brand_id, $price, $old_price, $image_url, $ram, $storage, $description, $screen_size, $battery])) {
        header('Location: products.php?saved=1');
        exit;
    } else {
        $message = '<div class="alert error">حدث خطأ أثناء الإضافة</div>';
    }
}

// جلب قائمة الماركات (للقائمة المنسدلة)
$brands = $pdo->query("SELECT * FROM brands")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="form-container">
    <div class="form-header">
        <h2>إضافة هاتف جديد 📱</h2>
        <p>أدخل تفاصيل الجهاز بدقة ليظهر في المتجر</p>
    </div>

    <?php echo $message; ?>

    <form method="POST" class="modern-form">
        <div class="form-grid">
            
            <div class="form-section">
                <div class="input-group">
                    <label>اسم الهاتف</label>
                    <input type="text" name="name"  aria-label="الاسم" placeholder="مثال: iPhone 15 Pro Max" required>
                </div>

                <div class="row-2">
                    <div class="input-group">
                        <label>الماركة</label>
                        <select name="brand_id"  aria-label="الماركة" required>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo e($brand['id']); ?>"><?php echo e($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>السعر ($)</label>
                        <input type="number" step="0.01" name="price"  aria-label="السعر" placeholder="999.00" required>
                    </div>
                </div>

                <div class="row-2">
                    <div class="input-group">
                        <label>الرام (RAM)</label>
                        <input type="text" name="ram"  aria-label="الذاكرة" placeholder="8GB">
                    </div>
                    <div class="input-group">
                        <label>التخزين</label>
                        <input type="text" name="storage"  aria-label="التخزين" placeholder="256GB">
                    </div>
                </div>

                <div class="input-group"><label for="screen_size">الشاشة</label><input id="screen_size" name="screen_size" maxlength="50" placeholder="6.7 بوصة"></div><div class="input-group"><label for="battery">البطارية</label><input id="battery" name="battery" maxlength="50" placeholder="5000 mAh"></div><div class="input-group">
                    <label>سعر قديم (اختياري للخصم)</label>
                    <input type="number" step="0.01" name="old_price"  aria-label="السعر القديم" placeholder="1200.00">
                </div>
            </div>

            <div class="form-section">
                <div class="input-group">
                    <label>رابط الصورة (URL)</label>
                    <input type="url" name="image_url"  aria-label="رابط الصورة" id="imgInput" placeholder="https://example.com/image.jpg" required oninput="previewImage()">
                </div>

                <div class="image-preview-box">
                    <img loading="lazy" onerror="this.onerror=null;this.src='../image-fallback.svg';" id="preview" src="../image-fallback.svg" alt="معاينة">
                    <span>معاينة الصورة</span>
                </div>

                <div class="input-group">
                    <label>وصف المنتج</label>
                    <textarea name="description"  aria-label="الوصف" rows="4" placeholder="اكتب وصفاً مختصراً وجذاباً..."></textarea>
                </div>
            </div>

        </div>

        <button type="submit" class="btn-submit">حفظ المنتج <i class="fa-solid fa-cloud-arrow-up"></i></button>
    </form>
</div>

<script>
    function previewImage() {
        const input = document.getElementById('imgInput');
        const img = document.getElementById('preview');
        if(input.value) {
            img.src = input.value;
        }
    }
</script>

<style>
    /* --- تنسيق صفحة الإضافة --- */
    .form-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .form-header { text-align: center; margin-bottom: 30px; }
    .form-header h2 { color: var(--secondary-color); margin-bottom: 10px; }
    .form-header p { color: #94a3b8; font-size: 14px; }

    .modern-form { display: flex; flex-direction: column; gap: 20px; }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    /* الحقول */
    .input-group { margin-bottom: 20px; }
    .input-group label { display: block; margin-bottom: 8px; color: #64748b; font-weight: 500; font-size: 13px; }
    
    .input-group input, .input-group select, .input-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #f1f5f9;
        border-radius: 10px;
        font-family: inherit;
        transition: 0.3s;
        background: #f8fafc;
    }

    .input-group input:focus, .input-group select:focus, .input-group textarea:focus {
        border-color: var(--primary-color);
        background: white;
        outline: none;
    }

    .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    /* معاينة الصورة */
    .image-preview-box {
        width: 100%;
        height: 200px;
        border: 2px dashed #cbd5e1;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        overflow: hidden;
        background: #f8fafc;
    }
    
    .image-preview-box img { max-height: 80%; object-fit: contain; margin-bottom: 10px; }
    .image-preview-box span { font-size: 12px; color: #94a3b8; }

    /* زر الحفظ */
    .btn-submit {
        background: var(--primary-color);
        color: white;
        padding: 15px;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
        margin-top: 10px;
    }
    .btn-submit:hover { background: #1d4ed8; transform: translateY(-2px); }

    /* رسائل التنبيه */
    .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: bold; }
    .alert.success { background: #dcfce7; color: #16a34a; }
    .alert.error { background: #fee2e2; color: #ef4444; }

    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; gap: 20px; }
        .form-container { padding: 20px; }
    }
</style>
</div></body></html>
