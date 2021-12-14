<?php
namespace efi;


const SEND_FILE_TEXT = "text/plain";
const SEND_FILE_TABLE = "application/octet-stream";
const SEND_FILE_PNG = "image/png";
const SEND_FILE_BINARY = "application/octet-stream";
const SEND_FILE_ZIP = "application/zip";

class send_file {
    public static function send_headers($file_name, $file_size, $type = SEND_FILE_BINARY) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $file_name . '";');
        header('Content-Type: ' . $type);
        header('Content-Length: ' . $file_size);
    }
    public static function send($file_path, $file_name, $type = SEND_FILE_BINARY) {
        $file_size = filesize($file_path);
        self::send_headers($file_name, $file_size, $type);
        self::send_file_contents($file_path);
    }
    public static function send_file_contents($file) {
        $handle = fopen($file, 'rb');
        self::send_file_contents_handle($handle);
        fclose($handle);
    }
    public static function send_file_contents_handle($handle) {
        $chunkSize = 1024 * 1024;
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
    }
}

