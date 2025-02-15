<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\ProductController;

// Set headers for JSON API
header('Content-Type: application/json');

// Initialize router
$router = new Router();

// Product routes
$router->post('/products', 'ProductController@createProduct');
$router->get('/products', 'ProductController@getAllProducts');
$router->get('/products/{id}', 'ProductController@getProduct');
$router->post('/products/{id}', 'ProductController@updateProduct');
$router->post('/products/{id}/delete', 'ProductController@deleteProduct');

// Extract ID from URL for routes with parameters
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('/\/products\/(\d+)(?:\/delete)?/', $path, $matches)) {
    $_GET['id'] = $matches[1];
    $path = preg_replace('/\d+/', '{id}', $path);
}

// Dispatch the request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $path
);