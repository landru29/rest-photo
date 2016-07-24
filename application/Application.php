<?php
appendIncludePath(dirname(__FILE__));

class Application {
    var $restApp;
    var $controllers;
    var $controllerData;

    function Application($options) {
        $this->restApp = new Rest_Application();
        $this->init($options);
        $this->pdo = new Pdo_Connect($options["config"]["database"]);
        $this->controllers = array(
            'picture' => new Picture_Controller($options["config"], $this->pdo)
        );
        $this->buildRoutes($options["config"]['rest']);
    }

    function getRouteDesc($ctrlTarget, $methodRoute) {
        $result = array();
        $explosion = explode('/', $ctrlTarget);
        $result['ctrl'] = $explosion[0];
        $result['func'] = $explosion[1];
        if (preg_match('#([A-Za-z]*)/(.*)#', $methodRoute, $match)) {
            $result['method'] = strtoupper($match[1]);
            $result['route'] = $match[2];
        }
        return $result;
    }

    function setController(callable $controller) {
        $this->controllerData = $controller;
    }

    function controller($req) {
        $ctrl = $this->controllerData[0];
        $callb = $this->controllerData[1];
        return $ctrl->$callb($req);
    }

    function buildRoutes($desc) {
        $routes = array();
        forEach($desc as $ctrlTarget => $methodRoute) {
            $detail = $this->getRouteDesc($ctrlTarget, $methodRoute);
            if (!array_key_exists($detail['route'], $routes)) {
                $routes[$detail['route']] = array();
            }
            $routes[$detail['route']][] = $detail;
        }

        forEach($routes as $route => $details) {
            $router = new Rest_Route($route, $this);
            forEach($details as $detail) {
                $ctrl = $this->controllers[$detail['ctrl']];
                $func = $detail['func'];
                $this->setController(array($ctrl, $func));
                $this->restApp->route($router->set($detail['method'], function ($req, $resp) {
                    $result = $this->controller($req);
                    $code = 200;
                    $data = $result;
                    if (array_key_exists('code', $result)) {
                        $code = $result['code'];
                        $data = $result['data'];
                    }
                    $resp->status($code)->json($data);
                }));
            }
        }
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
