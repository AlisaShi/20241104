// config/database.php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "food_delivery";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

// models/User.php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "INSERT INTO " . $this->table_name . " 
                 (username, email, password) VALUES 
                 (:username, :email, :password)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $hashed_password);
        
        return $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE username = :username LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }
}

// controllers/UserController.php
class UserController {
    private $user;

    public function __construct($db) {
        $this->user = new User($db);
    }

    public function handleRegistration() {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if($this->user->register($username, $email, $password)) {
                return ["success" => true, "message" => "Registration successful"];
            }
            return ["success" => false, "message" => "Registration failed"];
        }
    }
}