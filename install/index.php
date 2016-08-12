#!/usr/bin/php
<?php

include dirname(__FILE__) . "/../framework/Bootstrap.php";
include dirname(__FILE__) . "/Thumb.php";
include dirname(__FILE__) . "/Obsolete.php";

appendIncludePath(dirname(__FILE__));

function getFormats($options) {
    $keys = array_filter(
        array_keys($options),
        function ($elt) {
            return preg_match('#^format_#', $elt);
        }
    );
    return array_map(
        function($key) {
            preg_match('#^format_(.*)#', $key, $match);
            return "`" . $match[1] . "` varchar(300) NOT NULL, ";
        },
        $keys
    );
}

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
    $query = "CREATE TABLE IF NOT EXISTS `picture` ("
    . "  `filename` varchar(300) NOT NULL,"
    . "  `basename` varchar(300) NOT NULL,"
    . "  `rate` tinyint(3) unsigned NOT NULL,"
    . "  `level` tinyint(3) unsigned NOT NULL,"
    . "  `folder` varchar(300) NOT NULL,"
    . join(" ", getFormats($config["album"]))
    . "  `title` varchar(100) NOT NULL,"
    . "  `type` varchar(30) NOT NULL,"
    . "  `description` text NOT NULL,"
    . "  PRIMARY KEY (`filename`)"
    . ") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    $connexion->query($query);
    $query = "CREATE TABLE IF NOT EXISTS `errors` ("
    ."`filename` varchar(300) NOT NULL,"
    ."`stack` text NOT NULL"
    .") ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    $connexion->query($query);
    $connexion->query("TRUNCATE errors;");
    echo "\t\t\033[32m[OK]\033[0m\n";
} catch(Exception $e) {
    echo "\t\t\033[31m[ERROR]\033[0m\n";
    echo $e->getMessage()."\n";
    exit(1);
}

$obsolete = new Obsolete($config["album"], $connexion);

$thumb = new Thumb($config["album"], $connexion);
