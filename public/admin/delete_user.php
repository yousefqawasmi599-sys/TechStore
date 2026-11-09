<?php
require __DIR__ . '/../config.php';
require_admin();
require_post();
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) fail_request('معرّف المستخدم غير صالح.');
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'");
$stmt->execute([$id]);
header('Location: users.php');
exit;
