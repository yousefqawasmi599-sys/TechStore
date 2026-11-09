<?php
require_once __DIR__ . '/../bootstrap.php';
include '../config.php'; // الخروج من مجلد الأدمن لجلب ملف الاتصال
require_admin();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم | TechStore</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ستايل خاص بلوحة التحكم فقط */
        body {
            display: flex;
            background-color: #f1f5f9;
            min-height: 100vh;
        }
        
        /* القائمة الجانبية */
        .sidebar {
            width: 260px;
            background: var(--secondary-color);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
            right: 0;
            top: 0;
            display: flex;
            flex-direction: column;
        }

        .admin-logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 40px;
            color: white;
            text-align: center;
        }
        
        .admin-logo span { color: var(--accent-color); }

        .sidebar-menu {
            list-style: none;
            flex: 1;
        }

        .sidebar-menu li { margin-bottom: 10px; }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .logout-btn {
            background: #ef4444;
            text-align: center;
            padding: 10px;
            border-radius: 10px;
            color: white;
            margin-top: auto; /* يدفعه للأسفل */
        }

        /* المحتوى الرئيسي */
        .main-content {
            margin-right: 260px; /* لترك مساحة للسايدبار */
            width: calc(100% - 260px);
            padding: 30px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="../index.php" class="admin-logo">
            <i class="fa-solid fa-bolt"></i> Tech<span>Admin</span>
        </a>

        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) === 'dashboard.php' ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> الرئيسية</a></li>
            <li><a href="products.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) === 'products.php' ? 'active' : ''; ?>"><i class="fa-solid fa-mobile-screen"></i> المنتجات</a></li>
            <li><a href="add_product.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) === 'add_product.php' ? 'active' : ''; ?>"><i class="fa-solid fa-plus-circle"></i> إضافة منتج</a></li>
            <li><a href="orders.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) === 'orders.php' ? 'active' : ''; ?>"><i class="fa-solid fa-box-open"></i> الطلبات</a></li>
            <li><a href="users.php" class="<?php echo basename($_SERVER['SCRIPT_NAME']) === 'users.php' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> المستخدمين</a></li>
        </ul>

        <form action="../logout.php" method="POST"><button type="submit" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> تسجيل خروج</button></form>
    </div>

    <div class="main-content">
