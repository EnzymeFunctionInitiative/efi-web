<?php
require_once(__DIR__."/../../../init.php");

use \efi\cgfp\job_manager;
use \efi\cgfp\job_types;


require_once(__DIR__."/inc/header.inc.php");

$month = date('n');
if (isset($_GET['month'])) {
    $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
    $year = $_GET['year'];
}

if (isset($_GET['job-type']) && $_GET['job-type'] == "quantify") {
    $job_type = "quantify";
    $job_text = "Quantify";
    $results_page = "../stepe.php";
    $mgr_type = job_types::Quantify;
} else {
    $job_type = "identify";
    $job_text = "Identify";
    $results_page = "../stepc.php";
    $mgr_type = job_types::Identify;
}


$date_range = get_date_range($month, $year);
$job_mgr = job_manager::init_by_date_range($db, $mgr_type, $date_range);
$job_ids = $job_mgr->get_all_job_ids();

$job_html = "";
foreach ($job_ids as $job_id) {
    $job = $job_mgr->get_job_by_id($job_id);

    $get_array = array('key' => $job['key']);
    if ($job_type == "quantify") {
        $id_field = $job['identify_id'] . "-" . $job['id'];;
        $get_array['quantify-id'] = $job['id'];
        $get_array['id'] = $job['identify_id'];
    } else {
        $id_field = $job['id'];
        $get_array['id'] = $job['id'];
    }

    $url = $results_page . "?" . http_build_query($get_array);

	$job_html .= "<tr>";
	if (time() < $job['time_completed'] + __RETENTION_DAYS__)
		$job_html .= "<td>&nbsp</td>";
	else
        $job_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";

	$job_html .= "<td>" . $id_field . "</td>";
	$job_html .= "<td>" . $job['email'] . "</td>";
    $job_html .= "<td>" . $job['filename'] . "</td>";
    if ($job_type == "quantify") {
        $mg_ids = $job['metagenomes'];
        $mg_title = join(", ", $mg_ids);
        if (count($mg_ids) > 1)
            $mg_ids = array($mg_ids[0], "...");
        $job_name = implode(", ", $mg_ids);
        $job_html .= "    <td title='$mg_title'>$job_name</td>";
    }

	$job_html .= "<td>" . str_replace(" ", "&nbsp;", $job['time_created']) . "</td>";
	$job_html .= "<td>" . str_replace(" ", "&nbsp;", $job['time_started']) . "</td>";
    $job_html .= "<td>";
    if ($job['time_completed'] == __RUNNING__)
        $job_html .= '<span class="running">';
    elseif ($job['time_completed'] == __FAILED__)
        $job_html .= '<span class="failed">';
    elseif ($job['time_completed'] == __CANCELLED__)
        $job_html .= '<span class="cancelled">';
    else
        $job_html .= '<span class="completed">';
    $job_html .= str_replace(" ", "&nbsp;", $job['time_completed']) . "</span></td>";
	$job_html .= "</tr>\n";
}




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

$month_name = date("F", mktime(0, 0, 0, $month, 10));

?>

<br><br>
<h3>EFI/ShortBRED <?php echo $job_text; ?> Jobs - <?php echo $month_name . " - " . $year; ?></h3>

<!--
<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
-->

<?php include("stats_nav.php"); ?>

<table class='table table-condensed table-bordered table-striped'>
<tr>
	<th> </th>
    <th>ID_ID<?php if ($job_type == "quantify") echo " - Q_ID"; ?></th>
	<th>Email</th>
    <th>Filename</th>
<?php if ($job_type == "quantify") { ?>
    <th>Metagenomes</th>
<?php } ?>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
</tr>
<?php echo $job_html; ?>
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



<?php

function get_date_range($month, $year) {
    if (!is_numeric($month) || !is_numeric($year) || $month < 1 || $month > 12 || $year < 2000 || $year > 2200)
        return NULL;
    $start = mktime(0, 0, 0, $month, 1, $year);
    $end = mktime(0, 0, 0, $month+1, 1, $year);
    $start_str = date("Y-m-d H:i:s", $start);
    $end_str = date("Y-m-d H:i:s", $end);
    return array("start" => $start_str, "end" => $end_str);
}

require_once(__DIR__."/inc/footer.inc.php");

?>
