<?php
class Route {
    var $method;
    var $path;
    var $callback;
    var $variables;

    function Route($method, $path, $callback) {
        $this->variables = array();
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->regexp = '#' . preg_replace_callback('/\{([a-zA-Z0-9_]*)\}/', function($matches) {
            $this->variables[] =  $matches[1];
            return '([^/]*)';
        }, $path) . '#';
        $this->callback = $callback;
    }
}
