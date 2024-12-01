<?php

class Router
{
    private $routes = []; // Array to store all routes

    // Add a new route
    public function add($route, $callback)
    {
        $this->routes[$route] = $callback;
    }

    // Dispatch the route
    public function dispatch($uri)
    {
        $uri = rtrim(parse_url($uri, PHP_URL_PATH), '/'); // Normalize the URL by removing trailing slashes
        $uri = $uri === '' ? '/' : $uri; // Default to root if the URI is empty

        if (isset($this->routes[$uri])) {
            call_user_func($this->routes[$uri]);
        } else {
            $this->handle404();
        }
    }

    // Handle 404 errors for undefined routes
    private function handle404()
    {
        http_response_code(404);
        echo "404 - Page not found!";
        exit;
    }
}
