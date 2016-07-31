<?php
class Rest_Request {

    var $query;
    var $body;
    var $path;
    var $method;
    var $params;
    var $headers;

    /**
     * Constructor
     */
    function __construct() {
        $this->path = array_key_exists("PATH_INFO", $_SERVER) ? $_SERVER["PATH_INFO"] : $_SERVER["SCRIPT_URL"];
        $this->baseUrl = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"];
        $this->query = $_GET;
        $this->method = strtoupper($_SERVER["REQUEST_METHOD"]);
        $this->body = file_get_contents("php://input");
        $this->params = array();
        $this->headers = apache_request_headers();
    }


}
