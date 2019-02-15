<?php

require_once("../../../libs/global_functions.class.inc.php");
require_once("../inc/stats_main.inc.php");
require_once("../inc/stats_admin_header.inc.php");

$is_diagram = isset($_GET["diagrams"]);
$job_type = $is_diagram ? "GND" : "GNT";

$month = date('n');
if (isset($_GET['month'])) {
        $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
        $year = $_GET['year'];
}
$stepc_page = $job_type == "GND" ? "../view_diagrams.php" : "../stepc.php";
$jobs = statistics::get_jobs($db, $month, $year, $job_type);

$id_field = "$job_type $ID";
$query_id_field = $job_type == "GNT" ? "id" : "direct-id";

$gnn_html = "";
foreach ($jobs as $job) {
	$get_array = array($query_id_field=>$job['ID'],'key'=>$job['Key']);
	$url = $stepc_page . "?" . http_build_query($get_array);
	$gnn_html .= "<tr>";
	if (time() < $job['Time Completed'] + __RETENTION_DAYS__) {
		$gnn_html .= "<td>&nbsp</td>";
	}
	else {
		$gnn_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";
    }
    $tco = $job['Time Completed'];
    if ($tco != __FINISH__ || strpos($tco, "0000") !== FALSE)
        $tco = "<span title=\"" . $job['PBS Number'] . "\">" . $job['Status'] . "</span>";
    else
        $tco = str_replace(" ", "&nbsp;", global_functions::format_short_date($tco));
	$gnn_html .= "<td>" . $job['ID'] . "</td>\n";
    $gnn_html .= "<td>" . $job['Email'] . "</td>\n";
    $filename = "";
    $nb_size = "";
    if ($job_type == "GNT") {
        $filename = $job['Filename'];
        $nb_size = $job['Neighborhood Size'];
    } else {
        $params = global_functions::decode_object($job['params']);
        $type = $job['type'];

        $gnn_html .= "<td>" . $type . "</td>\n";

        if (isset($params["neighborhood_size"]))
            $nb_size = $params["neighborhood_size"];
        $filename = $job['Title'];
    }
    $gnn_html .= "<td class='file_col'>" . $filename . "</td>\n";
    $gnn_html .= "<td>" . $nb_size . "</td>\n";
    if ($job_type == "GNT")
	    $gnn_html .= "<td>" . $job['Input Cooccurrance'] . "</td>\n";
	$gnn_html .= "<td>" . str_replace(" ", "&nbsp;", global_functions::format_short_date($job['Time Created'])) . "</td>\n";
	$gnn_html .= "<td>" . str_replace(" ", "&nbsp;", global_functions::format_short_date($job['Time Started'])) . "</td>\n";
	$gnn_html .= "<td>" . $tco . "</td>\n";
	$gnn_html .= "</tr>";

}

$cooc_field = $job_type == "GND" ? "Diagram Type" : "Input Cooccurrence";



$month_html = "<select class='form-control' name='month'>";
for ($i=1;$i<=12;$i++) {
        if ($month == $i) {
                $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
        }
        else {
                $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
        }
}
$month_html .= "</select>";

$year_html = "<select class='form-control' name='year'>";
for ($i=2014;$i<=date('Y');$i++) {
        if ($year = $i) {
                $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>\n";
        }
        else {
                $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
        }

}
$year_html .= "</select>";

$monthName = date("F", mktime(0, 0, 0, $month, 10));
?>
<h3>EFI-GNT Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
<h4>Jobs</h4>
<table class='table table-condensed table-bordered table-striped'>
<tr>
	<th>&nbsp</th>
    <th>EFI-<?php echo $id_field; ?> ID</th>
	<th>Email</th>
	<th>Filename</th>
	<th>Neighborhood Size</th>
    <th><?php echo $cooc_field; ?></th>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
</tr>
<?php echo $gnn_html; ?>
</table>



<?php include_once '../inc/stats_footer.inc.php' ?>
