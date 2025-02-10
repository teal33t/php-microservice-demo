# Building Native PHP Microservices: A Demo Guide

This repository demonstrates an advanced implementation of microservices using native PHP features and industry best practices. It serves as both a practical guide and a showcase of expertise in creating scalable, maintainable microservices architecture using PHP.

## Table of Contents

1. [Understanding Microservices Architecture](#1-understanding-microservices-architecture)
2. [Architectural Overview](#2-architectural-overview)
3. [Setting Up the Project Structure](#3-setting-up-the-project-structure)
4. [Creating a Tiny PHP Framework for Microservices](#4-creating-a-tiny-php-framework-for-microservices)
5. [Building HTTP Communication in PHP](#5-building-http-communication-in-php)
6. [Autoloading and Namespaces](#6-autoloading-and-namespaces)
7. [Implementing Routing Logic](#7-implementing-routing-logic)
8. [Managing Database Access](#8-managing-database-access)
9. [Handling Inter-Service Communication](#9-handling-inter-service-communication)
10. [Security and Authentication](#10-security-and-authentication)
11. [Service Discovery](#11-service-discovery)
12. [Load Balancing and Scaling](#12-load-balancing-and-scaling)
13. [Monitoring and Logging](#13-monitoring-and-logging)
14. [Error Handling and Resilience](#14-error-handling-and-resilience)
15. [Conclusion](#15-conclusion)

## Introduction

In today's fast-paced development environment, microservices architecture is increasingly adopted for building scalable and maintainable applications. By decomposing a monolithic application into smaller, independent services, organizations can achieve greater agility, scalability, and fault tolerance.

Although PHP is traditionally used for monolithic web applications, this guide shows how PHP can be effectively utilized to create microservices. You will learn how to build a simple PHP micro-framework and put industry-standard best practices into action.

## 1. Understanding Microservices Architecture

### What Are Microservices?

Microservices architecture is a design approach where an application is composed of small, independent services that communicate over a network. Each service is self-contained and focused on a specific business capability.

### Benefits

- **Scalability:** Each service can be scaled independently.
- **Maintainability:** Smaller codebases are easier to manage and update.
- **Flexibility:** Services can be developed and deployed separately, potentially using different technologies.
- **Fault Isolation:** Failures in one service are less likely to affect others.

### Challenges Specific to PHP

- **Shared State Management:** Maintaining data consistency across services.
- **Inter-Service Communication:** Balancing synchronous and asynchronous interactions.
- **Security Concerns:** Managing authentication and authorization across services.
- **Operational Complexity:** Dealing with deployment, monitoring, and scaling multiple services.

## 2. Architectural Overview

### Decoupled Services

Each microservice is responsible for a specific domain or functionality. Examples include:

- **User Service:** Manages registrations, profiles, and authentication.
- **Order Service:** Handles order processing.
- **Product Service:** Manages product catalog and inventory.

### Independent Deployment

Ensure that each microservice is deployable without impacting others. This facilitates rapid feature updates and bug fixes.

### Communication Patterns

Microservices interact via methods such as:

- **HTTP/REST APIs:** For synchronous communication.
- **Message Queues:** For asynchronous exchanges using brokers like RabbitMQ or Kafka.
- **gRPC:** For high-performance, language-agnostic communication.

#### Synchronous vs. Asynchronous Communication

- **Synchronous:** The client waits for the response (request-response).
- **Asynchronous:** The client continues processing without waiting for an immediate response, enhancing scalability.

## 3. Setting Up the Project Structure

### Organizing Your Microservices

Treat each microservice as an independent project:

```
/user-service
    /src
    /public
    composer.json
/order-service
    /src
    /public
    composer.json
/product-service
    /src
    /public
    composer.json
```

### Version Control Best Practices

- **Separate Repositories:** Preferable for true isolation.
- **Monorepo Option:** Alternatively, use a monorepo structure with clear directory separations.

## 4. Creating a Tiny PHP Framework for Microservices

### Rationale

Creating your own minimalistic framework gives you full control over the structure and design, ensuring adherence to clean code principles.

### Key Components

- **Routing:** Maps HTTP requests to handlers.
- **Controllers:** Manage request processing and responses.
- **Responses:** Standardized responses to client requests.

### Basic Router Example

```php
namespace App\Core;

class Router {
    private $routes = [];

    public function get(string $path, callable $handler) {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler) {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $path) {
        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found";
            exit;
        }
        call_user_func($handler);
    }
}
```

## 5. Building HTTP Communication in PHP

### Using Native PHP

```php
$url = 'http://product-service/api/products/1';
$response = file_get_contents($url);
if ($response === false) {
    // Handle error
}
$data = json_decode($response, true);
```

### With cURL

```php
$ch = curl_init('http://auth-service/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'john_doe',
    'password' => 'secret',
]));
$response = curl_exec($ch);
if ($response === false) {
    $error = curl_error($ch);
}
curl_close($ch);
$data = json_decode($response, true);
```

## 6. Autoloading and Namespaces

### Composer Autoloading

Configure Composer to autoload your classes:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

### Organizing with Namespaces

Example of defining a controller:

```php
namespace App\Controller;

class UserController {
    public function listUsers() {
        // Logic to list users...
    }
}
```

## 7. Implementing Routing Logic

### Enhanced Router Implementation

```php
public function dispatch(string $method, string $path) {
    $handler = $this->routes[$method][$path] ?? null;
    if (!$handler) {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }
    if (is_callable($handler)) {
        call_user_func($handler);
    } elseif (is_string($handler)) {
        list($controller, $method) = explode('@', $handler);
        $controller = "App\\Controller\\$controller";
        if (class_exists($controller)) {
            $obj = new $controller();
            if (method_exists($obj, $method)) {
                call_user_func([$obj, $method]);
            }
        }
    }
}
```

## 8. Managing Database Access

### Database Connection Example

```php
namespace App\Database;

use PDO;

class Database {
    private $connection;

    public function __construct() {
        $dsn = 'mysql:host=localhost;dbname=user_service;charset=utf8mb4';
        $username = 'dbuser';
        $password = 'dbpass';
        $this->connection = new PDO($dsn, $username, $password);
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}
```

## 9. Handling Inter-Service Communication

### Example Using a Message Queue

```php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageProducer {
    public function send($queue, $data) {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $msg = new AMQPMessage(json_encode($data), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
        $channel->basic_publish($msg, '', $queue);
        $channel->close();
        $connection->close();
    }
}
```

## 10. Security and Authentication

### JWT Authentication Example

```php
use Firebase\JWT\JWT;

class AuthService {
    private $key = 'your-secret-key';

    public function generateToken($userId) {
        $payload = [
            'iss' => "http://your-domain.com",
            'iat' => time(),
            'exp' => time() + (60 * 60),
            'userId' => $userId,
        ];
        return JWT::encode($payload, $this->key, 'HS256');
    }
}
```

## 11. Service Discovery

### Configuration Management

```php
namespace App\Config;

class ServiceConfig {
    private $services;

    public function __construct() {
        $this->services = require __DIR__ . '/../../config/services.php';
    }

    public function getServiceUrl($serviceName) {
        return $this->services[$serviceName] ?? null;
    }
}
```

## 12. Load Balancing and Scaling

### Sample Nginx Load Balancer Configuration

```nginx
http {
    upstream user_service {
        server user-service1:80;
        server user-service2:80;
    }
    server {
        listen 80;
        location / {
            proxy_pass http://user_service;
        }
    }
}
```

## 13. Monitoring and Logging

### Centralized Logging using Monolog

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {
    private static $logger;

    public static function getLogger(): Logger {
        if (!self::$logger) {
            self::$logger = new Logger('app');
            self::$logger->pushHandler(new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG));
        }
        return self::$logger;
    }
}
```

## 14. Error Handling and Resilience

### Circuit Breaker Implementation

```php
class CircuitBreaker {
    private $failures = 0;
    private $failureThreshold = 3;
    private $lastFailureTime;
    private $retryTimeout = 60;

    public function call($function) {
        if ($this->isOpen()) {
            throw new Exception("Circuit is open");
        }
        try {
            $result = $function();
            $this->reset();
            return $result;
        } catch (Exception $e) {
            $this->recordFailure();
            throw $e;
        }
    }

    private function isOpen() {
        // Your logic based on failure counts and timeout
        return false;
    }

    private function recordFailure() {
        $this->failures++;
        $this->lastFailureTime = time();
    }

    private function reset() {
        $this->failures = 0;
        $this->lastFailureTime = null;
    }
}
```

## 15. Conclusion

This guide has demonstrated advanced implementation techniques for PHP microservices including:

- Modular, decoupled architecture
- Creation of a tiny, purpose-built PHP framework
- Advanced communication patterns: both synchronous and asynchronous
- Robust security and authentication practices
- Scalable load balancing configurations
- Implementing resilience with error handling and circuit breakers

For a complete look at the source code and practical examples, please visit the [GitHub repository](https://github.com/teal33t/php-microservice-demo).
