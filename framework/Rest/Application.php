<?php
class Rest_Application {
    var $request;
    var $routes;
    var $middlewares;
    var $response;

    /**
     * Constructor
     */
    function __construct() {
        $this->routes = array();
        $this->middlewares = array();
        $this->request = new Rest_Request();
        $this->response = new Rest_Response();
    }

    /**
     * Add Route
     * @param  Rest_Route $route Route to add
     */
    function route(Rest_Route $route) {
        $this->routes[] = $route;
    }

    /**
     * Add a middleware
     * @param  String   $name     Middleware name
     * @param  callable $callback Callback to use ($req, $res)
     */
    function middleware($name, callable $callback) {
        $this->middlewares[] = new Rest_Middleware($name, $callback);
    }

    /**
     * Launch the middlewares and routes
     */
    function execute() {
        forEach($this->middlewares as $middleware) {
            $middleware->execute($this->request, $this->response);
        }
        forEach($this->routes as $route) {
            $route->execute($this->request, $this->response);
        }
        return $this->response->execute();
    }
}
