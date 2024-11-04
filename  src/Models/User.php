// src/Models/User.php
<?php
namespace App\Models;

class User extends BaseModel {
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'role'
    ];

    public function create(array $data) {
        // Hash password before saving
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }

    public function orders() {
        $stmt = $this->db->prepare("
            SELECT orders.* 
            FROM orders 
            WHERE orders.user_id = ?
            ORDER BY orders.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}