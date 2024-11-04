<?php
require 'AuthController.php';

$auth = new AuthController();
$result = $auth->register($_POST['username'], $_POST['password'], $_POST['email']);

// 檢查註冊結果並進行跳轉
if ($result === "註冊成功") {
    // 註冊成功後跳轉到登入頁面
    header("Location: login.php?message=" . urlencode("註冊成功，請登入"));
    exit;
} else {
    // 註冊失敗，跳轉回註冊頁面並顯示錯誤
    header("Location: register.php?error=" . urlencode($result));
    exit;
}
?>
