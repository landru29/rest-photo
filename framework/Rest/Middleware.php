<?php
class Rest_Middleware {
    var $name;
    var $callback;
    var $request;
    var $response;

    function __construct($name, callable $callback, Rest_Request $request, Rest_Response $response) {
        $this->name = $name;
        $this->callback = $callback;
        $this->request = $request;
        $this->response = $response;
    }

    function execute() {
        $callback = $this->callback;
        $callback($this->request, $this->response);
    }
}
