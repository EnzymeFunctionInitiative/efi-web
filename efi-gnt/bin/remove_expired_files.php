<?php
chdir(dirname(__FILE__));
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__."/libs/data_retention.class.inc.php");

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

?>
