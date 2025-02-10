<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get(string $path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, $handler)
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, $handler)
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(string $method, string $path)
    {
        $handler = $this->routes[$method][$path] ?? null;
        
        if (!$handler) {
            header("HTTP/1.0 404 Not Found");
            echo json_encode(['error' => '404 Not Found']);
            exit;
        }

        if (is_callable($handler)) {
            call_user_func($handler);
        } elseif (is_string($handler)) {
            // Assume format 'Controller@method'
            list($controller, $method) = explode('@', $handler);
            $controller = "App\\Controller\\$controller";
            
            if (class_exists($controller)) {
                $obj = new $controller();
                if (method_exists($obj, $method)) {
                    call_user_func([$obj, $method]);
                } else {
                    header("HTTP/1.0 500 Internal Server Error");
                    echo json_encode(['error' => 'Method not found']);
                }
            } else {
                header("HTTP/1.0 500 Internal Server Error");
                echo json_encode(['error' => 'Controller not found']);
            }
        }
    }
}