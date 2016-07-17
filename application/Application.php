<?php
appendIncludePath(dirname(__FILE__));

class Application {
    var $restApp;

    function Application() {
        $this->restApp = new Rest_Application();
        $this->init();
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
