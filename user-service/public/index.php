<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\UserController;

header('Content-Type: application/json');

$router = new Router();

// Auth routes
$router->post('/login', 'UserController@login');
$router->post('/register', 'UserController@register');

// Protected routes
$router->get('/profile', 'UserController@getProfile');
$router->put('/profile', 'UserController@updateProfile');

// Handle the request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);