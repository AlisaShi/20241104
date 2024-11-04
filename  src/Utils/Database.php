// src/Utils/Database.php
<?php
namespace App\Utils;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = require dirname(__DIR__) . '/../config/database.php';
        $dbConfig = $config['connections'][$config['default']];

        try {
            $this->connection = new \PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password']
            );
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}