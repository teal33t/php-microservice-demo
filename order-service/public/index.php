<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\OrderController;

// Set headers for JSON API
header('Content-Type: application/json');

// Initialize router
$router = new Router();

// Order routes (all protected by auth)
$router->post('/orders', 'OrderController@createOrder');
$router->get('/orders', 'OrderController@getUserOrders');
$router->get('/orders/{id}', 'OrderController@getOrder');
$router->post('/orders/{id}/status', 'OrderController@updateOrderStatus');

// Extract ID from URL for routes with parameters
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('/\/orders\/(\d+)(?:\/status)?/', $path, $matches)) {
    $_GET['id'] = $matches[1];
    $path = preg_replace('/\d+/', '{id}', $path);
}

// Dispatch the request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $path
);