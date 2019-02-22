<?php

include_once 'inc/stats_main.inc.php';


$NumWaitingJobs = efi_statistics::get_num_jobs($db, __NEW__);
$NumRunningJobs = efi_statistics::get_num_jobs($db, __RUNNING__);

include_once 'inc/stats_admin_header.inc.php';

$month = date('n');
if (isset($_POST['month'])) {
    $month = $_POST['month'];
}
$year = date('Y');
if (isset($_POST['year'])) {
    $year = $_POST['year'];
}
$job_type = "generate";
if ((isset($_POST['job-type']) && $_POST['job-type'] == "analysis") || (isset($_GET['job-type']) && $_GET['job-type'] == "analysis")) {
    $job_type = "analysis";
}

$graph_type = "${job_type}_daily_jobs";
$get_array  = array('graph_type' => $graph_type, 'month' => $month, 'year' => $year);
$graph_image = "<img src='stats_graph.php?" . http_build_query($get_array) . "' id='daily-graph'>";

$all_get_array  = array('graph_type' => 'generate_monthly');
$all_graph_image = "<img src='stats_graph.php?" . http_build_query($all_get_array) . "'>";

$recent_only = false;
if ($job_type == "generate")
    $jobs_per_month = efi_statistics::num_generate_per_month($db, $recent_only);
else
    $jobs_per_month = efi_statistics::num_analysis_per_month($db, $recent_only);
$jobs_per_month_html = "";
$recent_start = count($jobs_per_month) - 9;
$total_num_jobs = 0;
$total_time = 0;
for ($i = 0; $i < count($jobs_per_month); $i++) {
    $value = $jobs_per_month[$i];
    $class = "";
    if ($i < $recent_start)
        $class = "old-month";
    $jobs_per_month_html .= "<tr class='$class'>";
    $jobs_per_month_html .= "<td>" . $value['month'] . "</td>";
    $jobs_per_month_html .= "<td>" . $value['year'] . "</td>";
    $jobs_per_month_html .= "<td>" . $value['count'] . "</td>";

    if ($job_type == "generate") {
        $jobs_per_month_html .= "<td>" . $value['num_success_option_a'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_a'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_success_option_b'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_b'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_seq_option_b'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_success_option_c'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_c'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_success_option_c_id'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_c_id'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_success_option_d'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_d'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_success_option_color'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed_option_color'] . "</td>";
    } else {
        $jobs_per_month_html .= "<td>" . $value['num_success'] . "</td>";
        $jobs_per_month_html .= "<td>" . $value['num_failed'] . "</td>";
    }
    $month_time = format_time($value['total_time']);
    $jobs_per_month_html .= "<td>" . $month_time . "</td>";
    $jobs_per_month_html .= "</tr>\n";
    
    $total_time += $value['total_time'];
    $total_num_jobs += $value['count'];
}
$colspan = $job_type == "generate" ? 17 : 6;
$total_num_jobs = number_format($total_num_jobs);
$total_time = format_time($total_time);
$jobs_per_month_html .= "<tr class='old-month'><td colspan='$colspan'>Total Jobs: $total_num_jobs / Total Time: $total_time</td></tr>\n";


$month_html = "<select class='form-control month-sel' name='month'>";
for ($i = 1; $i <= 12; $i++) {
    if ($month == $i)
        $month_html .= "<option value='" . $i . "' selected>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    else
        $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
}
$month_html .= "</select>";

$year_html = "<select class='form-control year-sel' name='year'>";
for ($i = 2014; $i <= date('Y'); $i++) {
    if ($year = $i)
        $year_html .= "<option value='" . $i . "' selected>". $i . "</option>\n";
    else
        $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
}
$year_html .= "</select>";


$job_text = "Generate";
if ($job_type == "analysis")
    $job_text = "Analysis";

?>



<h3>EFI-EST <?php echo $job_text; ?> Statistics</h3>

<h4><?php echo $job_text; ?> Step</h4>
<button class="btn btn-primary" id="toggle-recent" type="button">Show All Months</button>
<table class='table table-condensed table-bordered span8'>
<?php if ($job_type == "generate") { ?>
<tr>
    <th>Month</th>
    <th>Year</th>
    <th>Total Jobs</th>
    <th>Successful Option A</th>
    <th>Failed Option A</th>    
    <th>Successful Option B</th>
    <th>Failed Option B</th>
    <th>Failed Option B (> <?php echo __MAX_SEQ__; ?> Sequences)</th>
    <th>Successful Option C</th>
    <th>Failed Option C</th>
    <th>Successful Option C+ID</th>
    <th>Failed Option C+ID</th>
    <th>Successful Option D</th>
    <th>Failed Option D</th>
    <th>Successful Color SSN</th>
    <th>Failed Color SSN</th>
    <th>Total Time</th>    
</tr>
<?php } else { ?>
<h4>Analysis Step</h4>
<tr>
	<th>Month</th>
	<th>Year</th>
	<th>Total Jobs</th>
	<th>Successful Jobs</th>
	<th>Failed Jobs</th>
	<th>Total Time</th>
</tr>
<?php } ?>
<?php echo $jobs_per_month_html; ?>
</table>

<form class='form-inline' method='post' action='report.php'>
<select name='report_type' class='form-control'>
    <option value='xls'>Excel 2003</option>
    <option value='xlsx'>Excel 2007</option>
    <option value='csv'>CSV</option>
</select>
<input class='btn btn-primary' type='submit' name='create_user_report' value='Download User List'>
</form>

<br>
<?php if ($job_type == "generate") { ?>
Number of <?php echo __NEW__ . " jobs: $NumWaitingJobs"; ?><br>
Number of <?php echo __RUNNING__ . " jobs: $NumRunningJobs"; ?><br>
<?php } ?>
<br>
<hr>

<form class='form-inline' method='post' action='report.php'>
<select name='report_type' class='form-control'>
    <option value='xls'>Excel 2003</option>
    <option value='xlsx'>Excel 2007</option>
    <option value='csv'>CSV</option>
</select> 
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit' name='create_job_report' value='Download Job List'>
</form>

<hr>

<?php include("stats_nav.php"); ?>

<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit' name='create_user_report' value='Get Daily Graph'>
</form>

<br>
<hr>
<?php echo $graph_image; ?>

<br>
<hr>
<?php echo $all_graph_image; ?>

<script type="text/javascript" src="stats_nav.js"></script>
<script>
$(document).ready(function() {
    var graphApp = "stats_graph.php";
    var graphType = "<?php echo $graph_type; ?>";
    setMonth(<?php echo $month; ?>);
    setYear(<?php echo $year; ?>);
    
    $("#prev-month").click(function() {
        decMonth();
        var url = graphApp + "?" + "graph_type=" + graphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-graph").attr("src", url);
    });
    $("#next-month").click(function() {
        incMonth();
        var url = graphApp + "?" + "graph_type=" + graphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-graph").attr("src", url);
    });

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
include_once '../includes/stats_footer.inc.php';

function format_time($t) {
    $t = round($t);
    $d = $t / 86400;
    $h = ($t % 86400) / 3600;
    $m = ($t / 60) % 60;
    $s = $t % 60;
    return sprintf("%dd&nbsp;%02d:%02d:%02d", $d, $h, $m, $s);
}

?>
