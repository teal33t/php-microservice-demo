<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controller\UserController;

// Set headers for JSON API
header('Content-Type: application/json');

// Initialize router
$router = new Router();

// Auth routes
$router->post('/login', 'UserController@login');
$router->post('/register', 'UserController@register');

// Protected routes
$router->get('/profile', 'UserController@getProfile');
$router->post('/profile', 'UserController@updateProfile');

// Dispatch the request
$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);