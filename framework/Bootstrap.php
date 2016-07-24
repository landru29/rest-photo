<?php

/**
 * Path includer
 * @param  String $path Path to add in include path
 */
function appendIncludePath($path) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}

/**
 * Autoloader
 * @param  String $class_name Name of the requested class
 */
function __autoload($class_name) {
	$filename = preg_replace('/_/', '/', $class_name);
    include $filename . '.php';
}

appendIncludePath(dirname(__FILE__));

/**
 * Parse configuration path
 * @return Array Configuration
 */
function getConfig() {
    return parse_ini_file("config.ini", true);
}
