<?php
class Middleware_Paginator {
    var $defaultLimit;

    function __construct($config) {
        preg_match('#[a-zA-Z]*$#', get_class($this), $match);
        $className = $match[0];
        if (array_key_exists($className, $config)) {
            $conf = $config[$className];
            if (array_key_exists('limit', $conf)) {
                $defaultLimit = $conf['limit'];
            }
        }
    }

    function middleware ($req, $res) {
        $page = array_key_exists('page', $req->query) ? $req->query['page'] : 1;
        $limit = array_key_exists('limit', $req->query) ? $req->query['limit'] : $defaultLimit;
        $limit = array_key_exists('perPage', $req->query) ? $req->query['perPage'] : $limit;
        $offset = array_key_exists('offset', $req->query) ? $req->query['offset'] : ($page - 1) * $limit;
        $req->pagination = array(
            'offset' => $offset,
            'limit' => $limit,
            'page' => floor($offset / $limit) + 1,
        );
    }
}
