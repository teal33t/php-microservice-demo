<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Service\AuthService;
use App\Service\MessageProducer;

class OrderController
{
    private $orderRepository;
    private $authService;
    private $messageProducer;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
        $this->authService = new AuthService();
        $this->messageProducer = new MessageProducer();
    }

    public function createOrder()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['products']) || empty($data['products'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Products are required']);
            return;
        }

        try {
            $data['user_id'] = $user['userId'];
            $data['status'] = 'pending';
            $orderId = $this->orderRepository->create($data);
            
            // Notify other services about the new order
            $this->messageProducer->send('order_created', [
                'order_id' => $orderId,
                'user_id' => $user['userId'],
                'products' => $data['products']
            ]);

            $order = $this->orderRepository->findById($orderId);
            echo json_encode(['order' => $order]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to create order']);
        }
    }

    public function getOrder()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $orderId = $_GET['id'] ?? null;
        if (!$orderId) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Order ID is required']);
            return;
        }

        try {
            $order = $this->orderRepository->findById($orderId);
            if (!$order) {
                header('HTTP/1.0 404 Not Found');
                echo json_encode(['error' => 'Order not found']);
                return;
            }

            if ($order['user_id'] !== $user['userId']) {
                header('HTTP/1.0 403 Forbidden');
                echo json_encode(['error' => 'Access denied']);
                return;
            }

            echo json_encode(['order' => $order]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to fetch order']);
        }
    }

    public function listUserOrders()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $orders = $this->orderRepository->findByUserId($user['userId']);
            echo json_encode(['orders' => $orders]);
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to fetch orders']);
        }
    }

    public function updateOrderStatus()
    {
        $user = $this->authService->getAuthenticatedUser(apache_request_headers());
        if (!$user) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $orderId = $_GET['id'] ?? null;
        if (!$orderId) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Order ID is required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['status'])) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode(['error' => 'Status is required']);
            return;
        }

        try {
            $success = $this->orderRepository->updateStatus($orderId, $data['status']);
            if ($success) {
                $order = $this->orderRepository->findById($orderId);
                echo json_encode(['order' => $order]);

                // Notify other services about the status change
                $this->messageProducer->send('order_status_changed', [
                    'order_id' => $orderId,
                    'status' => $data['status']
                ]);
            } else {
                header('HTTP/1.0 500 Internal Server Error');
                echo json_encode(['error' => 'Failed to update order status']);
            }
        } catch (\Exception $e) {
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode(['error' => 'Failed to update order status']);
        }
    }
}