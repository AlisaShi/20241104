// src/Models/Order.php
<?php
namespace App\Models;

class Order extends BaseModel {
    protected $table = 'orders';
    protected $fillable = [
        'user_id',
        'restaurant_id',
        'total_amount',
        'delivery_address',
        'status',
        'payment_status',
        'delivery_fee',
        'special_instructions'
    ];

    public function getItems() {
        $stmt = $this->db->prepare("
            SELECT order_items.*, menu_items.name, menu_items.price
            FROM order_items 
            JOIN menu_items ON order_items.menu_item_id = menu_items.id
            WHERE order_items.order_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}