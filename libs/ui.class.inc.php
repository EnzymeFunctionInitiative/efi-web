<?php
require_once(__DIR__ . "/../init.php");


class ui {
    public static function make_upload_box($title, $file_id, $progress_bar_id, $progress_num_id, $other = "", $site_url_prefix = "", $default_file = "") {
        global $maxFileSize;
        if (!isset($maxFileSize))
            $maxFileSize = ini_get('post_max_size');

        if (!$default_file)
             $default_file = "Choose a file&hellip;";

        if (!$site_url_prefix)
             $site_url_prefix = \efi\global_settings::get_base_web_path();

        return <<<HTML
                <div>
                    <b>$title</b> <a class="question" title="Maximum size is $maxFileSize">?</a><br>
                    <input type='file' name='$file_id' id='$file_id' data-url='server/php/' class="input_file">
                    <label for="$file_id" class="file_upload"><img src="$site_url_prefix/images/upload.svg" /> <span>$default_file</span></label>
                    <progress id='$progress_bar_id' max='100' value='0'></progress> <span id="$progress_num_id"></span>
                </div>
                $other
HTML;
    }
}

?>

