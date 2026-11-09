<?php
require __DIR__ . '/config.php';
$order_id = (int)($_GET['order_id'] ?? 0);
if (!$order_id || $order_id !== (int)($_SESSION['last_order_id'] ?? 0)) fail_request('الطلب غير متاح.', 404);
$stmt=$pdo->prepare('SELECT * FROM orders WHERE id=?'); $stmt->execute([$order_id]); $order=$stmt->fetch();
if (!$order) fail_request('الطلب غير موجود.',404);
$stmt=$pdo->prepare('SELECT order_items.*,products.name FROM order_items LEFT JOIN products ON products.id=order_items.product_id WHERE order_id=?'); $stmt->execute([$order_id]); $items=$stmt->fetchAll();
include 'header.php';
?>

<div class="thank-you-wrapper">
    
    <div class="success-header">
        <div class="container">
            <h1>تم استلام طلبك بنجاح! 🎉</h1>
            <p>شكراً لثقتك بنا، فريقنا سيبدأ بتجهيز طلبك فوراً.</p>
        </div>
    </div>

    <div class="container">
        <div class="order-status-card">
            
            <div class="steps-container">
                <div class="step active">
                    <div class="step-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                    <span>تم الطلب</span>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fa-solid fa-box-open"></i></div>
                    <span>التجهيز</span>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <span>التوصيل</span>
                </div>
                <div class="step">
                    <div class="step-icon"><i class="fa-solid fa-house-chimney"></i></div>
                    <span>الاستلام</span>
                </div>
            </div>

            <hr class="divider">

            <div class="order-info-box">
                <div class="info-item">
                    <span>رقم الطلب</span>
                    <strong>#<?php echo $order_id; ?></strong>
                </div>
                <div class="info-item">
                    <span>تاريخ الطلب</span>
                    <strong><?php echo e(date('Y-m-d',strtotime($order['created_at']))); ?></strong>
                </div>
                <div class="info-item">
                    <span>حالة الدفع</span>
                    <strong class="status-badge">الدفع عند الاستلام</strong>
                </div>
            </div>

            <section class="receipt-summary"><h2>تفاصيل طلبك</h2><p><?php echo e($order['name']); ?></p><table><thead><tr><th>المنتج</th><th>الكمية</th><th>الإجمالي</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?php echo e($item['name'] ?? 'منتج'); ?></td><td><?php echo (int)$item['quantity']; ?></td><td>$<?php echo number_format((float)$item['price']*$item['quantity'],2); ?></td></tr><?php endforeach; ?></tbody></table><p><strong>إجمالي الطلب: $<?php echo number_format((float)$order['total_amount'],2); ?></strong></p></section><div class="action-buttons">
                <a href="index.php" class="btn-primary">متابعة التسوق <i class="fa-solid fa-bag-shopping"></i></a>
                <button class="btn-outline" type="button" onclick="window.print()">طباعة الفاتورة <i class="fa-solid fa-print"></i></button>
            </div>
        </div>
    </div>
</div>

</body>
</html>