<?php

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private $connection;

    public function __construct()
    {
        try {
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_DATABASE') ?: 'user_service';
            $username = getenv('DB_USERNAME') ?: 'dbuser';
            $password = getenv('DB_PASSWORD') ?: 'dbpass';
            
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->connection->commit();
    }

    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
}