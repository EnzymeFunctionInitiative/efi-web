<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\global_functions;

use \efi\est\stepa;
use \efi\est\functions;


$id = filter_input(INPUT_POST, "id", FILTER_SANITIZE_NUMBER_INT);
$key = filter_input(INPUT_POST, "key", FILTER_SANITIZE_STRING);
$node_name = filter_input(INPUT_POST, "o", FILTER_SANITIZE_STRING);

if (!$id || !$key) {
    echo "";
    exit(1);
}

$job = new stepa($db, $id, false);
if ($key != $job->get_key()) {
    echo "";
    exit(1);
}

$ids = $_POST["ids"];
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
$filesize = strlen($output);
global_functions::send_headers($filename, $filesize);
echo $output;
ob_flush();
flush();

