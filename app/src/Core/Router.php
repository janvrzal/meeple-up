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
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('#\{[a-zA-Z_]}#', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->callHandler($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        echo '404 Not Found';
    }

    public function callHandler($handler, array $params = []) : void{
        if(is_array($handler)){
            [$class, $methodName] = $handler;
            (new $class()) -> $methodName(...$params);
        }
        else{
            $handler(...$params);
        }
    }
}