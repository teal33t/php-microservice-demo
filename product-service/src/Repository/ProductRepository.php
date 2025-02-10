<?php

namespace App\Repository;

use App\Database\Database;
use PDO;
use PDOException;

class ProductRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, price, description, stock) VALUES (:name, :price, :description, :stock)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'price' => $data['price'],
            'description' => $data['description'] ?? '',
            'stock' => $data['stock'] ?? 0
        ]);

        $id = $this->db->lastInsertId();
        return $this->findById($id);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM products');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $product ?: null;
    }

    public function update(int $id, array $data): ?array
    {
        $fields = [];
        $params = ['id' => $id];

        foreach (['name', 'price', 'description', 'stock'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id'
        );

        $stmt->execute($params);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public function updateStock(int $id, int $quantity): bool
    {
        $stmt = $this->db->prepare('UPDATE products SET stock = stock + :quantity WHERE id = :id');
        return $stmt->execute([
            'id' => $id,
            'quantity' => $quantity
        ]);
    }
}