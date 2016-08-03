<?php
appendIncludePath(dirname(__FILE__));

class Application {
    var $restApp;
    var $controllers;
    var $db;

    /**
     * Constructor
     * @param Array $options Application options
     */
    function __construct ($options) {
        $this->restApp = new Rest_Application();
        $this->db = new Pdo_Connect($options["config"]["database"]);
        $this->controllers = array(
            'picture' => new Picture_Controller($options["config"], $this)
        );
        $this->buildRoutes($options['config']['rest']);
        $paginator = new Middleware_Paginator($options['config']);
        $thumb = new Middleware_Thumb($options['config']);
        $this->restApp->middleware("paginator", function($req, $res) use ($paginator) {
            $paginator->middleware($req, $res);
        });
        $this->restApp->middleware("thumb", function($req, $res) use ($thumb) {
            $thumb->middleware($req, $res);
        });
    }

    /**
     * Build a route descriptor from config entry
     * @param  String $ctrlTarget  Controller descriptor (controller/method)
     * @param  String $methodRoute Route descriptor (METHOD/route)
     * @return Array               Descriptor
     */
    function getRouteDesc ($ctrlTarget, $methodRoute) {
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

    /**
     * Build all routes
     * @param Array $desc Route descriptor
     */
    function buildRoutes ($desc) {
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
                $this->restApp->route(
                    $router->set(
                        $detail['method'],
                        function ($req, $resp) use ($ctrl, $func) {
                            $result = $ctrl->$func($req);
                            $code = 200;
                            $data = $result;
                            if (array_key_exists('code', $result)) {
                                $code = $result['code'];
                                $data = $result['data'];
                            }
                            if (array_key_exists('headers', $result)) {
                                foreach ($result['headers'] as $key => $value) {
                                    $resp->header($key, $value);
                                }
                            }
                            $resp->status($code)->json($data);
                        }
                    )
                );
            }
        }
    }

    /**
     * Launch the routers
     */
    function execute () {
        $this->restApp->execute();
    }
}
