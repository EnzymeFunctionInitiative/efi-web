<?php
chdir(dirname(__FILE__));
require_once("../includes/main.inc.php");
require_once("../../libs/data_retention.class.inc.php");

$sapi_type = php_sapi_name();
//If run from command line
if ($sapi_type != 'cli') {
    echo "Error: This script can only be run from the command line.\n";
}
else {

    $base_dir = functions::get_results_dir();

    $dr = new data_retention($db, $base_dir);
    $jobs = $dr->get_expired_jobs("generate", "EST");

    foreach ($jobs as $id => $dir_path) {
        if (file_exists($dir_path)) {
            print "Cleaning up $id since it's too old: $dir_path\n";
            global_functions::rrmdir($dir_path);
        }
    }
}

?>
