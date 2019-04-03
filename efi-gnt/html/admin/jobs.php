<?php

require_once("inc/stats_main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");
require_once("inc/stats_admin_header.inc.php");

if (isset($_GET['job-type']) && $_GET['job-type'] == "diagram") {
    $job_type = "diagram";
    $job_text = "GND";
} else {
    $job_type = "gnt";
    $job_text = "GNT";
}

$month = date('n');
if (isset($_GET['month'])) {
    $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
    $year = $_GET['year'];
}
$stepc_page = $job_type == "diagram" ? "../view_diagrams.php" : "../stepc.php";
$jobs = statistics::get_jobs($db, $month, $year, $job_type);

$id_field = "$job_text $ID";
$query_id_field = $job_type == "gnt" ? "id" : "direct-id";

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
    if ($job['Status'] != __FINISH__ || strpos($tco, "0000") !== FALSE)
        $tco = "<span title=\"$tco // " . $job['PBS Number'] . "\">" . $job['Status'] . "</span>";
    else
        $tco = str_replace(" ", "&nbsp;", global_functions::format_short_date($tco));
	$gnn_html .= "<td>" . $job['ID'] . "</td>\n";
    $gnn_html .= "<td>" . $job['Email'] . "</td>\n";
    $filename = "";
    $nb_size = "";
    $type_field = "";
    $params = global_functions::decode_object($job['params']);
    if ($job_type == "gnt") {
        $filename = $params["filename"];
        $nb_size = $params["neighborhood_size"];
        $type_field = $params["db_mod"];
        $cooc = $params["cooccurrence"];
    } else {
        $type_field = $job['type'];

        if (isset($params["neighborhood_size"]))
            $nb_size = $params["neighborhood_size"];
        $filename = $job['Title'];
    }
    $gnn_html .= "<td class='file_col'>" . $filename . "</td>\n";
    $gnn_html .= "<td>" . $nb_size . "</td>\n";
    if ($job_type == "gnt")
	    $gnn_html .= "<td>$cooc</td>";
    $gnn_html .= "<td>" . $type_field . "</td>\n";
	$gnn_html .= "<td>" . str_replace(" ", "&nbsp;", global_functions::format_short_date($job['Time Created'])) . "</td>\n";
	$gnn_html .= "<td>" . str_replace(" ", "&nbsp;", global_functions::format_short_date($job['Time Started'])) . "</td>\n";
	$gnn_html .= "<td class='" . strtolower($job['Status']) . "'>" . $tco . "</td>\n";
	$gnn_html .= "</tr>";
}

$cooc_field = $job_type == "diagram" ? "Diagram Type" : "Input Cooccurrence";



$month_html = "<select class='form-control' name='month'>";
for ($i = 1; $i <= 12; $i++) {
    if ($month == $i)
        $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    else
        $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
}
$month_html .= "</select>";

$year_html = "<select class='form-control' name='year'>";
for ($i = 2014; $i <= date('Y'); $i++) {
    if ($year == $i)
        $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>\n";
    else
        $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
}
$year_html .= "</select>";

$monthName = date("F", mktime(0, 0, 0, $month, 10));
?>

<br><br>
<h3><?php echo $job_text; ?> Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<?php include("stats_nav.php"); ?>

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
<?php if ($job_type == "gnt") echo "    <th>DB Mod</th>\n"; ?>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
</tr>
<?php echo $gnn_html; ?>
</table>


<script type="text/javascript" src="stats_nav.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    setMonth(<?php echo $month; ?>);
    setYear(<?php echo $year; ?>);
    var jobType = "<?php echo $job_type; ?>";
    
    $("#prev-month").click(function() {
        decMonth();
        window.location = "?job-type=" + jobType + "&month=" + getMonth() + "&year=" + getYear();
    });
    $("#next-month").click(function() {
        incMonth();
        window.location = "?job-type=" + jobType + "&month=" + getMonth() + "&year=" + getYear();
    });
});

</script>



<?php include_once '../inc/stats_footer.inc.php' ?>
