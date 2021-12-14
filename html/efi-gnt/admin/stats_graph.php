<?php
namespace efi\gnt;

require_once(__DIR__."/inc/stats_main.inc.php");

use \efi\custom_graph;
use \efi\gnt\statistics;


if (isset($_GET["year"])) {
    $year = $_GET["year"];
}
if (isset($_GET["month"])) {
    $month = $_GET["month"];

}

$user_id = 0;
if (isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) {
    $user_id = $_GET["user_id"];
}

$graph_type = "";
if (isset($_GET["graph_type"])) {
    $graph_type = $_GET["graph_type"];
}


//Jobs Per Month
if ($graph_type == "daily") {
    $data = statistics::get_daily_jobs_aggregated($db, $month, $year);
    $xaxis = "day";
    $yaxis = "count";
    $title = "Daily Jobs - " . date("F", mktime(0, 0, 0, $month, 10)) . " - " . $year;
    custom_graph::bar_graph($data, $xaxis, $yaxis, $title);

// Jobs by month
} elseif ($graph_type == "monthly") {
    $raw_data = statistics::num_per_month_aggregated($db);
    $data = array();
    foreach ($raw_data as $row) {
        $month = substr($row['month'], 0, 3);
        $year = $row['year'];
        $count = $row['total'];
        array_push($data, array("month" => "$month $year", "count" => $count));
    }
    $xaxis = "month";
    $yaxis = "count";
    $title = "Monthly Jobs";
    custom_graph::bar_graph($data, $xaxis, $yaxis, $title);
}


