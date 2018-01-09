<?php

include_once '../includes/stats_main.inc.php';
include_once '../includes/stats_admin_header.inc.php';

$month = date('n');
if (isset($_GET['month'])) {
        $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
        $year = $_GET['year'];
}
$analysis_page = functions::get_web_root() . "/stepe.php";
$colorssn_page = functions::get_web_root() . "/view_coloredssn.php";
$jobs = efi_statistics::get_generate($db,$month,$year);


$analysis_jobs = efi_statistics::get_analysis($db,$month,$year);
$analysis_html = "";
foreach ($analysis_jobs as $job) {
    $get_array = array('id'=>$job['Generate ID'],'key'=>$job['Key'],'analysis_id'=>$job['Analysis ID']);
    if ($job['Option Selected'] == "COLORSSN") {
        $url = $colorssn_page;
    } else {
        $url = $analysis_page;
    }
    $url = $url . "?" . http_build_query($get_array);
    
	$analysis_html .= "<tr>";
	if (time() < $job['Time Completed'] + __RETENTION_DAYS__) {
		$analysis_html .= "<td>&nbsp</td>\n";
	}
	else {
		$analysis_html .= "<td><a href='" . $url . "'><span class='glyphicon glyphicon-share'></span></a></td>\n";
	}
	$analysis_html .= "<td>" . $job['Generate ID'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Analysis ID'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Email'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Minimum Length'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Maximum Length'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Alignment Score'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Name'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Time Submitted'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Time Started'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Time Completed'] . "</td>\n";
	$analysis_html .= "<td>" . $job['Status'] . "</td>\n";
	$analysis_html .= "</tr>";

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

$monthName = date("F", mktime(0, 0, 0, $month, 10));
?>
<h3>EFI-EST Analyse Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>

<hr>
<h4>Analysis Step</h4>
<table class='table table-condensed table-bordered span8'>
<tr>
	<th>&nbsp</th>
	<th>EFI-EST ID</th>
	<th>Analysis ID</th>
	<th>Email</th>
	<th>Min Length</th>
	<th>Max Length</th>
	<th>Alignment Score</th>
	<th>Network Name</th>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
	<th>Status</th>
</tr>
<?php echo $analysis_html; ?>
</table>




<?php include_once '../includes/stats_footer.inc.php' ?>
