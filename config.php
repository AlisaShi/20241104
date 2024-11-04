<?php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'food_delivery');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_URL', 'http://localhost/food-delivery');

// 1. User Account System (用戶帳號系統)
class UserSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function register($data) {
        $sql = "INSERT INTO users (username, email, password, phone, address) 
                VALUES (:username, :email, :password, :phone, :address)";
        $stmt = $this->db->prepare($sql);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $stmt->execute($data);
    }

    public function login($username, $password) {
        $sql = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            return true;
        }
        return false;
    }

    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET 
                email = :email, 
                phone = :phone, 
                address = :address 
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $data['user_id'] = $userId;
        return $stmt->execute($data);
    }
}

// 2. Restaurant and Menu System (餐廳與菜單展示之子系統)
class RestaurantSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getRestaurants($filters = []) {
        $sql = "SELECT * FROM restaurants";
        if (!empty($filters)) {
            $sql .= " WHERE " . implode(' AND ', $filters);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuItems($restaurantId) {
        $sql = "SELECT * FROM menu_items WHERE restaurant_id = :restaurant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['restaurant_id' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchRestaurants($keyword) {
        $sql = "SELECT * FROM restaurants 
                WHERE name LIKE :keyword 
                OR description LIKE :keyword";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['keyword' => "%$keyword%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 3. Shopping Cart System (購物車子系統)
class CartSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function addToCart($userId, $itemId, $quantity) {
        $sql = "INSERT INTO cart (user_id, item_id, quantity) 
                VALUES (:user_id, :item_id, :quantity) 
                ON DUPLICATE KEY UPDATE quantity = quantity + :quantity";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'item_id' => $itemId,
            'quantity' => $quantity
        ]);
    }

    public function updateQuantity($userId, $itemId, $quantity) {
        $sql = "UPDATE cart 
                SET quantity = :quantity 
                WHERE user_id = :user_id AND item_id = :item_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'quantity' => $quantity,
            'user_id' => $userId,
            'item_id' => $itemId
        ]);
    }

    public function getCart($userId) {
        $sql = "SELECT c.*, m.name, m.price, m.restaurant_id 
                FROM cart c 
                JOIN menu_items m ON c.item_id = m.item_id 
                WHERE c.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearCart($userId) {
        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }
}

// 4. Payment System (支付子系統)
class PaymentSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function processPayment($orderId, $amount, $paymentMethod) {
        // In a real system, this would integrate with a payment gateway
        try {
            $sql = "INSERT INTO payments (order_id, amount, payment_method, status) 
                    VALUES (:order_id, :amount, :payment_method, 'completed')";
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);

            if ($success) {
                $this->updateOrderPaymentStatus($orderId, 'paid');
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function updateOrderPaymentStatus($orderId, $status) {
        $sql = "UPDATE orders 
                SET payment_status = :status 
                WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'order_id' => $orderId
        ]);
    }
}

// 5. Order System (訂單查詢子系統)
class OrderSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function createOrder($userId, $cartItems) {
        try {
            $this->db->beginTransaction();

            // Create order
            $sql = "INSERT INTO orders (user_id, restaurant_id, total_amount, status) 
                    VALUES (:user_id, :restaurant_id, :total_amount, 'pending')";
            $stmt = $this->db->prepare($sql);
            
            $totalAmount = array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $cartItems));

            $stmt->execute([
                'user_id' => $userId,
                'restaurant_id' => $cartItems[0]['restaurant_id'],
                'total_amount' => $totalAmount
            ]);
            
            $orderId = $this->db->lastInsertId();

            // Create order items
            foreach ($cartItems as $item) {
                $sql = "INSERT INTO order_items (order_id, item_id, quantity, price) 
                        VALUES (:order_id, :item_id, :quantity, :price)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'order_id' => $orderId,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getOrderHistory($userId) {
        $sql = "SELECT o.*, r.name as restaurant_name 
                FROM orders o 
                JOIN restaurants r ON o.restaurant_id = r.restaurant_id 
                WHERE o.user_id = :user_id 
                ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderDetails($orderId) {
        $sql = "SELECT oi.*, m.name 
                FROM order_items oi 
                JOIN menu_items m ON oi.item_id = m.item_id 
                WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders 
                SET status = :status 
                WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'order_id' => $orderId
        ]);
    }
}

// 6. Review System (評價與回饋子系統)
class ReviewSystem {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function addReview($orderId, $userId, $restaurantId, $rating, $comment) {
        $sql = "INSERT INTO reviews (order_id, user_id, restaurant_id, rating, comment) 
                VALUES (:order_id, :user_id, :restaurant_id, :rating, :comment)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
            'rating' => $rating,
            'comment' => $comment
        ]);
    }

    public function getRestaurantReviews($restaurantId) {
        $sql = "SELECT r.*, u.username 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.restaurant_id = :restaurant_id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['restaurant_id' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRestaurantRating($restaurantId) {
        $sql = "UPDATE restaurants 
                SET rating = (
                    SELECT AVG(rating) 
                    FROM reviews 
                    WHERE restaurant_id = :restaurant_id
                ) 
                WHERE restaurant_id = :restaurant_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['restaurant_id' => $restaurantId]);
    }
}