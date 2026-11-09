<?php
include 'header.php';

// جلب المنتجات مع اسم الماركة
$sql = "SELECT products.*, brands.name as brand_name 
        FROM products 
        JOIN brands ON products.brand_id = brands.id 
        ORDER BY products.id DESC";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <div>
        <h2>إدارة المنتجات 📱</h2>
        <p>تحكم بجميع الهواتف المعروضة في المتجر</p>
    </div>
    <a href="add_product.php" class="btn-main"><i class="fa-solid fa-plus"></i> إضافة منتج</a>
</div>

<?php if (isset($_GET['saved'])): ?><div class="alert success" role="status">تم حفظ المنتج بنجاح.</div><?php endif; ?><div class="table-section">
    <table class="modern-table">
        <thead>
            <tr>
                <th>صورة</th>
                <th>تفاصيل المنتج</th>
                <th>الماركة</th>
                <th>السعر</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td>
                    <div class="img-wrapper">
                        <img loading="lazy" onerror="this.onerror=null;this.src='../image-fallback.svg';" src="<?php echo e(product_image($product['image_url'])); ?>" alt="img">
                    </div>
                </td>
                
                <td>
                    <div class="product-info">
                        <strong><?php echo e($product['name']); ?></strong>
                        <span class="specs-badge">
                            <i class="fa-solid fa-microchip"></i> <?php echo e($product['ram']); ?> | <?php echo e($product['storage']); ?>
                        </span>
                    </div>
                </td>

                <td>
                    <span class="brand-badge"><?php echo e($product['brand_name']); ?></span>
                </td>

                <td>
                    <div class="price-tag">
                        $<?php echo e($product['price']); ?>
                        <?php if($product['old_price']): ?>
                            <small>$<?php echo e($product['old_price']); ?></small>
                        <?php endif; ?>
                    </div>
                </td>

                <td>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="edit_product.php?id=<?php echo e($product['id']); ?>" class="action-btn edit" title="تعديل">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>

                        <form action="delete_product.php" method="POST" onsubmit="return confirm('تأكيد الحذف؟');"><input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>"><button type="submit" class="action-btn delete" title="حذف"><i class="fa-solid fa-trash"></i></button></form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    /* --- تنسيقات صفحة المنتجات --- */
    
    /* الهيدر */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .dashboard-header h2 { color: var(--secondary-color); margin-bottom: 5px; }
    .dashboard-header p { color: #94a3b8; font-size: 14px; }

    /* الزر الرئيسي */
    .btn-main {
        background: var(--primary-color);
        color: white;
        padding: 12px 25px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-main:hover { transform: translateY(-2px); background: #1d4ed8; }

    /* الجدول الحديث */
    .table-section {
        background: white;
        padding: 10px;
        border-radius: 20px;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 15px;
    }

    .modern-table th {
        text-align: right;
        color: #94a3b8;
        font-weight: 500;
        padding: 0 20px;
        font-size: 13px;
    }

    .modern-table tbody tr {
        background: white;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        transition: 0.3s;
    }
    .modern-table tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .modern-table td {
        padding: 15px 20px;
        vertical-align: middle;
        border-top: 1px solid #f8fafc;
        border-bottom: 1px solid #f8fafc;
    }
    .modern-table td:first-child { border-top-right-radius: 15px; border-bottom-right-radius: 15px; border-right: 1px solid #f8fafc; }
    .modern-table td:last-child { border-top-left-radius: 15px; border-bottom-left-radius: 15px; border-left: 1px solid #f8fafc; }

    /* العناصر الداخلية */
    .img-wrapper {
        width: 60px; height: 60px;
        background: #f8fafc;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        padding: 5px;
    }
    .img-wrapper img { max-width: 100%; max-height: 100%; object-fit: contain; }

    .product-info { display: flex; flex-direction: column; gap: 5px; }
    .product-info strong { color: var(--secondary-color); font-size: 15px; }
    .specs-badge { font-size: 11px; color: #94a3b8; background: #f1f5f9; padding: 3px 8px; border-radius: 6px; width: fit-content; }

    .brand-badge {
        background: rgba(59, 130, 246, 0.1);
        color: var(--primary-color);
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12px;
    }

    .price-tag { font-weight: 800; color: var(--secondary-color); font-size: 16px; }
    .price-tag small { text-decoration: line-through; color: #cbd5e1; font-size: 12px; margin-right: 5px; font-weight: normal; }

    /* أزرار الإجراءات */
    .action-btn {
        width: 35px; height: 35px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px;
        transition: 0.2s;
        text-decoration: none;
    }

    /* زر الحذف */
    .action-btn.delete {
        background: #fee2e2;
        color: #ef4444;
    }
    .action-btn.delete:hover { background: #ef4444; color: white; }

    /* زر التعديل (جديد) */
    .action-btn.edit {
        background: #dbeafe;
        color: #2563eb;
    }
    .action-btn.edit:hover { background: #2563eb; color: white; }

</style>
</div></body></html>
