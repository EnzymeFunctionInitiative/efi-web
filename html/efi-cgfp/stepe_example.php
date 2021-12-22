<?php 
require_once(__DIR__."/../../init.php");

use \efi\cgfp\settings;
use \efi\cgfp\quantify_example;


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


