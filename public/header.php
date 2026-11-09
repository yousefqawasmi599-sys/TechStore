<?php
require_once __DIR__ . '/bootstrap.php';
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isOffers = ($_GET['show'] ?? '') === 'offers';
// حساب عدد العناصر في السلة
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore | متجر المستقبل</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="container nav-content">
            
            <a href="index.php" class="logo">
                <i class="fa-solid fa-bolt"></i> Tech<span class="highlight">Store</span>
            </a>

            <ul class="nav-links">
                <li><a href="index.php" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">الرئيسية</a></li>
                <li><a href="shop.php" class="<?php echo $currentPage === 'shop.php' && !$isOffers ? 'active' : ''; ?>">أحدث الهواتف</a></li> 
                <li><a href="shop.php?show=offers" class="<?php echo $currentPage === 'shop.php' && $isOffers ? 'active' : ''; ?>">العروض</a></li>
            </ul>

            <div class="nav-tools">
                
                <form action="shop.php" method="GET" class="search-form">
                    <input type="text" name="search" aria-label="البحث عن المنتجات" placeholder="ابحث..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" aria-label="بحث"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                
                <a href="cart.php" class="icon-btn cart-btn">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span class="badge"><?php echo $cart_count; ?></span>
                </a>

                <?php if(isset($_SESSION['user_id'])): ?>
                    
                    <div class="user-menu" style="display: flex; gap: 10px; align-items: center;">
                        <span style="font-weight: bold; color: var(--primary-color); font-size: 14px;">
                            مرحباً، <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </span>
                        
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                             <a href="admin/dashboard.php" class="icon-btn" title="لوحة التحكم" style="color: var(--secondary-color);">
                                <i class="fa-solid fa-gauge-high"></i>
                             </a>
                        <?php endif; ?>
                        
                        <form action="logout.php" method="POST"><button type="submit" class="btn-login" style="background: #fee2e2; color: #ef4444; padding: 8px 15px; font-size: 13px;">
                            <i class="fa-solid fa-right-from-bracket"></i> خروج
                        </button></form>
                    </div>

                <?php else: ?>
                    
                    <a href="login.php" class="btn-login">
                        <i class="fa-regular fa-user"></i> تسجيل دخول
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </nav>