<?php
class Rest_Response {
    var $data;
    var $status;
    var $headers;

    function __construct() {
        $this->data = '';
        $this->status = 200;
        $this->headers = array();
    }

    function execute() {
        http_response_code($this->status);
        forEach($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->data;
    }

    function status ($code) {
        $this->status = $code;
        return $this;
    }

    function header($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    function json ($data) {
        $this->data = json_encode($data);
        return $this;
    }

}
