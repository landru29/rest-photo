<?php
class Middleware_Thumb {

    function __construct($config) {
        preg_match('#[a-zA-Z]*$#', get_class($this), $match);
        $className = $match[0];
        $this->options = $config;
    }

    function getFormats() {
        $keys = array_filter(
            array_keys($this->options['album']),
            function ($elt) {
                return preg_match('#^format_#', $elt);
            }
        );
        return array_map(
            function($key) {
                preg_match('#^format_(.*)#', $key, $match);
                return $match[1];
            },
            $keys
        );
    }

    function getBaseUrl($req) {
        return preg_replace(
            array(
                '#\[HOST\]#',
                '#\[PROTOCOL\]#',
            ),
            array(
                $req->host,
                $req->protocol,
            ),
            $this->options['album']['baseUrl']
        );
    }

    function middleware ($req, $res) {
        $req->thumb = array(
            'formats' => $this->getFormats(),
            'baseUrl' => $this->getBaseUrl($req)
        );
    }
}
