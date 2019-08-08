<?php

require_once("inc/stats_main.inc.php");
require_once("inc/stats_admin_header.inc.php");

$month = date("n");
if (isset($_POST["month"])) {
	$month = $_POST["month"];
}
$year = date("Y");
if (isset($_POST["year"])) {
	$year = $_POST["year"];
}

$get_array  = array("graph_type" => "daily", "month" => $month, "year" => $year);
$graph_image = "<img src='stats_graph.php?" . http_build_query($get_array) . "'>";

$all_get_array  = array("graph_type" => "monthly");
$all_graph_image = "<img src='stats_graph.php?" . http_build_query($all_get_array) . "'>";

$recent_only = true;
$jobs_per_month = statistics::num_per_month($db, $recent_only);
$jobs_per_month_html = "";
foreach ($jobs_per_month as $value) {
	$jobs_per_month_html .= "<tr><td>" . $value["month"] . "</td>";
	$jobs_per_month_html .= "<td>" . $value["year"] . "</td>";
	$jobs_per_month_html .= "<td>" . $value["count"] . "</td>";
	$jobs_per_month_html .= "</tr>";

}


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
?>

<br><br><br>
<h3>EFI-GNT Statistics</h3>

<h4>Statistics</h4>
<table class='table table-condensed table-bordered table-striped'>
<tr>
	<th>Month</th>
	<th>Year</th>
	<th>Total Jobs</th>
</tr>
<?php echo $jobs_per_month_html; ?>
</table>

<form class='form-inline' method='post' action='report.php'>
                <select name='report_type' class='form-control'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> <input class='btn btn-primary' type='submit'
                name='create_user_report' value='Download User List'>
<br>
<br>
<hr>
                <select name='report_type' class='form-control'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> 
	<?php echo $month_html; ?>
	<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='create_job_report' value='Download Job List'>

</form>
<br>
<hr>
<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
                <select name='report_type' class='form-control'>
                <option value='xls'>Excel 2003</option>
                <option value='xlsx'>Excel 2007</option>
                <option value='csv'>CSV</option>
        </select> 
        <?php echo $month_html; ?>
        <?php echo $year_html; ?>

<input class='btn btn-primary' type='submit'
                name='create_user_report' value='Get Daily Graph'>

<br>
<hr>
<?php echo $graph_image; ?>

<br>
<hr>
<?php echo $all_graph_image; ?>


<?php require_once("inc/stats_footer.inc.php"); ?>
