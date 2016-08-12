<?php
class Thumb {
    var $files;
    var $options;
    var $dbConnexion;

    function __construct($options, $dbConnexion) {
        $this->files = array();
        $this->options = $options;
        $this->dirToArray($this->getSource());
        $this->dbConnexion = $dbConnexion;
        $formats = $this->getFormats();
        foreach ($formats as $format) {
            $this->pictureBuilder($format['name'], $format['size']);
        }
    }

    /**
     * Get complete source filename
     * @param  string $filename
     * @return string
     */
    function getSource($filename = null) {
        return preg_replace('#/\.#', '', $this->options["cwd"] . DIRECTORY_SEPARATOR . $this->options["source"] .
        (!empty($filename) ?  DIRECTORY_SEPARATOR . $filename : ""));
    }

    /**
     * get complete build filename
     * @param  [string] $filename
     * @return [string]
     */
    function getBuild($filename = null) {
        return preg_replace('#/\.#', '', $this->options["cwd"] . DIRECTORY_SEPARATOR . $this->options["build"] .
        (!empty($filename) ?  DIRECTORY_SEPARATOR . $filename : ""));
    }

    /**
     * Get all thumb formats from config file
     * @return array
     */
    function getFormats() {
        $self = $this;
        $keys = array_filter(
            array_keys($this->options),
            function ($elt) {
                return preg_match('#^format_#', $elt);
            }
        );
        return array_map(
            function($key) use ($self) {
                preg_match('#^format_(.*)#', $key, $match);
                return array(
                    'name' => $match[1],
                    'size' => $self->options[$key],
                );
            },
            $keys
        );
    }

    function logError($filename, $stack) {
        $sql = 'INSERT INTO errors (filename, stack) VALUES (:filename, :stack)';
        $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute(
            array(
                'filename' => $filename,
                'stack'     => $stack
            )
        );
        echo "\t\t\t\033[31m[FATAL]\033[0m\n" . $filename . "\n" . $stack;
    }

    /**
     * Main thumb builder
     * @param  string $suffixe filename suffixe
     * @param  number $size    Size in px
     */
    function pictureBuilder($suffixe, $size) {
        $total = count($this->files);
        $i = 0;
        forEach($this->files as $file) {
            $regexp = array_map(
                function($elt) {
                    return '^[^@]*' . $elt;
                },
                array_merge(
                    $this->options['pics'],
                    $this->options['vids']
                )
            );
            $completeRegex = '#' . join($regexp, '$|') . '$#i';
            if (preg_match($completeRegex, $file)) {
                $info = pathinfo($file);
                $thumbFilename = preg_replace('#^\./#', '', $info['dirname'] . DIRECTORY_SEPARATOR . $info["filename"] . '@' . $suffixe . '.' . $info['extension']);
                try {
                    switch ($this->getType($file)) {
                        case 'photo':
                            $this->pictureToThumb($this->getSource($file), $this->getBuild($thumbFilename), $size);
                            $this->updateDb($file, $thumbFilename, $suffixe);
                            break;
                        case 'video':
                            $this->videoToThumb($this->getSource($file), $this->getBuild($thumbFilename), $size);
                            $this->updateDb($file, $thumbFilename, $suffixe);
                            break;
                        default:
                            echo "  - \033[90mUnknown format " . $file . "\033[0m\n";
                    }
                } catch (Exception $e) {
                    $this->logError($file, $e->getMessage());
                }
            } else {
                echo "  - \033[90mSkipping " . $file . "\033[0m\n";
            }
            $i++;
            $percent = floor(100 * ($i) / $total);
            if (floor(100 * ($i-1) / $total) != $percent) {
                echo "-------------------------------------- " . $percent . "%\n";
            }
        }
    }

    /**
     * Get file type (photo | video)
     * @param  string $filename
     * @return string
     */
    function getType($filename) {
        $info = pathinfo($filename);
        $ext = strtolower($info['extension']);
        if (in_array($ext , $this->options['pics'])) {
            return 'photo';
        }
        if (in_array($ext , $this->options['vids'])) {
            return 'video';
        }
        return 'undefined';
    }

    /**
     * [cleanFilename description]
     * @param  [type] $file [description]
     * @return [type]       [description]
     */
    function cleanFilename ($file) {
        return preg_replace('#^/|^\./#', '', $file);
    }

