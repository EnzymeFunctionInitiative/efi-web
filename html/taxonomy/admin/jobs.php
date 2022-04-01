<?php
require_once(__DIR__."/../../../init.php");


use \efi\taxonomy\statistics;

require_once(__DIR__."/../../shared/est_taxonomy/admin/admin_header.inc.php");
require_once(__DIR__."/../../shared/est_taxonomy/admin/shared.inc.php");

$job_type = "taxonomy";
$results_page = "../stepc.php";


$month = isset($_GET['month']) ? $_GET['month'] : date('n');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$jobs = statistics::get_jobs($db, $month, $year);

$table_html = get_job_table_html($jobs, $results_page);


$headers = array();
show_job_table("Taxonomy", $table_html, $headers);

show_job_js($job_type);


require_once(__DIR__."/../../shared/est_taxonomy/admin/admin_footer.inc.php");


