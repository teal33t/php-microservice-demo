<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\AuthService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OrderController
{
    private $orderRepository;
    private $authService;
    private $httpClient;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->authService = new AuthService();
        $this->httpClient = new Client([
            'base_uri' => getenv('PRODUCT_SERVICE_URL', 'http://product-service'),
            'timeout'  => 5.0,
        ]);
    }

    public function createOrder()
    {
        $userId = $this->authService->middleware();
        if (!$userId) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['items']) || empty($data['items'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Order items are required']);
            return;
        }

        try {
            // Validate products and calculate total
            $totalAmount = 0;
            foreach ($data['items'] as &$item) {
                $response = $this->httpClient->get("/api/products/{$item['product_id']}");
                $product = json_decode($response->getBody(), true);
                
                if (!$product) {
                    header('HTTP/1.0 404 Not Found');
                    echo json_encode(['error' => "Product {$item['product_id']} not found"]);
                    return;
                }
                
                $item['price'] = $product['price'];
                $totalAmount += $product['price'] * $item['quantity'];
            }

            $orderData = [
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'items' => $data['items']
            ];

            $orderId = $this->orderRepository->create($orderData);
            $order = $this->orderRepository->findById($orderId);
            $order['items'] = $this->orderRepository->getOrderItems($orderId);

            echo json_encode($order);

        } catch (GuzzleException $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to process order']);
            return;
        }
    }

    public function getOrder(int $id)
    {
        $userId = $this->authService->middleware();
        if (!$userId) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $order = $this->orderRepository->findById($id);
        if (!$order || $order['user_id'] !== $userId) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $order['items'] = $this->orderRepository->getOrderItems($id);
        echo json_encode($order);
    }

    public function getUserOrders()
    {
        $userId = $this->authService->middleware();
        if (!$userId) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $orders = $this->orderRepository->findByUserId($userId);
        foreach ($orders as &$order) {
            $order['items'] = $this->orderRepository->getOrderItems($order['id']);
        }

        echo json_encode($orders);
    }

    public function updateOrderStatus(int $id)
    {
        $userId = $this->authService->middleware();
        if (!$userId) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $order = $this->orderRepository->findById($id);
        if (!$order || $order['user_id'] !== $userId) {
            header('HTTP/1.0 404 Not Found');
            echo json_encode(['error' => 'Order not found']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['status'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Status is required']);
            return;
        }

        if ($this->orderRepository->update($id, ['status' => $data['status']])) {
            $updatedOrder = $this->orderRepository->findById($id);
            $updatedOrder['items'] = $this->orderRepository->getOrderItems($id);
            echo json_encode($updatedOrder);
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update order']);
        }
    }
}