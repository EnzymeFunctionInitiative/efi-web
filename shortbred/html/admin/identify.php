<?php

require_once("../../includes/stats_main.inc.php");
require_once("../../libs/job_manager.class.inc.php");

require_once("inc/header.inc.php");

$month = date('n');
if (isset($_GET['month'])) {
        $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
        $year = $_GET['year'];
}

$stepc_page = "../stepc.php";

$job_mgr = new job_manager($db, job_types::Identify);
$job_ids = $job_mgr->get_all_job_ids();

$job_html = "";
foreach ($job_ids as $job_id) {

    $job = $job_mgr->get_job_by_id($job_id);

	$get_array = array('id'=>$job['id'],'key'=>$job['key']);
	$url = $stepc_page . "?" . http_build_query($get_array);
	$job_html .= "<tr>";
	if (time() < $job['time_completed'] + __RETENTION_DAYS__) {
		$job_html .= "<td>&nbsp</td>";
	}
	else {
		$job_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";
	}
	$job_html .= "<td>" . $job['id'] . "</td>\n";
	$job_html .= "<td>" . $job['email'] . "</td>\n";
	$job_html .= "<td>" . $job['filename'] . "</td>\n";
	$job_html .= "<td>" . $job['time_created'] . "</td>\n";
	$job_html .= "<td>" . $job['time_started'] . "</td>\n";
	$job_html .= "<td>" . $job['time_completed'] . "</td>\n";
	$job_html .= "</tr>";
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

<br><br>
<h3>EFI/ShortBRED Identify Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<!--
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
-->

<table class='table table-condensed table-bordered table-striped'>
<tr>
	<th> </th>
	<th>ID_ID</th>
	<th>Email</th>
	<th>Filename</th>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
</tr>
<?php echo $job_html; ?>
</table>



<?php include_once 'inc/footer.inc.php' ?>