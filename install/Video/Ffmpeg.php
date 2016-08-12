<?php

class Video_Ffmpeg {
    var $comm_installation_path;

    function __construct($comm_installation_path="/usr/bin/ffmpeg") {
        $this->comm_installation_path = $comm_installation_path;
    }

    function exists() {
        return file_exists($this->comm_installation_path);
    }

    function generate($video_file_path, $thumbnail_path, $thumbnail_size=array(), $seconds=1) {
        $seconds = $seconds ? $seconds : 1;
        $thumbWidth = array_key_exists('width', $thumbnail_size) ? $thumbnail_size['width'] : 150;
        $thumbHeight = array_key_exists('height', $thumbnail_size) ? $thumbnail_size['height'] : 150;
        $thumbSize       =  $thumbWidth . "x" . $thumbHeight;
        $cmd = "{$this->comm_installation_path} -i {$video_file_path} -deinterlace -an -ss {$seconds} -t 00:00:01  -s {$thumbSize} -r 1 -y -vcodec mjpeg -f mjpeg {$thumbnail_path} 2>&1";
        exec($cmd, $output, $retval);
        if ($retval) {
            throw new Exception("Error Generating video thumb with ffmpeg\n" . implode("\n", $output), 1);
        }
    }
}