    /**
     * Update database
     * @param  string $filename  source filename
     * @param  string $thumbname generated pic filename
     * @param  string $suffixe   format
     */
    function updateDb($filename, $thumbname, $suffixe) {
        $filename = $this->cleanFilename($filename);
        $thumbname = $this->cleanFilename($thumbname);
        $info = pathinfo($filename);
        $title = $info['filename'];
        $folder = preg_replace('#^.$#', '', $info['dirname']);
        $basename = $info['basename'];
        $level = count(preg_split('@/@', $folder, NULL, PREG_SPLIT_NO_EMPTY));
        try {
            echo '   - Inserting (DB) ' . $thumbname;
            $sql = 'SELECT *
                    FROM picture
                    WHERE filename = :filename';
            $sth = $this->dbConnexion->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':filename' => $filename));
            $bindings = array(
                ':filename'  => $filename,
                ':basename'  => $basename,
                ':thumbname' => $thumbname,
                ':folder'    => '/' . $folder,
                ':level'     => $level,
                ':type'      => $this->getType($filename),
            );
            if (count($sth->fetchAll())) {
                echo "\t\033[34m(UPDATE)\033[0m";
                $sql = 'UPDATE picture
                SET ' . $suffixe . '=:thumbname, folder=:folder, level=:level, basename=:basename, type=:type
                WHERE filename = :filename';
            } else {
                echo "\t\033[35m(CREATE)\033[0m";
                $sql = 'INSERT INTO picture (filename, rate, ' . $suffixe . ', title, folder, level, basename, type)
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

    /**
     * Perform a hard rotation of the image, depending on exif
     * @param    string $filename Source filename
     * @param  resource $img      Image
     */
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

    /**
     * Generate the thumb from the picture
     * @param  string $filename  source filename
     * @param  string $thumbname build filename
     * @param  number $size      size in px
     */
    function pictureToThumb($filename, $thumbname, $size) {
        try {
            if ((!file_exists(dirname($thumbname))) && (!mkdir(dirname($thumbname), 0777, true))) {
                throw new Exception("Could not create folder " . dirname($thumbname) . " for file " . $thumbname, 1);
            }

            echo "   - Creating (File) " . $thumbname;
            $img = imagecreatefromjpeg( $filename );

            // Rotate
            $img = $this->rotateThumb($filename, $img);

            $width = imagesx( $img );
            $height = imagesy( $img );

            // calculate thumbnail size
            if ($height < $width) {
                $new_width = $size;
                $new_height = floor( $height * ( $new_width / $width ) );
            } else {
                $new_height = $size;
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

    /**
     * Generate the thumb from the video
     * @param  string $filename  source filename
     * @param  string $thumbname build filename
     * @param  number $size      size in px
     */
    function videoToThumb($filename, $thumbname, $size) {
        if ((!file_exists(dirname($thumbname))) && (!mkdir(dirname($thumbname), 0777, true))) {
            throw new Exception("Could not create folder " . dirname($thumbname) . " for file " . $thumbname, 1);
        }
        $transformer = new Video_Ffmpeg();
        if (!$transformer->exists()) {
            $transformer = new Video_AvConv();
        }
        $transformer->generate(
            $filename,
            $thumbname,
            array(
                'width'=> $size,
                'height' => $size,
            ),
            1
        );
        if (!$transformer) {
            echo "\t\t\t\033[31m[ERROR]\033[0m\nCould not find a video tool";
            return;
        }
    }

    /**
     * Scan a complete folder deeply to retrieve files in an array
     * @param  string $cwd    base folder
     * @param  string $subdir subdir to scan
     * @return array          list of files
     */
    function dirToArray($cwd, $subdir = "") {
        $dir = $cwd .(!empty($subdir) ? DIRECTORY_SEPARATOR . $subdir : "");
        $subdir = empty($subdir) ? "" : $subdir;
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
          if (!in_array($value,array(".",".."))) {
             if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                 $this->dirToArray($cwd, $subdir . DIRECTORY_SEPARATOR . $value);
             }
             else if ( !preg_match('#_thumb\.[a-zA-Z0-9]*#', $value)) {
                $this->files[] = preg_replace("#^/*#", "", $subdir . DIRECTORY_SEPARATOR . $value);
             }
          }
        }

        return $this->files;
    }
}
