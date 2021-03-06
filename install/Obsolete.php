<?php
class Obsolete {
    var $dbConnexion;
    var $options;

    function __construct($options, $dbConnexion) {
        $this->dbConnexion = $dbConnexion;
        $this->options = $options;
        $toRemove = $this->getObsolete();
        $this->removeObsolete($toRemove);
    }

    function getObsolete () {
        $result = array();
        $sql = 'SELECT filename, thumb
                FROM picture';
        $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute();
        forEach($sth->fetchAll() as $data) {
            $filename = $this->options["cwd"] . DIRECTORY_SEPARATOR . $this->options["source"] . DIRECTORY_SEPARATOR . $data["filename"];
            if (!file_exists($filename)) {
                $result[] = $data["filename"];
                $thumb = $this->options["cwd"] . DIRECTORY_SEPARATOR .$this->options["build"] . DIRECTORY_SEPARATOR . $data["thumb"];
                if (file_exists($thumb)) {
                    unlink($thumb);
                }
            }
        }
        return $result;
    }

    function removeObsolete($list) {
        forEach($list as $filename) {
            try {
                echo "   - Cleaning " . $filename . "\t\t\033[33m(DELETE)\033[0m";
                $sql = 'DELETE FROM picture
                        WHERE filename = :filename';
                $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $sth->execute(array(":filename" => $filename));
                echo "\t\033[32m[OK]\033[0m\n";
            }  catch(Exception $e) {
                echo "\t[ERROR]\n";
                echo $e->getMessage()."\n";
                exit(1);
            }
        }

    }
}
