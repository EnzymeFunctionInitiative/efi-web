<?php

require_once(__DIR__."/../../init.php");
require_once(__DIR__."/../../conf/settings_utils.inc.php");
require_once(__DIR__."/../../libs/efi/send_file.class.inc.php");

use efi\send_file;

$temp_dir = __MSA_TEMP_DIR__;

$debug = 0;

$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_STRING);

$rescode = 0;

if ($id && !preg_match("/[ \r\n\/]/", $id)) {
    $id_dir = "$temp_dir/$id";
    if (!isset($_GET["dl"])) {
        if (file_exists("$id_dir/done")) {
            if (isset($_GET["sz"])) {
                $name = trim(file_get_contents("$id_dir/name"));
                $rescode = get_file_size("$id_dir/$name");
            } else {
                $rescode = 1;
            }
        }
    } else {
        $name = trim(file_get_contents("$id_dir/name"));
        $csv_path = "$id_dir/$name";
        send_file::send($csv_path, $name);
        exit(0);
        #rrmdir($temp_dir, 0);
    }
}

echo $rescode;

function get_file_size($file_path) {
    $bytes = filesize($file_path);
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0) $sz = 'KMGT';
    $decimals = 1;
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
}

