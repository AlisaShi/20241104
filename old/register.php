<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>註冊</title>
</head>
<body>
    <h1>註冊</h1>
    
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    
    <form action="register_action.php" method="post">
        <label for="username">用戶名稱：</label>
        <input type="text" id="username" name="username" required><br>

        <label for="email">電子郵件：</label>
        <input type="email" id="email" name="email" required><br>

        <label for="password">密碼：</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">註冊</button>
    </form>
</body>
</html>
