<?php

include_once '../includes/stats_main.inc.php';


$NumWaitingJobs = efi_statistics::get_num_jobs($db, __NEW__);
$NumRunningJobs = efi_statistics::get_num_jobs($db, __RUNNING__);

include_once '../includes/stats_admin_header.inc.php';

$month = date('n');
if (isset($_GET['month'])) {
        $month = $_GET['month'];
}
$year = date('Y');
if (isset($_GET['year'])) {
        $year = $_GET['year'];
}
$job_type = "generate";
if ((isset($_POST['job-type']) && $_POST['job-type'] == "analysis") || (isset($_GET['job-type']) && $_GET['job-type'] == "analysis")) {
    $job_type = "analysis";
}

$generate_page = functions::get_web_root() . "/stepc.php";
$analysis_page = functions::get_web_root() . "/stepe.php";
$colorssn_page = functions::get_web_root() . "/view_coloredssn.php";

if ($job_type == "generate") {
    $jobs = efi_statistics::get_generate($db,$month,$year);
    $job_text = "Generate";
} else {
    $jobs = efi_statistics::get_analysis($db,$month,$year);
    $job_text = "Analyse";
}

$job_list_html = "";
foreach ($jobs as $job) {
    $id = $job['Generate ID'];
    
    $get_array = array('id' => $id, 'key' => $job['Key']);
    if ($job_type == "analysis") {
        $url = $analysis_page;
        $get_array['analysis_id'] = $job['Analysis ID'];
    } else {
        if ($job['Option Selected'] == "COLORSSN") {
            $url = $colorssn_page;
        } else {
            $url = $generate_page;
        }
    }
    $url = $url . "?" . http_build_query($get_array);

    $job_list_html .= "<tr>";

    if (time() < $job['Time Completed'] + __RETENTION_DAYS__ || $job['Status'] != __FINISH__)
        $job_list_html .= "<td>&nbsp</td>";
    else
        $job_list_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";
    $job_list_html .= "<td>$id</td>";

    if ($job_type == "generate") {
        $job_list_html .= "<td>" . $job['Email'] . "</td>";
        $job_list_html .= "<td>" . $job['Option Selected'] . "</td>";
        if ($job['Option Selected'] == 'BLAST') { 
            $job_list_html .= "<td><a href='../blast.php?blast=" . $job['Blast'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
            $job_list_html .= "</td>";
        } elseif ($job['Option Selected'] == 'FASTA' or $job['Option Selected'] == 'FASTA_ID') {
            $job_list_html .= "<td><a href='fasta.php?id=" . $job['Generate ID'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
            $job_list_html .= "</td>";
        } elseif ($job['Option Selected'] == 'ACCESSION') {
            $job_list_html .= "<td><a href='accession.php?id=" . $job['Generate ID'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
            $job_list_html .= "</td>";
        } else {
            $job_list_html .= "<td>&nbsp</td>";
        }
        $families = implode(", ", explode(",", $job['Families']));
        $job_list_html .= "<td>" . $families . "</td>";
        $job_list_html .= "<td>" . $job['E-Value'] . "</td>";
        $job_list_html .= "<td>" . $job['UniRef'] . "</td>";
    } else {
	    $job_list_html .= "<td>" . $job['Analysis ID'] . "</td>\n";
	    $job_list_html .= "<td>" . $job['Email'] . "</td>\n";
	    $job_list_html .= "<td>" . $job['Minimum Length'] . "</td>\n";
	    $job_list_html .= "<td>" . $job['Maximum Length'] . "</td>\n";
	    $job_list_html .= "<td>" . $job['Alignment Score'] . "</td>\n";
	    $job_list_html .= "<td>" . $job['Name'] . "</td>\n";
    }
    $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Submitted']) . "</td>";
    $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Started']) . "</td>";
    $job_list_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Completed'])  ."</td>";
    $job_list_html .= "<td>" . $job['Status'] . "</td>";
    //$job_list_html .= "<td><center><span style='font-size: 100%'><a href='#' onclick='restartJob($id)'>&#8635;</a></span></center></td>";
    $job_list_html .= "</tr>\n";
}

$month_html = "<select class='form-control month-sel' name='month'>";
for ($i = 1; $i <= 12; $i++) {
    if ($month == $i)
        $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>";
    else
        $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>";
}
$month_html .= "</select>";

$year_html = "<select class='form-control year-sel' name='year'>";
for ($i = 2014; $i <= date('Y'); $i++) {
    if ($year == $i)
        $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>";
    else
        $year_html .= "<option value='" . $i . "'>". $i . "</option>";
}
$year_html .= "</select>";

$month_name = date("F", mktime(0, 0, 0, $month, 10));
?>
<h3>EFI-EST <?php echo $job_text; ?> Jobs - <?php echo $month_name . " - " . $year; ?></h3>

<?php include("stats_nav.php"); ?>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
<h4><?php echo $job_text; ?> Step</h4>
<table class='table table-condensed table-bordered'>
<tr>
    <th>&nbsp</th>
    <th>EFI-EST ID</th>
<?php if ($job_type == "generate") { ?>
    <th>Email</th>
    <th>Type</th>
    <th>Blast</th>
    <th>Family</th>
    <th>E-Value</th>
    <th>UniRef</th>
<?php } else { ?>
	<th>Analysis ID</th>
	<th>Email</th>
	<th>Min Length</th>
	<th>Max Length</th>
	<th>Alignment Score</th>
    <th>Network Name</th>
<?php } ?>
    <th>Time Submitted</th>
    <th>Time Started</th>
    <th>Time Finished</th>
    <th>Status</th>
<!--    <th>Restart</th>-->
</tr>
<?php echo $job_list_html; ?>
</table>


<script type="text/javascript" src="stats_nav.js"></script>
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


<?php include_once '../includes/stats_footer.inc.php' ?>
