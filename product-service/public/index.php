<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\ProductController;

header('Content-Type: application/json');

$router = new Router();

// Public routes
$router->get('/products', 'ProductController@listProducts');
$router->get('/products/detail', 'ProductController@getProduct');

// Protected routes
$router->post('/products', 'ProductController@createProduct');
$router->put('/products', 'ProductController@updateProduct');
$router->delete('/products', 'ProductController@deleteProduct');

// Handle the request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);