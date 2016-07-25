<?php
class Rest_Route {
    var $method;
    var $path;
    var $callback;
    var $variables;
    var $routes;

    /**
     * Constructor
     * @param String $path Route path
     */
    function __construct($path) {
        $this->path = preg_replace('#//#', '/', $path);
        if (!preg_match('#^/#', $this->path)) {
            $this->path = '/' . $this->path;
        }
        $this->routes = array();
    }

    /**
     * Set a callable
     * @param String   $method   HTTP method
     * @param callable $callback Function to invoke (req, res)
     */
    function set($method, $callback) {
        $this->method = strtoupper($method);
        $this->callback = $callback;
        return $this;
    }

    /**
     * Add a path
     * @param String $path Path
     * @return Rest_Route  New created route
     */
    function add($path) {
        $route = new Rest_Route($this->path . '/' . $path);
        $this->routes[] = $route;
        return $route;
    }

    /**
     * Build a regexp with this path
     * @return String Regular expression
     */
    function getRegexp () {
        $this->variables = array();
        return '#^' . preg_replace_callback('/\{([a-zA-Z0-9_]*)\}/', function($matches) {
            $this->variables[] =  $matches[1];
            return '([^/]*)';
        }, $this->path) . '$#';
    }

    /**
     * Try to execute the route (if request match)
     * @param  Rest_Request  $request  Request
     * @param  Rest_Response $response Response
     */
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
