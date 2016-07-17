<?php
class Pdo_Connect extends PDO {
    var $connection;

    function __construct($config) {
        $chain = $config["type"] . ':host=' . $config["host"]  .
               ';port=' . $config["port"] .
               ';dbname=' . $config["name"];
        parent::__construct($chain, $config["username"], $config["password"]);
    }
}
