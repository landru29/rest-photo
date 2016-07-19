<?php
class Rest_Route {
    var $method;
    var $path;
    var $callback;
    var $variables;
    var $routes;

    function __construct($path) {
        $this->init($path);
    }

    function set($method, callable $callback) {
        $this->method = strtoupper($method);
        $this->callback = $callback;
        return $this;
    }

    function add($path) {
        $route = new Rest_Route($this->path . '/' . $path);
        $this->routes[] = $route;
        return $route;
    }

    function getRegexp () {
        $this->variables = array();
        return '#' . preg_replace_callback('/\{([a-zA-Z0-9_]*)\}/', function($matches) {
            $this->variables[] =  $matches[1];
            return '([^/]*)';
        }, $this->path) . '#';
    }

    function init($path) {
        $this->path = preg_replace('#//#', '/', $path);
        if (!preg_match('#^/#', $this->path)) {
            $this->path = '/' . $this->path;
        }
        $this->routes = array();
    }

    function execute($request, $response) {
        if (
            ($request->method == $this->method) &&
            (preg_match($this->getRegexp(), $request->path, $matches)) &&
            (is_callable($this->callback))
        ) {
            foreach ($this->variables as $index => $key) {
                $request->params[$key] = $matches[$index+1];
            }
            $callback = $this->callback;
            $callback($request, $response);
            return;
        }

        forEach($this->routes as $route) {
            $route->execute($request, $response);
        }
    }
}
