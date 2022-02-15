<?php
require_once(__DIR__."/../../../../init.php");

$month = isset($_POST['month']) ? $_POST['month'] : date('n');
$year = isset($_POST['year']) ? $_POST['year'] : date('Y');


function format_time($t) {
    $t = round($t);
    $d = $t / 86400;
    $h = ($t % 86400) / 3600;
    $m = ($t / 60) % 60;
    $s = $t % 60;
    return sprintf("%dd&nbsp;%02d:%02d:%02d", $d, $h, $m, $s);
}


function get_stats_table_html($jobs_per_month, $num_cols) {
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

        $jobs_per_month_html .= implode("", array_map(function($val) { return "<td>$val</td>"; }, $value['VALUES']));
        $month_time = format_time($value['total_time']);
        $jobs_per_month_html .= "<td>" . $month_time . "</td>";
        $jobs_per_month_html .= "</tr>\n";
        
        $total_time += $value['total_time'];
        $total_num_jobs += $value['count'];
    }
    $colspan = $num_cols;
    $total_num_jobs = number_format($total_num_jobs);
    $total_time = format_time($total_time);
    $jobs_per_month_html .= "<tr class='old-month'><td colspan='$colspan'>Total Jobs: $total_num_jobs / Total Time: $total_time</td></tr>\n";
    return $jobs_per_month_html;
}


function get_month_html() {
    $month = isset($_POST['month']) ? $_POST['month'] : date('n');
    $month_html = "<select class='form-control month-sel' name='month'>";
    for ($i = 1; $i <= 12; $i++) {
        if ($month == $i)
            $month_html .= "<option value='" . $i . "' selected>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
        else
            $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    }
    $month_html .= "</select>";
    
    return $month_html;
}


function get_year_html() {
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
    $year_html = "<select class='form-control year-sel' name='year'>";
    for ($i = 2014; $i <= date('Y'); $i++) {
        if ($year = $i)
            $year_html .= "<option value='" . $i . "' selected>". $i . "</option>\n";
        else
            $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
    }
    $year_html .= "</select>";

    return $year_html;
}


function show_stats_table($job_stats_html, $cols) {
    $col_html = implode("", array_map(function($col) { return "<th>$col</th>"; }, $cols));
?>
<button class="btn btn-primary" id="toggle-recent" type="button">Show All Months</button>
<table class='table table-condensed table-bordered span8'>
<tr>
    <th>Month</th>
    <th>Year</th>
    <th>Total Jobs</th>
<?php echo $col_html; ?>
    <th>Total Time</th>    
</tr>
<?php echo $job_stats_html; ?>
</table>

<?php
}


function show_report_code($num_waiting = null, $num_running = null) {
    $month_html = get_month_html();
    $year_html = get_year_html();
?>

<form class='form-inline' method='post' action='report.php'>
<select name='report_type' class='form-control'>
    <option value='xls'>Excel 2003</option>
    <option value='xlsx'>Excel 2007</option>
    <option value='csv'>CSV</option>
</select>
<input class='btn btn-primary' type='submit' name='create_user_report' value='Download User List'>
</form>

<br>
<?php if (isset($num_waiting) && isset($num_running)) { ?>
Number of <?php echo __NEW__ . " jobs: $num_waiting"; ?><br>
Number of <?php echo __RUNNING__ . " jobs: $num_running"; ?><br>
<br>
<?php } ?>
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
<?php
}


function show_stats_nav() {
?>
<div style="margin-bottom: 20px">
<button type="button" id="prev-month" class="btn"><span class="glyphicon glyphicon-chevron-left"></span></button>
Month
<button type="button" id="next-month" class="btn"><span class="glyphicon glyphicon-chevron-right"></span></button>
</div>
<?php
}


