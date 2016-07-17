<?php
class Rest_Application {
    var $request;
    var $routes;
    var $middlewares;
    var $response;

    function Rest_Application() {
        $this->routes = array();
        $this->middlewares = array();
        $this->request = new Rest_Request();
        $this->response = new Rest_Response();
    }

    function route(Rest_Route $route) {
        $this->routes[] = $route;
    }

    function middleware($name, callable $callback) {
        $this->middlewares[] = new Rest_Middleware($name, $callback, $this->request, $this->response);
    }

    function execute() {
        forEach($this->middlewares as $middleware) {
            $middleware->execute();
        }
        forEach($this->routes as $route) {
            $route->execute($this->request, $this->response);
        }
        return $this->response->execute();
    }
}
