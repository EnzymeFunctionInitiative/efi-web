<?php
require_once(__DIR__."/../../../conf/settings_paths.inc.php");
require_once(__DIR__."/inc/stats_main.inc.php");
require_once(__CGFP_DIR__ . "/libs/functions.class.inc.php");
require_once(__CGFP_DIR__ . "/libs/cgfp_statistics.class.inc.php");
require_once(__BASE_DIR__ . "/libs/custom_graph.class.inc.php");

if (isset($_GET['year'])) {
    $year = $_GET['year'];
}
if (isset($_GET['month'])) {
    $month = $_GET['month'];
}

$user_id = 0;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

$graph_type = "";
if (isset($_GET['graph_type'])) {
    $graph_type = $_GET['graph_type'];
}

if ($graph_type == 'identify_daily_jobs') {
    $data = cgfp_statistics::get_identify_daily_jobs($db,$month,$year);
    $xaxis = "day";
    $yaxis = "count";
    $title = "Daily Identify Jobs - " . date("F", mktime(0, 0, 0, $month, 10)) . " - " . $year;
    custom_graph::bar_graph($data, $xaxis, $yaxis, $title);
}

if ($graph_type == 'quantify_daily_jobs') {
    $data = cgfp_statistics::get_quantify_daily_jobs($db,$month,$year);
    $xaxis = "day";
    $yaxis = "count";
    $title = "Daily Quantify Jobs - " . date("F", mktime(0, 0, 0, $month, 10)) . " - " . $year;
    custom_graph::bar_graph($data,$xaxis,$yaxis,$title);
}

?>
