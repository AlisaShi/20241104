// src/Services/OrderService.php
<?php
namespace App\Services;

use App\Models\Order;
use App\Services\CartService;
use App\Utils\Session;

class OrderService {
    private $orderModel;
    private $cartService;

    public function __construct() {
        $this->orderModel = new Order();
        $this->cartService = new CartService();
    }

    public function createOrder($data) {
        $cart = $this->cartService->getCart();
        
        if (empty($cart['items'])) {
            throw new \Exception('Cart is empty');
        }

        // Start transaction
        $this->orderModel->db->beginTransaction();

        try {
            // Create order
            $order = $this->orderModel->create([
                'user_id' => Session::get('user')->id,
                'restaurant_id' => $cart['restaurant_id'],
                'total_amount' => $cart['total'],
                'delivery_address' => $data['delivery_address'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'delivery_fee' => $data['delivery_fee'] ?? 0,
                'special_instructions' => $data['special_instructions'] ?? null
            ]);

            // Create order items
            foreach ($cart['items'] as $item) {
                $stmt = $this->orderModel->db->prepare("
                    INSERT INTO order_items (order_id, menu_item_id, quantity, price)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $order->id,
                    $item['id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $this->orderModel->db->commit();
            $this->cartService->clear();

            return $order;
        } catch (\Exception $e) {
            $this->orderModel->db->rollBack();
            throw $e;
        }
    }

    public function getUserOrders($userId) {
        return $this->orderModel->where('user_id', '=', $userId);
    }

    public function updateOrderStatus($orderId, $status) {
        return $this->orderModel->update($orderId, ['status' => $status]);
    }
}