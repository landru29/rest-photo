<?php

function __autoload($class_name) {
	$text = preg_replace('/_/', '/', $class_name);
    include $class_name . '.php';
}

$app = new Application();
$app->addRoute("GET", "/bob/kiki/{id}/{zozo}", null);
$app->execute();
	print_r($app->path);
	print_r($app->query);
	print_r($app->body);
	print_r($app->params);
