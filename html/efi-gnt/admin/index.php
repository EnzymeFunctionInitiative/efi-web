<?php
require_once(__DIR__."/../../../init.php");

require_once(__DIR__."/inc/stats_main.inc.php");
require_once(__DIR__."/inc/stats_admin_header.inc.php");

use \efi\gnt\statistics;


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

$jobs_per_month = statistics::num_per_month_aggregated($db);
$recent_start = count($jobs_per_month) - 9;
$table_html = "";
for ($i = 0; $i < count($jobs_per_month); $i++) {
    $value = $jobs_per_month[$i];
    $class = "";
    if ($i < $recent_start)
        $class = "old-month";
    $table_html .= "<tr class=\"$class\">\n";
    $table_html .= "<td>" . $value["month"] . "</td>\n";
	$table_html .= "<td>" . $value["year"] . "</td>\n";
    $table_html .= "<td>" . $value["total"] . "</td>\n";
    $table_html .= "<td>" . $value["gnn"] . "</td>\n";
    $table_html .= "<td>" . $value["direct"] . "</td>\n";
    $table_html .= "<td>" . $value["blast"] . "</td>\n";
    $table_html .= "<td>" . $value["id_lookup"] . "</td>\n";
    $table_html .= "<td>" . $value["fasta"] . "</td>\n";
    $table_html .= "</tr>\n";
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
<button class="btn btn-primary" id="toggle-recent" type="button">Show All Months</button>
<table class='table table-condensed table-bordered table-striped'>
<tr>
	<th>Month</th>
	<th>Year</th>
    <th>Total Jobs</th>
    <th>GNN</th>
    <th>D/Saved</th>
    <th>D/BLAST</th>
    <th>D/ID Lookup</th>
    <th>D/FASTA</th>
</tr>
<?php echo $table_html; ?>
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

<script>
$(document).ready(function() {
    var oldVisible = false;
    $("#toggle-recent").click(function() {
        oldVisible = !oldVisible;
        if (oldVisible) {
            $(".old-month").show();
            $(this).text("Hide Older Months");
        } else {
            $(".old-month").hide();
            $(this).text("Show All Months");
        }
    });
    $(".old-month").hide();
});

</script>

<?php

require_once(__DIR__."/inc/stats_footer.inc.php");


