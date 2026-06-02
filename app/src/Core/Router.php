<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler) : void{
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable|array $handler) : void{
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri) : void{
        $handler = $this->routes[$method][$uri] ?? null;

        if($handler === null) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        if(is_array($handler)){
            [$class, $methodName] = $handler;
            $controller = new $class();
            $controller->$methodName();
        }
        else{
            $handler();
        }
    }
}