// src/Services/RestaurantService.php
<?php
namespace App\Services;

use App\Models\Restaurant;
use App\Utils\FileUploader;

class RestaurantService {
    private $restaurantModel;

    public function __construct() {
        $this->restaurantModel = new Restaurant();
    }

    public function getRestaurants($filters = []) {
        $query = "SELECT * FROM restaurants WHERE status = 'active'";
        $params = [];

        if (!empty($filters['cuisine_type'])) {
            $query .= " AND cuisine_type = ?";
            $params[] = $filters['cuisine_type'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        if (!empty($filters['rating'])) {
            $query .= " AND id IN (
                SELECT restaurant_id 
                FROM reviews 
                GROUP BY restaurant_id 
                HAVING AVG(rating) >= ?
            )";
            $params[] = $filters['rating'];
        }

        $stmt = $this->restaurantModel->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getRestaurantDetails($id) {
        $restaurant = $this->restaurantModel->find($id);
        if (!$restaurant) {
            throw new \Exception('Restaurant not found');
        }

        $restaurant->menu_items = $this->restaurantModel->getMenuItems();
        $restaurant->reviews = $this->restaurantModel->getReviews();
        $restaurant->average_rating = $this->restaurantModel->getAverageRating();

        return $restaurant;
    }

    public function createRestaurant($data) {
        // Handle image upload if present
        if (isset($_FILES['image'])) {
            $uploader = new FileUploader(['jpg', 'jpeg', 'png']);
            $data['image_url'] = $uploader->upload($_FILES['image'], 'restaurants');
        }

        return $this->restaurantModel->create($data);
    }
}