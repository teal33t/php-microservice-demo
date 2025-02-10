<?php

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private $connection;
    private static $instance = null;

    private function __construct()
    {
        try {
            $dsn = 'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') .
                  ';port=' . (getenv('DB_PORT') ?: '3306') .
                  ';dbname=' . (getenv('DB_DATABASE') ?: 'product_service') .
                  ';charset=utf8mb4';
            $username = getenv('DB_USERNAME') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function __clone() {}
    private function __wakeup() {}
}