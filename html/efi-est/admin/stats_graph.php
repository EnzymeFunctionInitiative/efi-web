<?php
require_once(__DIR__."/../../../init.php");

use \efi\custom_graph;
use \efi\est\efi_statistics;


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


//Jobs Per Month
if ($graph_type == 'analysis_daily_jobs') {
    $data = efi_statistics::get_analysis_daily_jobs($db,$month,$year);
    $xaxis = "day";
    $yaxis = "count";
    $title = "Daily Jobs - " . date("F", mktime(0, 0, 0, $month, 10)) . " - " . $year;
    custom_graph::bar_graph($data,$xaxis,$yaxis,$title);
} elseif ($graph_type == 'generate_daily_jobs') {
    $data = efi_statistics::get_generate_daily_jobs($db,$month,$year);
    $xaxis = "day";
    $yaxis = "count";
    $title = "Daily Jobs - " . date("F", mktime(0, 0, 0, $month, 10)) . " - " . $year;
    custom_graph::bar_graph($data,$xaxis,$yaxis,$title);
} elseif ($graph_type == 'generate_monthly') {
    $raw_data = efi_statistics::num_generate_per_month($db);
    $data = array();
    foreach ($raw_data as $row) {
        $month = substr($row['month'], 0, 3);
        $year = $row['year'];
        $count = $row['count'];
        array_push($data, array("month" => "$month $year", "count" => $count));
    }
    $xaxis = "month";
    $yaxis = "count";
    $title = "Monthly Jobs";
    custom_graph::bar_graph($data,$xaxis,$yaxis,$title);
}


