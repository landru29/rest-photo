<?php
class Thumb {
    var $files;
    var $options;
    var $dbConnexion;

    function __construct($options, $dbConnexion) {
        $this->files = array();
        $this->dirToArray($options["folder"]);
        $this->options = $options;
        $this->dbConnexion = $dbConnexion;
        $this->pictureBuilder();
    }

    function pictureBuilder() {
        $total = count($this->files);
        $i = 0;
        forEach($this->files as $file) {
            if (preg_match("#" . join($this->options["pics"], '$|') . "$#i", $file)) {
                $info = pathinfo($file);
                $thumbFilename = $info["dirname"] . DIRECTORY_SEPARATOR . $info["filename"] . "_thumb." . $info["extension"];
                $this->pictureToThumb($file, $thumbFilename);
                $this->updateDb($file, $thumbFilename);
            }
            $i++;
            $percent = floor(100 * ($i) / $total);
            if (floor(100 * ($i-1) / $total) != $percent) {
                echo "-------------------------------------- " . $percent . "%\n";
            }
        }
    }

    function cleanFilename ($file) {
        return preg_replace('#^/#', '', substr($file, strlen($this->options["folder"])));
    }

    function getType($filename) {
        $info = pathinfo($filename);
        $ext = $info['extension'];
        if (in_array($ext , $this->options['pics'])) {
            return "photo";
        }
        if (in_array($ext , $this->options['vids'])) {
            return "video";
        }
        return "undefined";
    }

    function updateDb($filename, $thumbname) {
        $filename = $this->cleanFilename($filename);
        $thumbname = $this->cleanFilename($thumbname);
        $info = pathinfo($filename);
        $title = $info['filename'];
        $folder = preg_replace('#^.$#', '', $info['dirname']);
        $basename = $info['basename'];
        $level = count(preg_split('@/@', $folder, NULL, PREG_SPLIT_NO_EMPTY));
        try {
            echo "   - Inserting (DB) " . $thumbname;
            $sql = 'SELECT *
                    FROM picture
                    WHERE filename = :filename';
            $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':filename' => $filename));
            $bindings = array(
                ':filename'  => $filename,
                ':basename'  => $basename,
                ':thumbname' => $thumbname,
                ':folder'    => $folder,
                ':level'     => $level,
                ':type'      => $this->getType($filename),
            );
            if (count($sth->fetchAll())) {
                echo "\t\033[34m(UPDATE)\033[0m";
                $sql = 'UPDATE picture
                SET thumb=:thumbname, folder=:folder, level=:level, basename=:basename, type=:type
                WHERE filename = :filename';
            } else {
                echo "\t\033[35m(CREATE)\033[0m";
                $sql = 'INSERT INTO picture (filename, rate, thumb, title, folder, level, basename, type)
                        VALUES (:filename, 0, :thumbname, :title, :folder, :level, :basename, :type)';
                $bindings['title'] = $title;
            }
            $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($bindings);
            echo "\t\t\033[32m[OK]\033[0m\n";
        } catch(Exception $e) {
            echo "\t\t\033[31m[ERROR]\033[0m\n";
            echo $e->getMessage()."\n";
            exit(1);
        }
    }

    function rotateThumb($filename, $img) {
        // get orientation
        try {
            $degrees=0;
            $flip=null;
            $exif = exif_read_data($filename);
            $orientation = array_key_exists('Orientation', $exif) ? $exif['Orientation'] : 1;

            switch ($orientation) {
                case 2:
                    $flip = IMG_FLIP_HORIZONTAL;
                    break;
                case 3:
                    $degrees = 180;
                    break;
                case 4:
                    $flip = IMG_FLIP_VERTICAL;
                    break;
                case 5:
                    $degrees = -90;
                    $flip = IMG_FLIP_HORIZONTAL;
                    break;
                case 6:
                    $degrees = -90;
                    break;
                case 7:
                    $degrees = -90;
                    $flip = IMG_FLIP_VERTICAL;
                    break;
                case 8:
                    $degrees = 90;
                    break;
                default:
                return $img;
            }

            // rotation
            $newImg = $degrees != 0 ? imagerotate($img, $degrees, 0) : $img;
            // Retournement
            if ($flip != null) {
                imageflip($newImg, $flip);
            }
            return $newImg;

        } catch (exception $e) {
            return $img;
        }
    }

    function pictureToThumb($filename, $thumbname) {
        try {
            echo "   - Creating (File) " . $thumbname;
            $img = imagecreatefromjpeg( $filename );

            // Rotate
            $img = $this->rotateThumb($filename, $img);

            $width = imagesx( $img );
            $height = imagesy( $img );

            // calculate thumbnail size
            if ($height < $width) {
                $new_width = $this->options["width"];
                $new_height = floor( $height * ( $new_width / $width ) );
            } else {
                $new_height = $this->options["width"];
                $new_width = floor( $width * ( $new_height / $height) );
            }

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
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
          if (!in_array($value,array(".",".."))) {
             if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                 $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
             }
             else if ( !preg_match('#_thumb\.[a-zA-Z0-9]*#', $value)) {
                $this->files[] = $dir . DIRECTORY_SEPARATOR . $value;
             }
          }
        }

        return $this->files;
    }
}
