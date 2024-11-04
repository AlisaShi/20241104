// src/Models/Restaurant.php
<?php
namespace App\Models;

class Restaurant extends BaseModel {
    protected $table = 'restaurants';
    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'cuisine_type',
        'opening_hours',
        'delivery_fee',
        'minimum_order',
        'image_url',
        'status'
    ];

    public function getMenuItems() {
        $stmt = $this->db->prepare("
            SELECT * FROM menu_items 
            WHERE restaurant_id = ? 
            AND status = 'active'
            ORDER BY category
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getReviews() {
        $stmt = $this->db->prepare("
            SELECT reviews.*, users.name as user_name 
            FROM reviews 
            JOIN users ON reviews.user_id = users.id
            WHERE reviews.restaurant_id = ?
            ORDER BY reviews.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getAverageRating() {
        $stmt = $this->db->prepare("
            SELECT AVG(rating) as average_rating 
            FROM reviews 
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$this->id]);
        return round($stmt->fetch(\PDO::FETCH_OBJ)->average_rating, 1);
    }
}