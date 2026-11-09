<?php
require __DIR__ . '/config.php';
$msg = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100 || $name === '' || mb_strlen($name) > 100 || strlen($password) < 8 || strlen($password) > 72) {
        http_response_code(400);
        $msg = '<div class="alert error" role="alert">أدخل اسمًا وبريدًا صحيحين وكلمة مرور من 8 إلى 72 بايت.</div>';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,user_type,role) VALUES (?,?,?,'user','user')");
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user';
            header('Location: index.php');
            exit;
        } catch (PDOException $error) {
            if ($error->getCode() !== '23000') throw $error;
            http_response_code(409);
            $msg = '<div class="alert error" role="alert">هذا البريد الإلكتروني مسجل بالفعل.</div>';
        }
    }
}
include 'header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h2>حساب جديد 🚀</h2>
            <p>انضم إلينا واستمتع بتجربة تسوق فريدة</p>
        </div>

        <?php echo $msg; ?>

        <form method="POST" class="auth-form">
            <div class="input-box">
                <i class="fa-regular fa-user"></i>
                <input type="text" name="name"  aria-label="الاسم" maxlength="100" autocomplete="name" value="<?php echo e($name); ?>" placeholder="الاسم الكامل" required>
            </div>

            <div class="input-box">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email"  aria-label="البريد الإلكتروني" maxlength="100" autocomplete="email" value="<?php echo e($email); ?>" placeholder="البريد الإلكتروني" required>
            </div>

            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password"  aria-label="كلمة المرور" minlength="8" maxlength="72" autocomplete="new-password" placeholder="كلمة المرور" required>
            </div>

            <button type="submit" class="btn-auth">إنشاء الحساب</button>
        </form>
        
        <div class="auth-footer">
            لديك حساب بالفعل؟ <a href="login.php">سجل دخولك</a>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>