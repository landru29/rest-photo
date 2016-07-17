<?php
class Application {
    var $query;
    var $body;
    var $path;
    var $method;
    var $params;
    var $routes;

    function Application() {
        $this->routes = array();
        $this->path = $_SERVER["PATH_INFO"];
        $this->query = $_GET;
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $this->body = file_get_contents("php://input");
        $this->params = array();
    }

    function addRoute($method, $route, $callback) {
        $this->routes[] = new Route($method, $route, $callback);
    }

    function execute() {
        forEach($this->routes as $route) {
            if (($this->method == $route->method) && (preg_match($route->regexp, $this->path, $matches))) {
                foreach ($route->variables as $index => $key) {
                    $this->params[$key] = $matches[$index+1];
                }
                return; // $route->callback($request, $response);
            }
        }
    }
}
