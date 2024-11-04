<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>登入</title>
</head>
<body>
    <h1>登入</h1>
    <form action="login_action.php" method="post">
        <label for="username">用戶名稱：</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">密碼：</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">登入</button>
    </form>
</body>
</html>
