<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__CGFP_DIR__ . "/libs/settings.class.inc.php");

$ex_dir = settings::get_example_dir();
if (file_exists($ex_dir)) {
    $web_path = settings::get_example_web_path();
    $job_obj = new quantify_example($db, $ex_dir, $web_path);

    $table_format = "html";
    $id_query_string = "example=1";
    $identify_only_id_query_string = "example=1&identify=1";
    $id_tbl_val = "";
    $IsExample = true;
    
    // Vars needed by step_vars.inc.php
    require_once(__DIR__."/inc/stepe_vars.inc.php");
    
    require_once(__DIR__."/inc/stepe_body.inc.php");
}

?>


