<?php
namespace efi\est;

use \efi\send_file;

class download {

    public static function validate_image_type($type) {
        $valid_images = array(
            "nc" => true,
            "histogram" => true,
            "histogram_uniprot" => true,
            "histogram_uniref50" => true,
            "histogram_uniref90" => true,
            "histogram_uniref" => true,
            "histogram_uniprot_domain" => true,
            "histogram_uniref_domain" => true,
            "alignment" => true,
            "identity" => true,
            "edges" => true,
        );
        return isset($valid_images[$type]) ? $type : false;
    }

    public static function validate_file_type($type) {
        $valid_files = array(
            "nbconn" => true,
            "convratio" => true,
            "ssn" => true,
            "blasthits" => true,
            "stepc" => true,
            "colorssn" => true,
        );
        return isset($valid_files[$type]) ? $type : false;
    }

    function startsWith($haystack, $needle) {
         $length = strlen($needle);
         return substr($haystack, 0, $length) === $needle;
    }

    public static function validate_key($obj, $key) {
        if ($obj->get_key() !== $key) {
            return false;
        } else {
            return true;
        }
    }

    public static function output_file($file_path, $file_name, $mime_type) {
        if ($file_path && filesize($file_path)) {
            return send_file::send($file_path, $file_name, $mime_type);
        } else {
            return false;
        }
    }
}

