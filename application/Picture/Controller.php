<?php
class Picture_Controller {
    var $options;
    var $db;

    function __construct($options, $db) {
        $this->options = $options;
        $this->db = $db;
    }

    function get() {
        $query = $data['query'];
        if (!$query) {
            $sql = 'SELECT * FROM picture';
            $bindings = array();
        }
        if (array_key_exists('filename', $query)) {
            $sql = 'SELECT *
                    FROM picture
                    WHERE filename = :filename';
            $bindings = array(
                ':filename' => $query['filename']
            );
        }
        if (array_key_exists('folder', $query)) {
            $sql = 'SELECT *
                    FROM picture
                    WHERE folder = :folder';
            $bindings = array(
                ':folder' => $query['folder']
            );
        }
        $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute($bindings);
        return array(
            'data' => $sth->fetchAll(),
            'code' => 200
        );
    }
}