<?php
class Rest_Response {
    var $data;
    var $status;
    var $headers;

    /**
     * Constructor
     */
    function __construct() {
        $this->data = '';
        $this->status = 200;
        $this->headers = array();
    }

    /**
     * Launch the route
     * @return [type] [description]
     */
    function execute() {
        http_response_code($this->status);
        forEach($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->data;
    }

    /**
     * Set the status
     * @param  Integer $code Status code
     * @return Rest_Response This object
     */
    function status ($code) {
        $this->status = $code;
        return $this;
    }

    /**
     * Set header
     * @param  String $name  Field name
     * @param  String $value Field value
     * @return Rest_Response This object
     */
    function header($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set json body
     * @param  Mixed $data   Data to encode
     * @return Rest_Response This object
     */
    function json ($data) {
        $this->data = json_encode($data);
        return $this;
    }

}
