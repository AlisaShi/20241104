<?php
class AuthController {
    private $db;

    public function __construct() {
        // 初始化資料庫連線
        $this->db = new mysqli('localhost', 'root', '', '20241104');

        // 檢查連線是否成功
        if ($this->db->connect_error) {
            die("資料庫連線失敗: " . $this->db->connect_error);
        }
    }

    // 註冊功能
    public function register($username, $password, $email) {
        // 密碼哈希化
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 準備 SQL 語句，並進行插入操作
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $password_hash, $email);

        if ($stmt->execute()) {
            return "註冊成功";
        } else {
            return "註冊失敗：" . $stmt->error;
        }
    }

    // 登入功能
    public function login($username, $password) {
        // 準備 SQL 語句，並檢索用戶資訊
        $stmt = $this->db->prepare("SELECT user_id, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // 驗證密碼
            if (password_verify($password, $user['password_hash'])) {
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                return "登入成功";
            } else {
                return "密碼錯誤";
            }
        } else {
            return "用戶不存在";
        }
    }

    // 登出功能
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        return "登出成功";
    }

    // 關閉資料庫連線
    public function __destruct() {
        $this->db->close();
    }
}
?>
