<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\AuthService;

class ProductController
{
    private $productRepository;
    private $authService;

    public function __construct()
    {
        $this->productRepository = new ProductRepository();
        $this->authService = new AuthService();
    }

    public function createProduct()
    {
        if (!$this->authService->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Only admin can create products']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['name']) || !isset($data['price'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Name and price are required']);
            return;
        }

        $product = $this->productRepository->create($data);
        echo json_encode($product);
    }

    public function getAllProducts()
    {
        $products = $this->productRepository->findAll();
        echo json_encode($products);
    }

    public function getProduct()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $product = $this->productRepository->findById($id);
        if (!$product) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        echo json_encode($product);
    }

    public function updateProduct()
    {
        if (!$this->authService->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Only admin can update products']);
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Update data is required']);
            return;
        }

        $product = $this->productRepository->update($id, $data);
        if (!$product) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        echo json_encode($product);
    }

    public function deleteProduct()
    {
        if (!$this->authService->isAdmin()) {
            header('HTTP/1.0 403 Forbidden');
            echo json_encode(['error' => 'Only admin can delete products']);
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $success = $this->productRepository->delete($id);
        if (!$success) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        echo json_encode(['message' => 'Product deleted successfully']);
    }
}