function show_graphs($graph_type) {
    $month = isset($_POST['month']) ? $_POST['month'] : date('n');
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
    $month_html = get_month_html();
    $year_html = get_year_html();

    $graph_script = "../../shared/est_taxonomy/admin/stats_graph.php";

    $get_array  = array('graph_type' => "${graph_type}_daily_jobs", 'month' => $month, 'year' => $year);
    $graph_image = "<img src='$graph_script?" . http_build_query($get_array) . "' id='daily-graph'>";
    $all_get_array  = array('graph_type' => "${graph_type}_monthly");
    $all_graph_image = "<img src='$graph_script?" . http_build_query($all_get_array) . "'>";

?>
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

<?php
}


function show_nav_js($graph_type) {
    $month = isset($_POST['month']) ? $_POST['month'] : date('n');
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
    
    $graph_script = "../../shared/est_taxonomy/admin/stats_graph.php";

?>

<script type="text/javascript" src="../../shared/est_taxonomy/admin/stats_nav.js"></script>
<script>
$(document).ready(function() {
    var graphApp = "<?php echo $graph_script; ?>";
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
}


function show_job_js($job_type) {
    $month = isset($_POST['month']) ? $_POST['month'] : date('n');
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
?>
<script type="text/javascript">

function restartJob(jobId) {
    var r = confirm("Are you sure you want to restart job #" + jobId + "?");
    if (r != true) {
        return;
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            location.reload();
        } else if (this.status == 500) {
            alert("Restart failed!");
        }
    };
    xmlhttp.open("GET", "restart_job.php?job-id=" + jobId, true);
    xmlhttp.send();
}
function updateJob(action, jobType, jobId) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            $("#status-col-"+jobId).text("FINISH");
            $("#job-control-"+jobId).empty();
            //location.reload();
        } else if (this.status == 500) {
            alert("Restart failed! " + this.status + " " + jobId);
        }
    };
    xmlhttp.open("GET", "update_job_status.php?a=" + action + "&t=" + jobType + "&job-id=" + jobId, true);
    xmlhttp.send();
}

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
    $(".update-job-status").click(function() {
        var id = $(this).data("id");
        updateJob("unarchive", jobType, id);
    });
});

</script>
<?php
}


function get_job_table_html($jobs, $script_path) {

    $job_list_html = "";
    foreach ($jobs as $job) {
        $id = $job['Job ID'];
        
        $get_array = array('id' => $id, 'key' => $job['Key']);
        $url = $script_path . "?" . http_build_query($get_array);
    
        $job_list_html .= "<tr>";
        $job_list_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";
        $job_list_html .= "<td>$id</td>";
        $job_list_html .= "<td>" . $job['Email'] . "</td>";
    
        $sid = $id; // Status ID
        $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Submitted']) . "</td>";
        $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Started']) . "</td>";
        $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Completed'])  ."</td>";
        $job_list_html .= "<td class='" . strtolower($job['Status']) . "' id='status-col-$sid'>" . $job['Status'] . "</td>";
        $job_list_html .= "<td id='job-control-$sid'><center>" . ($job['Status'] == 'ARCHIVED' ? "<span style='font-size: 100%'><a class='update-job-status' data-id='$sid'>&#8683;</a></span>" : "") . "</center></td>";
        $job_list_html .= "</tr>\n";
    }

    return $job_list_html;
}


function show_job_table($title, $table_html, $headers) {
    $month = isset($_POST['month']) ? $_POST['month'] : date('n');
    $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

    $month_html = get_month_html();
    $year_html = get_year_html();
    $month_name = date("F", mktime(0, 0, 0, $month, 10));

    $col_html = implode("", array_map(function ($val) { return "<th>$val</th>"; }, $headers));
?>
<h3><?php echo $title; ?> Jobs - <?php echo $month_name . " - " . $year; ?></h3>
<?php

    show_stats_nav();

?>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
<h4><?php echo $title; ?> Step</h4>
<table class='table table-condensed table-bordered'>
<tr>
    <th>&nbsp;</th>
    <th>Job ID</th>
    <th>Email</th>
<?php echo $col_html; ?>
    <th>Time Submitted</th>
    <th>Time Started</th>
    <th>Time Finished</th>
    <th>Status</th>
    <th></th>
</tr>
<?php echo $table_html; ?>
</table>

<?php
}


