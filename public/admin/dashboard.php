<?php
include 'header.php';

// جلب البيانات (نفس المنطق السابق)
$stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$orders_count = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'");
$total_revenue = $stmt->fetchColumn() ?: 0; // إذا كان null نضع 0

$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$products_count = $stmt->fetchColumn();

// جلب آخر 5 طلبات
$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-header">
    <div>
        <h1>مرحباً، المدير 👋</h1>
        <p>إليك ملخص ما يحدث في متجرك اليوم</p>
    </div>
    <div class="date-badge">
        <i class="fa-regular fa-calendar"></i> <?php echo date('d M, Y'); ?>
    </div>
</div>

<div class="stats-grid">
    
    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-info">
                <span class="stat-label">إجمالي المبيعات المكتملة</span>
                <h3 class="stat-number">$<?php echo number_format($total_revenue, 2); ?></h3>
            </div>
            <div class="stat-icon revenue-icon"><i class="fa-solid fa-sack-dollar"></i></div>
        </div>
        
        <span class="stat-note">إجمالي الطلبات التي تم تسليمها</span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-info">
                <span class="stat-label">الطلبات الجديدة</span>
                <h3 class="stat-number"><?php echo $orders_count; ?></h3>
            </div>
            <div class="stat-icon orders-icon"><i class="fa-solid fa-box"></i></div>
        </div>
        
        <span class="stat-note">بانتظار المعالجة</span>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-info">
                <span class="stat-label">المنتجات النشطة</span>
                <h3 class="stat-number"><?php echo $products_count; ?></h3>
            </div>
            <div class="stat-icon products-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
        </div>
        
        <span class="stat-note">عدد المنتجات المعروضة في المتجر</span>
    </div>
</div>

<div class="table-section">
    <div class="section-title">
        <h3>أحدث الطلبات</h3>
        <a href="orders.php" class="btn-link">عرض الكل</a>
    </div>

    <div class="table-responsive">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>العميل</th>
                    <th>التكلفة</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td><span class="id-badge">#<?php echo e($order['id']); ?></span></td>
                    <td>
                        <div class="user-cell">
                            <div class="avatar-circle"><?php echo e(mb_substr($order['name'], 0, 1, 'UTF-8')); ?></div>
                            <span><?php echo e($order['name']); ?></span>
                        </div>
                    </td>
                    <td style="font-weight: bold;">$<?php echo e($order['total_amount']); ?></td>
                    <td>
                        <?php 
                            $status_color = 'gray';
                            if($order['status'] == 'pending') $status_color = 'orange';
                            if($order['status'] == 'completed') $status_color = 'green';
                            if($order['status'] == 'cancelled') $status_color = 'red';
                        ?>
                        <span class="status-dot <?php echo $status_color; ?>"></span>
                        <?php echo e($order['status']); ?>
                    </td>
                    <td class="date-cell"><?php echo date('Y/m/d', strtotime($order['created_at'])); ?></td>
                    <td><a href="order_details.php?id=<?php echo e($order['id']); ?>" class="action-btn"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    /* --- ستايل الداشبورد الجديد --- */
    
    /* الهيدر */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
    }
    .dashboard-header h1 { font-size: 26px; color: var(--secondary-color); margin-bottom: 5px; }
    .dashboard-header p { color: #94a3b8; font-size: 14px; }
    
    .date-badge {
        background: white;
        padding: 10px 20px;
        border-radius: 30px;
        color: #64748b;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        font-weight: 500;
    }

    /* شبكة البطاقات */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        transition: 0.3s;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); }

    .stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .stat-label { color: #94a3b8; font-size: 13px; font-weight: 600; display: block; margin-bottom: 8px; }
    .stat-number { font-size: 28px; color: var(--secondary-color); font-weight: 800; }

    /* الأيقونات الملونة */
    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .revenue-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .orders-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .products-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

    /* أشرطة التقدم */
    .progress-bar { height: 6px; background: #f1f5f9; border-radius: 10px; margin-bottom: 10px; overflow: hidden; }
    .fill { height: 100%; border-radius: 10px; }
    .revenue-fill { background: #10b981; }
    .orders-fill { background: #3b82f6; }
    .products-fill { background: #f59e0b; }

    .stat-note { font-size: 12px; color: #cbd5e1; }

    /* الجدول الحديث */
    .table-section { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); }
    .section-title { display: flex; justify-content: space-between; margin-bottom: 25px; }
    .section-title h3 { font-size: 18px; color: var(--secondary-color); }
    .btn-link { color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: 600; }

    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; } /* تباعد الأسطر */
    
    .modern-table th { 
        text-align: right; 
        color: #94a3b8; 
        font-weight: 500; 
        font-size: 13px; 
        padding: 0 20px 10px; 
    }

    .modern-table tbody tr {
        transition: 0.2s;
        background: #fff; /* خلفية للسطر نفسه */
    }

    .modern-table td {
        padding: 15px 20px;
        border-top: 1px solid #f8fafc;
        border-bottom: 1px solid #f8fafc;
        color: var(--secondary-color);
        font-size: 14px;
    }
    
    /* الحواف الدائرية للسطر */
    .modern-table td:first-child { border-right: 1px solid #f8fafc; border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
    .modern-table td:last-child { border-left: 1px solid #f8fafc; border-top-left-radius: 12px; border-bottom-left-radius: 12px; }

    .id-badge { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-family: monospace; }
    
    .user-cell { display: flex; align-items: center; gap: 10px; }
    .avatar-circle { 
        width: 32px; height: 32px; 
        background: var(--primary-color); 
        color: white; 
        border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 12px; 
    }

    .status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-left: 5px; }
    .status-dot.orange { background: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
    .status-dot.green { background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
    
    .action-btn { 
        width: 30px; height: 30px; 
        background: #f8fafc; 
        display: flex; align-items: center; justify-content: center; 
        border-radius: 8px; 
        color: #64748b; 
        transition: 0.2s;
    }
    .action-btn:hover { background: var(--primary-color); color: white; }
</style>
</div></body></html>
