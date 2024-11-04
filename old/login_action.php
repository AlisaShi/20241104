<?php
require 'AuthController.php';

$auth = new AuthController();
$result = $auth->login($_POST['username'], $_POST['password']);

// 檢查登入結果並進行跳轉
if ($result === "登入成功") {
    // 登入成功後跳轉到首頁
    header("Location: index.php?message=" . urlencode("歡迎登入"));
    exit;
} else {
    // 登入失敗，跳轉回登入頁面並顯示錯誤
    header("Location: login.php?error=" . urlencode($result));
    exit;
}
?>
