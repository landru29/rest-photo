<?php
class Rest_Link {
    var $links;
    var $baseUrl;

    function __construct($baseUrl) {
        $this->baseUrl = $baseUrl;
        $this->links = array();
    }

    function add($rel, $link, $data=null) {
        $completeLink = $link;
        if ($data) {
            $completeLink = preg_replace_callback(
                '#/:([^/}]*)|/{([^}]*)}#',
                function ($matches) {
                    $key = (count($matches) == 3) ? $matches[2] : $matches[1];
                    if (array_key_exists($key, $data)) {
                        $value = $data[$key];
                        unset($data[$key]);
                        return urlencode($value);
                    } else {
                        return "$$$$$$$$$";
                    }
                },
                $link
            );
            if (count($data)) {
                $completeLink.= '?' . join('&', array_map(function($value, $key) {
                    return urlencode($key) . '=' . urlencode($value);
                }, $data, array_keys($data)));
            }
        }
        $this->links[] = '<' . $this->baseUrl . '/' . $completeLink . '>; rel="' . $rel . '"';
    }

    function __toString() {
        return join(',', $this->links);
    }
}
