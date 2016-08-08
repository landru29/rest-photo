<?php
class Middleware_Cors {

    function __construct($config) {
        preg_match('#[a-zA-Z]*$#', get_class($this), $match);
        $className = $match[0];
        if (array_key_exists($className, $config)) {
            $conf = $config[$className];
        }
    }

    function middleware ($req, $res) {
        $origin = array_key_exists('HTTP_REFERER', $req->headers) ? $req->headers['HTTP_REFERER'] : '*';
        $origin = array_key_exists('HTTP_ORIGIN', $req->headers) ? $req->headers['HTTP_ORIGIN'] : $origin;
        header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE');
        header('Access-Control-Allow-Headers:' . join(',', array(
            'X-Requested-With',
            'Accept, Origin',
            'Referer, User-Agent',
            'Content-Type',
            'Authorization',
            'X-Mindflash-SessionID',
            'access-token',
            'refresh-token',
            'client-application',
        )));
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Expose-Headers: '. join(',', array(
            'Referer, User-Agent',
            'Content-Type',
            'access-token',
            'refresh-token',
            'link',
            'X-Total-Count',
        )));
    }
}
