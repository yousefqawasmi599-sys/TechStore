<?php
include 'header.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); if (!$order_id) fail_request('معرّف غير صالح.');

// 1. تحديث حالة الطلب (إذا تم الضغط على زر التحديث)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    if (!in_array($new_status, ['pending','processing','shipped','completed','cancelled'], true)) fail_request('حالة الطلب غير صالحة.');
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    header('Location: order_details.php?id=' . $order_id);
    exit;
}

// 2. جلب معلومات الطلب الأساسية (الزبون)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    fail_request('الطلب غير موجود.', 404);
    exit();
}

// 3. جلب المنتجات داخل هذا الطلب
$stmt = $pdo->prepare("
    SELECT order_items.*, products.name, products.image_url 
    FROM order_items 
    JOIN products ON order_items.product_id = products.id 
    WHERE order_items.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="details-container">
    
    <div class="details-header">
        <div>
            <h2>تفاصيل الطلب #<?php echo e($order['id']); ?></h2>
            <span class="date-badge"><i class="fa-regular fa-clock"></i> <?php echo e($order['created_at']); ?></span>
        </div>
        <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-right"></i> عودة</a>
    </div>

    <div class="details-grid">
        
        <div class="info-card">
            <h3><i class="fa-solid fa-user-tag"></i> معلومات العميل</h3>
            <div class="customer-info">
                <p><strong>الاسم:</strong> <?php echo e($order['name']); ?></p>
                <p><strong>الهاتف:</strong> <?php echo e($order['phone']); ?></p>
                <p><strong>العنوان:</strong> <br> <?php echo nl2br(e($order['address'])); ?></p>
            </div>

            <hr class="divider">

            <h3><i class="fa-solid fa-list-check"></i> حالة الطلب</h3>
            <form method="POST" class="status-form">
                <select name="status"  aria-label="حالة الطلب" class="status-select">
                    <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>🟡 قيد الانتظار (Pending)</option>
                    <option value="processing" <?php if($order['status']=='processing') echo 'selected'; ?>>🔵 جاري التحضير (Processing)</option>
                    <option value="shipped" <?php if($order['status']=='shipped') echo 'selected'; ?>>🚚 في الطريق (Shipped)</option>
                    <option value="completed" <?php if($order['status']=='completed') echo 'selected'; ?>>✅ تم التسليم (Completed)</option>
                    <option value="cancelled" <?php if($order['status']=='cancelled') echo 'selected'; ?>>❌ ملغي (Cancelled)</option>
                </select>
                <button type="submit" name="update_status" class="btn-update">تحديث الحالة</button>
            </form>
        </div>

        <div class="items-card">
            <h3><i class="fa-solid fa-basket-shopping"></i> المنتجات المطلوبة</h3>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="product-col">
                            <img loading="lazy" onerror="this.onerror=null;this.src='../image-fallback.svg';" src="<?php echo e(product_image($item['image_url'])); ?>" alt="img">
                            <span><?php echo e($item['name']); ?></span>
                        </td>
                        <td>$<?php echo e($item['price']); ?></td>
                        <td>x<?php echo e($item['quantity']); ?></td>
                        <td><strong>$<?php echo $item['price'] * $item['quantity']; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: left;">الإجمالي النهائي:</td>
                        <td class="total-price">$<?php echo e($order['total_amount']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

<style>
    /* --- ستايل صفحة التفاصيل --- */
    .details-container { max-width: 1000px; margin: 0 auto; }
    
    .details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .details-header h2 { color: var(--secondary-color); margin-bottom: 5px; }
    .date-badge { background: #e2e8f0; color: #64748b; padding: 5px 12px; border-radius: 20px; font-size: 12px; }
    
    .btn-back {
        background: white; color: var(--secondary-color);
        padding: 10px 20px; border-radius: 10px;
        text-decoration: none; font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .details-grid {
        display: grid;
        grid-template-columns: 1fr 2fr; /* العمود الأيسر أعرض */
        gap: 30px;
    }

    /* البطاقات */
    .info-card, .items-card {
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
    }

    h3 { font-size: 16px; color: var(--primary-color); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    
    /* معلومات العميل */
    .customer-info p { margin-bottom: 12px; color: var(--secondary-color); font-size: 14px; }
    .customer-info strong { color: #64748b; display: inline-block; width: 60px; }
    
    .divider { border: 0; border-top: 1px dashed #e2e8f0; margin: 20px 0; }

    /* فورم الحالة */
    .status-form { display: flex; flex-direction: column; gap: 10px; }
    .status-select {
        padding: 10px; border-radius: 8px; border: 2px solid #e2e8f0;
        font-weight: bold; color: var(--secondary-color);
    }
    .btn-update {
        background: var(--secondary-color); color: white;
        padding: 10px; border: none; border-radius: 8px;
        cursor: pointer; font-weight: bold; transition: 0.3s;
    }
    .btn-update:hover { background: var(--primary-color); }

    /* جدول المنتجات */
    .items-table { width: 100%; border-collapse: collapse; }
    .items-table th { text-align: right; color: #94a3b8; font-size: 12px; padding-bottom: 15px; border-bottom: 1px solid #f1f5f9; }
    .items-table td { padding: 15px 0; border-bottom: 1px solid #f1f5f9; color: var(--secondary-color); font-size: 14px; }
    
    .product-col { display: flex; align-items: center; gap: 10px; }
    .product-col img { width: 40px; height: 40px; object-fit: contain; border: 1px solid #eee; border-radius: 8px; }
    
    .total-price { font-size: 18px; font-weight: 800; color: var(--primary-color); }

    @media (max-width: 768px) {
        .details-grid { grid-template-columns: 1fr; }
    }
</style>
</div></body></html>
