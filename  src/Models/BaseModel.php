// src/Models/BaseModel.php
<?php
namespace App\Models;

use App\Utils\Database;

abstract class BaseModel {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function create(array $data) {
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($fillableData));
        $values = implode(', ', array_fill(0, count($fillableData), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($fillableData));
        
        return $this->find($this->db->lastInsertId());
    }

    public function update($id, array $data) {
        $fillableData = array_intersect_key($data, array_flip($this->fillable));
        $setClause = implode(', ', array_map(function($column) {
            return "{$column} = ?";
        }, array_keys($fillableData)));
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([...array_values($fillableData), $id]);
        
        return $this->find($id);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    public function where($column, $operator, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}