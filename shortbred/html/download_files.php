<?php

require_once("../includes/main.inc.php");
require_once("../libs/identify.class.inc.php");

$is_error = true;
$the_id = "";
$id_obj = NULL;

if (isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_GET["key"])) {
    $the_id = $_GET["id"];
    $id_obj = new identify($db, $the_id);

    if ($id_obj->get_key() != $_GET["key"]) {
        $is_error = true;
    } elseif (time() < $id_obj->get_time_completed() + settings::get_retention_days()) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}


if ($is_error) {
    error404();
}

if (isset($_GET["type"])) {

    $type = $_GET["type"];

    if ($type == "markers") {
        $marker_file = $id_obj->get_marker_file_path();
        if (file_exists($marker_file)) {
            $download_filename = pathinfo($id_obj->get_filename(), PATHINFO_FILENAME) . ".faa";
            $content_size = filesize($marker_file);
            sendHeaders($download_filename, $content_size);
            readfile($marker_file);
            exit(0);
        } else {
            $is_error = true;
        }
    } else {
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}





function sendHeaders($download_filename, $content_size) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $content_size);
    ob_clean();
}




?>
