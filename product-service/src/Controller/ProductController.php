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

    public function listProducts()
    {
        try {
            $products = $this->productRepository->findAll();
            echo json_encode(['products' => $products]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to fetch products']);
        }
    }

    public function getProduct()
    {
        $productId = $_GET['id'] ?? null;
        if (!$productId) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        try {
            $product = $this->productRepository->findById($productId);
            if (!$product) {
                header('HTTP/1.0 404 Not Found');
                echo json_encode(['error' => 'Product not found']);
                return;
            }
            echo json_encode(['product' => $product]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to fetch product']);
        }
    }

    public function createProduct()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['name']) || !isset($data['price']) || !isset($data['description'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Name, price and description are required']);
            return;
        }

        try {
            $productId = $this->productRepository->create($data);
            $product = $this->productRepository->findById($productId);
            echo json_encode(['product' => $product]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to create product']);
        }
    }

    public function updateProduct()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $productId = $_GET['id'] ?? null;
        if (!$productId) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data)) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'No data provided']);
            return;
        }

        try {
            $success = $this->productRepository->update($productId, $data);
            if ($success) {
                $product = $this->productRepository->findById($productId);
                echo json_encode(['product' => $product]);
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo json_encode(['error' => 'Failed to update product']);
            }
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update product']);
        }
    }

    public function deleteProduct()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $productId = $_GET['id'] ?? null;
        if (!$productId) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        try {
            $success = $this->productRepository->delete($productId);
            if ($success) {
                echo json_encode(['message' => 'Product deleted successfully']);
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo json_encode(['error' => 'Failed to delete product']);
            }
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to delete product']);
        }
    }
}