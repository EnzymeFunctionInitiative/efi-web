<?php
header("Cache-Control: no-cache");
header("Location: index.php");
exit(0);

require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__EST_DIR__."/libs/user_jobs.class.inc.php");
require_once(__EST_DIR__."/libs/ui.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");


$jobs = array();

// Require a token/login because we can't create jobs from Step C unless we have a valid email
// address.
if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $show_family_names = true;
    $jobs = user_jobs::load_jobs_for_group($db, functions::get_precompute_group(), $show_family_names);
}


require_once(__DIR__."/inc/header.inc.php");

?>

<h2>Precomputed Jobs</h2>

<?php if (count($jobs) > 0) { ?>

<p>For the convenience of our users, a number of Pfam and InterPro families have been
precomputed for the initial step.
They are listed below, and can be used to generate SSNs without
having to wait for the initial computation to occur.

<div style="margin-top: 25px; margin-bottom: 50px">
<?php
outputJobList($jobs);
?>
</div>

<?php } else { ?>

<p>No precomputed jobs are available.</p>

<?php } ?>


<?php require_once(__DIR__."/'inc/footer.inc.php'); ");

<?php

function outputJobList($jobs) {
    echo <<<HTML
            <table class="pretty-nested">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Job Name</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

    $lastBgColor = "#eee";
    for ($i = 0; $i < count($jobs); $i++) {
        $key = $jobs[$i]["key"];
        $id = $jobs[$i]["id"];
        $name = $jobs[$i]["job_name"];
        $dateCompleted = $jobs[$i]["date_completed"];
        $isCompleted = $jobs[$i]["is_completed"];
    
        $idText = "";
        $linkStart = "";
        $linkEnd = "";
        $nameStyle = "";

        if ($jobs[$i]["is_analysis"]) {
            if ($isCompleted) {
                $analysisId = $jobs[$i]["analysis_id"];
                $linkStart = "<a href=\"stepe.php?id=$id&key=$key&analysis_id=$analysisId\">";
                $linkEnd = "</a>";
            }
            $nameStyle = "style=\"padding-left: 50px;\"";
            //$name = '<i class="fas fa-long-arrow-right" aria-hidden="true"></i> ' . $name;
            $name = '[Analysis] ' . $name;
        } else {
            if ($isCompleted) {
                $theScript = $jobs[$i]["is_colorssn"] ? "view_coloredssn.php" : "stepc.php";
                $linkStart = "<a href=\"$theScript?id=$id&key=$key\">";
                $linkEnd = "</a>";
            }
            $idText = "$linkStart${id}$linkEnd";
            if ($lastBgColor == "#fff")
                $lastBgColor = "#eee";
            else
                $lastBgColor = "#fff";
        }    
        
        echo <<<HTML
                    <tr style="background-color: $lastBgColor">
                        <td>$idText</td>
                        <td $nameStyle>$linkStart${name}$linkEnd</td>
                        <td>$dateCompleted</td>
                    </tr>
HTML;
    }

    echo <<<HTML
                </tbody>
            </table>
HTML;
}

?>

