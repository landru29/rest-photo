<?php
class Rest_Request {

    var $query;
    var $body;
    var $path;
    var $method;
    var $params;
    var $headers;

    function __construct() {
        $this->path = $_SERVER["PATH_INFO"];
        $this->query = $_GET;
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $this->body = file_get_contents("php://input");
        $this->params = array();
        $this->headers = apache_request_headers();
    }


}
