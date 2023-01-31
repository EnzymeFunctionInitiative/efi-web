<?php
namespace efi;


class send_file {
    const SEND_FILE_TEXT = "text/plain";
    const SEND_FILE_TABLE = "application/octet-stream";
    const SEND_FILE_PNG = "image/png";
    const SEND_FILE_BINARY = "application/octet-stream";
    const SEND_FILE_ZIP = "application/zip";

    public static function send_headers($file_name, $file_size, $type = self::SEND_FILE_BINARY) {
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Description: File Transfer');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $file_name . '";');
        header('Content-Type: ' . $type);
        header('Content-Length: ' . $file_size);
    }

    // Typical use
    public static function send($file_path, $file_name, $type = self::SEND_FILE_BINARY) {
        $file_size = filesize($file_path);
        $handle = fopen($file_path, "rb");
        if (is_resource($handle)) {
            self::send_headers($file_name, $file_size, $type);
            $result = self::send_file_contents_handle($handle);
            fclose($handle);
            if ($result === false) {
                error_log("Something happened during an fread of $file_path");
                return false;
            }
            return true;
        } else {
            $error_text = " 500 Interal Server Error (serving file)";
            header($_SERVER["SERVER_PROTOCOL"] . $error_text, true, 500);
            return false;
        }
    }

    // Typical use
    public static function send_text($text_string, $file_name, $type = self::SEND_FILE_TABLE) {
        $file_size = strlen($text_string);
        self::send_headers($file_name, $file_size, $type);
        ob_clean();
        echo $text_string;
    }

    public static function send_file_contents_handle($handle) {
        $chunkSize = 1024 * 1024;
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            if ($buffer === false)
                return false;
            echo $buffer;
            ob_flush();
            flush();
        }
        return true;
    }
}

