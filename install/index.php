#!/usr/bin/php
<?php

include dirname(__FILE__) . "/../framework/Bootstrap.php";
include dirname(__FILE__) . "/Thumb.php";
include dirname(__FILE__) . "/Obsolete.php";


try {
    echo "* Reading config";
    $config = getConfig();
    echo "\t\t\033[32m[OK]\033[0m\n";
} catch(Exception $e) {
    echo "\t\t\033[31m[ERROR]\033[0m\n";
    echo $e->getMessage()."\n";
    exit(1);
}


try {
    echo "* Connecting to Database";
    $connexion = new Pdo_Connect($config["database"]);
    echo "\t\033[32m[OK]\033[0m\n";
} catch(Exception $e) {
        echo "\t\033[31m[ERROR]\033[0m\n";
        echo $e->getMessage()."\n";
        exit(1);
}

try {
    echo "* Creating tables";
    $connexion->query("CREATE TABLE IF NOT EXISTS `picture` ("
    . "  `filename` varchar(300) NOT NULL,"
    . "  `rate` tinyint(3) unsigned NOT NULL,"
    . "  `thumb` varchar(300) NOT NULL,"
    . "  `name` varchar(100) NOT NULL,"
    . "  PRIMARY KEY (`filename`)"
    . ") ENGINE=InnoDB DEFAULT CHARSET=latin1;");
    echo "\t\t\033[32m[OK]\033[0m\n";
} catch(Exception $e) {
    echo "\t\t\033[31m[ERROR]\033[0m\n";
    echo $e->getMessage()."\n";
    exit(1);
}

$obsolete = new Obsolete($connexion);

$thumb = new Thumb($config["album"], $connexion);
