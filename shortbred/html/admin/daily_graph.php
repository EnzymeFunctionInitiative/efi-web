<?php

set_include_path(get_include_path() . ":" . __DIR__."/../../../libs/jpgraph-3.5.0b1/src");
if (file_exists("../../includes/stats_main.inc.php")) {
    require_once("../../includes/stats_main.inc.php");
}

require_once(__DIR__."/../../libs/cgfp_statistics.class.inc.php");
require_once(__DIR__."/../../../libs/custom_graph.class.inc.php");

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
