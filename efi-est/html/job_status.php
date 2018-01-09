<?php
include_once '../includes/main.inc.php';


// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
$debug = !isset($_SERVER["HTTP_HOST"]);
if ($debug) {
    $num_args = count($argv);
    $arg_string = "";
    for ($i = 1; $i < $num_args; $i++) {
        if ($i > 1)
            $arg_string .= "&";
        $arg_string .= $argv[$i];
    }
    parse_str($arg_string, $_GET);
}


$show_error = false;
$gen_id = "";
$ans_id = "";
$status = "";
$sql = "";
$init_url = "";
$ans_url = "";

if (!array_key_exists("id", $_GET) || !array_key_exists("key", $_GET)) {
    $show_error = true;
}
else {
    $gen_id = $_GET["id"];
    $key = $_GET["key"];
    if (array_key_exists("analysis_id", $_GET))
        $ans_id = $_GET["analysis_id"];

    $job_status = functions::get_job_status($db, $gen_id, $ans_id, $key);
    if (!$job_status) {
        $show_error = true;
    }
    else {
        $gen_status = $job_status["generate"];
        $ans_status = $job_status["analysis"];
        $sql = $job_status["sql"];
        if (!$gen_status) {
            $show_error = true;
        }
        else if ($gen_status == __FAILED__) {
            $status = "has failed";
        }
        else if ($gen_status == __NEW__) {
            $status = "is waiting on other jobs to finish";
        }
        else if ($gen_status == __RUNNING__) {
            $status = "is currently running the initial processing step";
        }
        else {
            if ($job_status["job_type"] == "COLORSSN") {
                $status = "has completed coloring the SSN";
                $init_url = "view_coloredssn.php?";
            }
            else {
                $status = "has completed the initial processing";
                $init_url = "stepc.php?";
                $ans_url = "";
                if ($ans_status) {
                    if ($ans_status == __FAILED__) {
                        $status .= " but the SSN failed to generate";
                    }
                    else if ($ans_status == __NEW__) {
                        $status .= " and is waiting on other jobs to generate the SSN";
                    }
                    else if ($ans_status == __RUNNING__) {
                        $status .= " and the SSN is being created";
                    }
                    else {
                        $status .= " and the SSN has been created";
                        $ans_url = "stepe.php?analysis_id=" . $ans_id . "&key=" . $key . "&id=" . $gen_id;
                    }
                }
            }

            $init_url .= "key=" . $key . "&id=" . $gen_id;
        }
    }
}

if ($debug) {
    print "Generate ID: $gen_id\n";
    print "Analysis ID: $ans_id\n";
    print "Status: $status\n";
    print "$sql\n";
    exit;
}


include_once 'inc/header.inc.php';


?>


<?php if ($show_error) { ?>
    <h2>Error: No Job Found</h2>

    <p>No valid job was found.</p>
<?php } else { ?>

    <h2>Job Status - <?php echo $gen_id; ?></h2>
	<p>&nbsp;</p>
    <p>Job #<?php echo $gen_id . " " . $status; ?>.</p>
<?php } ?>

    <p>&nbsp;</p>
<?php if ($init_url) { ?>
    <p>Access initial calculation results <a href="<?php echo $init_url; ?>">here</a>.</p>
<?php } ?>
    <p>&nbsp;</p>
<?php if ($ans_url) { ?>
    <p>Access analysis results <a href="<?php echo $ans_url; ?>">here</a>.</p>
<?php } ?>
	<p></p>
    <p>&nbsp;</p>


<?php include_once 'inc/footer.inc.php'; ?>
