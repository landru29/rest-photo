<?php

function appendIncludePath($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

function __autoload($class_name) {
	$filename = preg_replace('/_/', '/', $class_name);
    include $filename . '.php';
}

appendIncludePath(dirname(__FILE__));
