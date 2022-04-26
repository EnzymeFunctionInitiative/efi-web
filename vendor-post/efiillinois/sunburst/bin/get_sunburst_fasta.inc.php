<?php
$init_dir = __DIR__."/../../../../..";
require_once("$init_dir/init.php");


use \efi\global_functions;
use \efi\est\stepa;
use \efi\est\functions;



function get_job_key($db, $id) {
    $job = get_job($db, $id);
    return $job->get_key();
}


function get_job($db, $id) {
    return new stepa($db, $id);
}


function get_results_dir($db, $id) {
    $job = get_job($db, $id);
    $results_dir = functions::get_results_dir();
    return $results_dir . "/" . $job->get_output_dir();
}

function send_output($filename, $output) {
    $filesize = strlen($output);
    global_functions::send_headers($filename, $filesize);
    echo $output;
    ob_flush();
    flush();
}




