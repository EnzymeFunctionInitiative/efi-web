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
$generate_page = functions::get_web_root() . "/stepc.php";
$colorssn_page = functions::get_web_root() . "/view_coloredssn.php";
$jobs = efi_statistics::get_generate($db,$month,$year);

$generate_html = "";
foreach ($jobs as $job) {
    $id = $job['Generate ID'];
    $families = implode(", ", explode(",", $job['Families']));
    $get_array = array('id'=>$id,'key'=>$job['Key']);
    if ($job['Option Selected'] == "COLORSSN") {
        $url = $colorssn_page;
    } else {
        $url = $generate_page;
    }
	$url = $url . "?" . http_build_query($get_array);
	$generate_html .= "<tr>";
	if (time() < $job['Time Completed'] + __RETENTION_DAYS__) {
		$generate_html .= "<td>&nbsp</td>";
	}
	else {
		$generate_html .= "<td><a href='" . $url ."'><span class='glyphicon glyphicon-share'></span></a></td>";
	}
	$generate_html .= "<td>" . $job['Generate ID'] . "</td>\n";
	$generate_html .= "<td>" . $job['Email'] . "</td>\n";
	$generate_html .= "<td>" . $job['Option Selected'] . "</td>\n";
	if ($job['Option Selected'] == 'BLAST') { 
		$generate_html .= "<td><a href='../blast.php?blast=" . $job['Blast'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
		$generate_html .= "</td>\n";
	}
	elseif ($job['Option Selected'] == 'FASTA' or $job['Option Selected'] == 'FASTA_ID') {
        $generate_html .= "<td><a href='fasta.php?id=" . $job['Generate ID'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
        $generate_html .= "</td>\n";
	}
	elseif ($job['Option Selected'] == 'ACCESSION') {
        $generate_html .= "<td><a href='accession.php?id=" . $job['Generate ID'] . "' target='_blank' ><span class='glyphicon glyphicon-ok'></span></a>";
        $generate_html .= "</td>\n";
	}
	else {
		$generate_html .= "<td>&nbsp</td>\n";
	}
	$generate_html .= "<td>" . $families . "</td>\n";
    $generate_html .= "<td>" . $job['E-Value'] . "</td>\n";
	$generate_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Submitted']) . "</td>\n";
	$generate_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Started']) . "</td>\n";
	$generate_html .= "<td>" . str_replace(" ", "&nbsp;", $job['Time Completed'])  ."</td>\n";
    $generate_html .= "<td>" . $job['Status'] . "</td>\n";
    //$generate_html .= "<td><center><span style='font-size: 100%'><a href='#' onclick='restartJob($id)'>&#8635;</a></span></center></td>\n";
	$generate_html .= "</tr>";

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
<h3>EFI-EST Generate Jobs - <?php echo $monthName . " - " . $year; ?></h3>

<form class='form-inline' method='get' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit'
                name='get_jobs' value='Submit'>

</form>
<h4>Generate Step</h4>
<table class='table table-condensed table-bordered'>
<tr>
	<th>&nbsp</th>
	<th>EFI-EST ID</th>
	<th>Email</th>
	<th>Type</th>
	<th>Blast</th>
	<th>Family</th>
    <th>E-Value</th>
	<th>Time Submitted</th>
	<th>Time Started</th>
	<th>Time Finished</th>
    <th>Status</th>
<!--    <th>Restart</th>-->
</tr>
<?php echo $generate_html; ?>
</table>


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

</script>


<?php include_once '../includes/stats_footer.inc.php' ?>
