<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\global_functions;

use \efi\est\stepa;
use \efi\est\functions;


$html5_download = false;
if ($_POST["type"] && $_POST["type"] == "html5") {
    $data = json_decode(file_get_contents("php://input"), true);
    $html5_download = true;
} else {
    $data = $_POST;
}

$id = filter_var($data["id"], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
$key = filter_var($data["key"], FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
$node_name = filter_var($data["o"], FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

if (!$id || !$key) {
    echo "";
    exit(1);
}

$job = new stepa($db, $id, false);
if ($key != $job->get_key()) {
    echo "";
    exit(1);
}

$ids = $data["ids"];
$ids = json_decode($ids);

if (!$ids) {
    echo "";
    exit(1);
}


$results_dir = functions::get_results_dir();
$blast_db = $results_dir . "/" . $job->get_output_dir() . "/database";

$blast_module = global_settings::get_blast_module();


$exec = "source /etc/profile\n";
$exec .= "module load $blast_module\n";


$output = "";

$num_ids = count($ids);
$batch_size = 1000;
for ($i = 0; $i < $num_ids; $i += $batch_size) {
    $max_idx = min($i + $batch_size, $num_ids);
    $id_list = "";
    for ($j = $i; $j < $max_idx; $j++) {
        if ($id_list)
            $id_list .= ",";
        $id_list .= $ids[$j];
    }
    $blast_exec  = $exec . "fastacmd -d $blast_db -s $id_list\n";

    $exit_status = 1;
    $output_array = array();
    $out = exec($blast_exec, $output_array, $exit_status);
    for ($o = 0; $o < count($output_array); $o++) {
        $line = $output_array[$o];
        if ($line[0] && $line[0] == ">") {
            $line = preg_replace('/^>([^\|]+)\|([^ ]+).*$/', '>$2', $line);
        }
        $output .= $line . "\n";
    }
}

$node_name = preg_replace("/[^A-Za-z0-9\-_]/", "", $node_name);
$filename = "${id}_$node_name.fasta";
if ($html5_download) {
    $response = array(
        "valid" => "true",
        "download" => array(
            "mimetype" => "application/octet-stream",
            "filename" => $filename,
            "data" => base64_encode($output)
        )
    );
    echo json_encode($response);
} else {
    $filesize = strlen($output);
    global_functions::send_headers($filename, $filesize);
    echo $output;
    ob_flush();
    flush();
}

