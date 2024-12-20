<?php
chdir(dirname(__FILE__));

require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\data_retention;
use \efi\gnt\settings;


$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {
    $base_dir = settings::get_output_dir();

    $dr = new data_retention($db, $base_dir);
    $jobs = $dr->get_expired_jobs("gnn", "GNT");

    foreach ($jobs as $id => $dir_path) {
        if (file_exists($dir_path)) {
            print "Cleaning up $id since it's too old: $dir_path\n";
            global_functions::rrmdir($dir_path);
        }
    }
}

