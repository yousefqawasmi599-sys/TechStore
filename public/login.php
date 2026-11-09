<?php
require_once __DIR__ . '/bootstrap.php';
include 'config.php';
require_once __DIR__ . '/auth.php';


$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
    $password = $_POST['password'] ?? '';
    if (!is_string($password)) fail_request('بيانات غير صالحة.');
    $attemptKey = login_attempt($pdo, $email);
    
    // جلب بيانات المستخدم بناءً على الإيميل
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // التحقق من كلمة المرور
    if ($user && password_verify($password, $user['password'])) {
        $clear = $pdo->prepare('DELETE FROM auth_attempts WHERE attempt_key=?');
        $clear->execute([$attemptKey]);
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $rehash = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
            $rehash->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        session_regenerate_id(true);
        // تسجيل الدخول بنجاح
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['user_type']; // (admin أو user)

        // إذا كان أدمن يذهب للوحة التحكم، وإذا زبون يذهب للرئيسية
        if($user['user_type'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $msg = '<div class="alert error">البريد الإلكتروني أو كلمة المرور غير صحيحة!</div>';
    }
}
include 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>مرحباً بعودتك 👋</h2>
            <p>سجل الدخول للمتابعة من حيث توقفت</p>
        </div>

        <?php echo $msg; ?>

        <form method="POST" class="auth-form">
            <div class="input-box">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email"  aria-label="البريد الإلكتروني" placeholder="البريد الإلكتروني" required>
            </div>

            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password"  aria-label="كلمة المرور" placeholder="كلمة المرور" required>
            </div>

            <button type="submit" class="btn-auth">تسجيل الدخول <i class="fa-solid fa-arrow-left"></i></button>
        </form>
        
        <div class="auth-footer">
            ليس لديك حساب؟ <a href="register.php">أنشئ حساباً جديداً</a>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
