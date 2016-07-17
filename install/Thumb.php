<?php
class Thumb {
    var $files;
    var $options;
    var $dbConnexion;

    function __construct($options, $dbConnexion) {
        $this->dirToArray($options["folder"]);
        $this->options = $options;
        $this->dbConnexion = $dbConnexion;
        $this->pictureBuilder();
    }

    function pictureBuilder() {
        forEach($this->files as $file) {
            if (preg_match("#" . join($this->options["pics"], '$|') . "$#i", $file)) {
                $info = pathinfo($file);
                $thumbFilename = $info["dirname"] . DIRECTORY_SEPARATOR . $info["filename"] . "_thumb." . $info["extension"];
                $this->pictureToThumb($file, $thumbFilename);
                $this->updateDb($file, $thumbFilename);
            }
        }
    }

    function updateDb($filename, $thumbname) {
        try {
            echo "   - Inserting " . $thumbname;
            $sql = 'SELECT *
                    FROM picture
                    WHERE filename = :filename';
            $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':filename' => $filename));
            if (count($sth->fetchAll())) {
                echo "\t\033[34m(UPDATE)\033[0m";
                $sql = 'UPDATE picture
                SET thumb=:thumbname
                WHERE filename = :filename';
            } else {
                echo "\t\033[35m(CREATE)\033[0m";
                $sql = 'INSERT INTO picture (filename, rate, thumb)
                        VALUES (:filename, 0, :thumbname)';
            }
            $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':filename' => $filename, ':thumbname' => $thumbname));
            echo "\t\033[32m[OK]\033[0m\n";
        } catch(Exception $e) {
            echo "\t\033[31m[ERROR]\033[0m\n";
            echo $e->getMessage()."\n";
            exit(1);
        }
    }

    function pictureToThumb($filename, $thumbname) {
        try {
            echo "   - Creating " . $thumbname;
            $img = imagecreatefromjpeg( $filename );
            $width = imagesx( $img );
            $height = imagesy( $img );

            // calculate thumbnail size
            $new_width = $this->options["width"];
            $new_height = floor( $height * ( $new_width / $width ) );

            // create a new temporary image
            $tmp_img = imagecreatetruecolor( $new_width, $new_height );

            // copy and resize old image into new image
            imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

            // save thumbnail into a file
            imagejpeg( $tmp_img, $thumbname );
            echo "\t\t\t\033[32m[OK]\033[0m\n";
        } catch(Exception $e) {
            echo "\t\t\t\033[31m[ERROR]\033[0m\n";
            echo $e->getMessage()."\n";
            exit(1);
        }
    }

    function dirToArray($dir) {
        $this->files = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
          if (!in_array($value,array(".",".."))) {
             if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                $this->files[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
             }
             else if ( !preg_match('#_thumb\.[a-zA-Z0-9]*#', $value)) {
                $this->files[] = $dir . DIRECTORY_SEPARATOR . $value;
             }
          }
        }

        return $this->files;
    }
}
