<?php

include "./framework/Bootstrap.php";
include "./application/Application.php";

$app = new Application(array(
	"config" => getConfig()
));
$app->execute();
