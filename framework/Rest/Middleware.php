<?php
class Rest_Middleware {
    var $name;
    var $callback;

    /**
     * Constructor
     * @param String        $name     Middleware name
     * @param callable      $callback Function to invoke (req, res)
     */
    function __construct($name, callable $callback) {
        $this->name = $name;
        $this->callback = $callback;
    }

    function execute($req, $res) {
        $callback = $this->callback;
        $callback($req, $res);
    }
}
