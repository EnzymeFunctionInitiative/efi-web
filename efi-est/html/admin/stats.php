<?php

include_once 'includes/stats_main.inc.php';
set_include_path(get_include_path() . ':../includes/PHPExcel/Classes');
set_include_path(get_include_path() . ':../includes/jpgraph-3.5.0b1/src');
include_once 'includes/stats_header.inc.php';

$month = date('n');
if (isset($_POST['month'])) {
	$month = $_POST['month'];
}
$year = date('Y');
if (isset($_POST['year'])) {
	$year = $_POST['year'];
}

$graph_type = "generate_daily_jobs";
$get_array  = array('graph_type'=>$graph_type,
                'month'=>$month,
                'year'=>$year);
$graph_image = "<img src='stats_graph.php?" . http_build_query($get_array) . "'>";


$get_analyse_array = array('graph_type'=>"analysis_daily_jobs",
                'month'=>$month,
                'year'=>$year);
$graph_analyze_image = "<img src='stats_graph.php?" . http_build_query($get_analyse_array) . "'>";

$generate_per_month = efi_statistics::num_generate_per_month($db);
$generate_per_month_html = "";
foreach ($generate_per_month as $value) {
	$generate_per_month_html .= "<tr><td>" . $value['month'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['year'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['count'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['num_success_option_a'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['num_failed_option_a'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['num_success_option_b'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['num_failed_option_b'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['num_failed_seq_option_b'] . "</td>";
	$generate_per_month_html .= "<td>" . $value['total_time'] . "</td>";
	$generate_per_month_html .= "</tr>";

}

$analysis_per_month = efi_statistics::num_analysis_per_month($db);
$analysis_per_month_html = "";
foreach ($analysis_per_month as $value) {
        $analysis_per_month_html .= "<tr><td>" . $value['month'] . "</td>";
        $analysis_per_month_html .= "<td>" . $value['year'] . "</td>";
        $analysis_per_month_html .= "<td>" . $value['count'] . "</td>";
        $analysis_per_month_html .= "<td>" . $value['num_success'] . "</td>";
        $analysis_per_month_html .= "<td>" . $value['num_failed'] . "</td>";
	$analysis_per_month_html .= "<td>" . $value['total_time'] . "</td>";
        $analysis_per_month_html .= "</tr>";

}

$month_html = "<select class='input' name='month'>";
for ($i=1;$i<=12;$i++) {
	if ($month == $i) {
		$month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
	}
	else {
		$month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
	}
}
$month_html .= "</select>";

$year_html = "<select class='input-small' name='year'>";
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



<h3>EFI-EST Statistics</h3>

<h4>Generate Step</h4>
<table class='table table-condensed table-bordered span8'>
<tr>
	<th>Month</th>
	<th>Year</th>
	<th>Total Jobs</th>
	<th>Successful Option A Jobs</th>
	<th>Failed Option A Jobs</th>	
	<th>Successful Option B Jobs</th>
	<th>Failed Option B Jobs</th>
	<th>Failed Option B Jobs (> <?php echo __MAX_SEQ__; ?> Sequences)</th>
	<th>Total Time</th>	
</tr>
<?php echo $generate_per_month_html; ?>
</table>

<table class='table table-condensed table-bordered span8'>
<h4>Analysis Step</h4>
<tr>
	<th>Month</th>
	<th>Year</th>
	<th>Total Jobs</th>
	<th>Successful Jobs</th>
	<th>Failed Jobs</th>
	<th>Total Time</th>
</tr>
<?php echo $analysis_per_month_html; ?>
</table>
<hr>
<h4>Running Total of Unique Users</h4>
<p>Number of Unique Users: <?php echo efi_statistics::num_unique_users($db); ?>
<hr>
<h4>Running Total of Jobs</h4>
<p>Generate Step: <?php echo efi_statistics::num_generate_jobs($db); ?>
<p>Analysis Step: <?php echo efi_statistics::num_analysis_jobs($db); ?>
<hr>
<h4>Daily Graph</h4>
<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
        <?php echo $month_html; ?>
        <?php echo $year_html; ?>

<input class='btn btn-primary' type='submit'
                name='create_user_report' value='Get Daily Graph'>

<br>
<hr>
<h4>Daily Generate Step Graph</h4>
<?php echo $graph_image; ?>
<hr>
<h4>Daily Analysis Step Graph</h4>

<?php echo $graph_analyze_image; ?>


<?php include_once 'includes/stats_footer.inc.php'; ?>
