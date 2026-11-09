<?php
include 'header.php';

// جلب جميع الطلبات مرتبة من الأحدث للأقدم
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <div>
        <h2>سجل الطلبات 📦</h2>
        <p>قائمة بجميع الطلبات الواردة وحالاتها</p>
    </div>
</div>

<div class="table-section">
    <table class="modern-table">
        <thead>
            <tr>
                <th>رقم الطلب</th>
                <th>العميل</th>
                <th>معلومات الاتصال</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
                <th>التاريخ</th>
                <th>إجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><span class="id-badge">#<?php echo e($order['id']); ?></span></td>
                
                <td>
                    <div class="user-cell">
                        <div class="avatar-circle"><?php echo e(mb_substr($order['name'], 0, 1, 'UTF-8')); ?></div>
                        <strong><?php echo e($order['name']); ?></strong>
                    </div>
                </td>

                <td style="font-size: 13px; color: #64748b;">
                    <?php echo e($order['phone']); ?>
                </td>

                <td>
                    <strong style="color: var(--secondary-color);">$<?php echo e($order['total_amount']); ?></strong>
                </td>

                <td>
                    <?php 
                        $statusClass = '';
                        $statusText = '';
                        
                        switch($order['status']) {
                            case 'pending': $statusClass = 'pending'; $statusText = 'قيد الانتظار'; break;
                            case 'processing': $statusClass = 'processing'; $statusText = 'جاري التجهيز'; break;
                            case 'shipped': $statusClass = 'shipped'; $statusText = 'في الطريق'; break;
                            case 'completed': $statusClass = 'completed'; $statusText = 'تم التسليم'; break;
                            case 'cancelled': $statusClass = 'cancelled'; $statusText = 'ملغي'; break;
                            default: $statusClass = 'default'; $statusText = $order['status'];
                        }
                    ?>
                    <span class="status-badge <?php echo $statusClass; ?>">
                        <?php echo $statusText; ?>
                    </span>
                </td>

                <td style="color: #94a3b8; font-size: 13px;">
                    <?php echo date('Y/m/d', strtotime($order['created_at'])); ?>
                </td>

                <td>
                    <a href="order_details.php?id=<?php echo e($order['id']); ?>" class="action-btn view" title="عرض التفاصيل">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
    /* --- تنسيقات صفحة الطلبات --- */
    
    /* الهيدر */
    .dashboard-header { margin-bottom: 30px; }
    .dashboard-header h2 { color: var(--secondary-color); margin-bottom: 5px; }
    .dashboard-header p { color: #94a3b8; font-size: 14px; }

    /* الجدول */
    .table-section { background: white; padding: 10px; border-radius: 20px; }
    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    
    .modern-table th { text-align: right; color: #94a3b8; padding: 10px 20px; font-size: 13px; font-weight: 500; }
    .modern-table td { padding: 15px 20px; vertical-align: middle; background: white; border-top: 1px solid #f8fafc; border-bottom: 1px solid #f8fafc; }
    
    /* تدوير الحواف للأسطر */
    .modern-table td:first-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid #f8fafc; }
    .modern-table td:last-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid #f8fafc; }

    /* رقم الطلب */
    .id-badge { background: #f1f5f9; padding: 5px 10px; border-radius: 6px; font-family: monospace; font-size: 13px; color: var(--secondary-color); }

    /* العميل */
    .user-cell { display: flex; align-items: center; gap: 10px; }
    .avatar-circle { width: 35px; height: 35px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; }

    /* ألوان الحالات (Badges) */
    .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; }
    
    .status-badge.pending { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; } /* برتقالي */
    .status-badge.processing { background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe; } /* أزرق */
    .status-badge.shipped { background: #f3e8ff; color: #9333ea; border: 1px solid #e9d5ff; } /* بنفسجي */
    .status-badge.completed { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; } /* أخضر */
    .status-badge.cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; } /* أحمر */

    /* زر العرض */
    .action-btn.view {
        width: 35px; height: 35px; background: #f8fafc; color: var(--secondary-color);
        display: flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; transition: 0.2s;
    }
    .action-btn.view:hover { background: var(--primary-color); color: white; }
    
    /* تأثير التحويم */
    .modern-table tbody tr:hover td { background: #f8fafc; }
</style>
</div></body></html>
