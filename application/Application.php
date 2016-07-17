<?php
appendIncludePath(dirname(__FILE__));

class Application {
    var $restApp;

    function Application($options) {
        $this->restApp = new Rest_Application();
        $this->init();
        $this->pdo = new Pdo_Connect($options["config"]["database"]);
    }

    function init () {
        $routeBobKiki = new Rest_Route("/bob/kiki/{id}/{zozo}");
        $this->restApp->route($routeBobKiki->set("GET", function ($req, $resp) {
        	$result = array( "aa" => 10 );
        	$resp->status(200)->json($result);
        }));
    }

    function execute () {
        $this->restApp->execute();
    }
}
