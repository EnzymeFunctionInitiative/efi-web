<?php
require_once("../includes/main.inc.php");
require_once("../libs/user_auth.class.inc.php");
require_once("../libs/global_functions.class.inc.php");
require_once("../includes/login_check.inc.php");

const GET_IDS_FROM_FILE = 1;
const GET_IDS_FROM_DB = 2;
const GET_IDS_FROM_ALL = 4;
const GET_IDS_IGNORE_PARENT = 8;
const LEVEL1 = 1; // EST jobs
const LEVEL2 = 2; // jobs that originated from an EST job (analysis or generate)
const LEVEL3 = 3; // jobs that are children of another job of the same type
const DEFAULT_DAYS = 49;

if (!$IsLoggedIn) {
    error500("You must be logged in.");
}

$hide_empty = isset($_GET["hide-empty"]) ? $_GET["hide-empty"] : true;
$show_extra = isset($_GET["show-extra"]) ? $_GET["show-extra"] : false;
$show_color_ssn = isset($_GET["show-colorssn"]) ? $_GET["show-colorssn"] : true;
$show_gnn = isset($_GET["show-gnn"]) ? $_GET["show-gnn"] : true;
$show_cgfp = isset($_GET["show-cgfp"]) ? $_GET["show-cgfp"] : true;
$num_days = isset($_GET["num-days"]) ? $_GET["num-days"] : DEFAULT_DAYS;
$recent_first = isset($_GET["recent-first"]) ? $_GET["recent-first"] : 1;

$user_email = $IsLoggedIn;
//$user_email = "rzallot@illinois.edu";
//$user_email = "j-gerlt@illinois.edu";
$sb_db = __EFI_SHORTBRED_DB_NAME__;
$est_db = __EFI_EST_DB_NAME__;
$gnt_db = __EFI_GNT_DB_NAME__;

$NoAdmin = true;
$HeaderAdditional = array("<link rel='stylesheet' type='text/css' href='css/tree.css' />");
require_once("inc/header.inc.php");

?>

<h2>Job Dashboard</h2>


<label><input type="checkbox" id="show-colorssn" <?php echo $show_color_ssn? "checked" : ""; ?> /> Color SSN</label>
<label><input type="checkbox" id="show-gnn" <?php echo $show_gnn ? "checked" : ""; ?> /> GNN</label>
<label><input type="checkbox" id="show-cgfp" <?php echo $show_cgfp ? "checked" : ""; ?> /> CGFP</label>
<br>
<label><input type="checkbox" id="hide-empty" <?php echo $hide_empty ? "checked" : ""; ?> /> Hide EST jobs that have no analyze step</label>
<label><input type="checkbox" id="recent-first" <?php echo $recent_first ? "checked" : ""; ?> /> Show recent jobs first</label>
<label><input type="checkbox" id="show-extra" <?php echo $show_extra ? "checked" : ""; ?> /> Show extra info</label>
<br>
<label>Show last <input type="text" id="num-days" style="width: 50px" value="<?php echo $num_days; ?>" /> days <button class="mini" id="submit-num-days">Apply</button></label>


<div style="margin-top: 20px"></div>

<?php

if ($num_days && is_numeric($num_days) && $num_days > 0 && $num_days < 200)
    $start_date = global_functions::get_prior_date($num_days);
else
    $start_date = user_auth::get_start_date_window();


$sb_jobs_file = array();
if ($show_cgfp) {
    $sb_sql = "SELECT * FROM $sb_db.identify WHERE identify_email = '$user_email' AND identify_time_completed >= '$start_date'";
    //$sb_sql = "SELECT identify_id, identify_key, identify_status, identify_params, quantify_id FROM $sb_db.identify LEFT JOIN $sb_db.quantify ON identify_id = quantify_identify_id WHERE identify_email = '$user_email' AND identify_time_completed >= '$start_date'";
    $results = $db->query($sb_sql);
    $sb_jobs_file = get_job_list($results, "identify", LEVEL2, GET_IDS_FROM_FILE|GET_IDS_IGNORE_PARENT);
    $sb_jobs_file = add_quantify_jobs($sb_jobs_file, $db, $sb_db);
    $sb_jobs_file_assn = array();
}


$gnt_sql = "SELECT * FROM $gnt_db.gnn WHERE gnn_email = '$user_email' AND gnn_time_completed >= '$start_date'";
$results = $db->query($gnt_sql);
$gnt_jobs_file = get_job_list($results, "gnn", LEVEL2, GET_IDS_FROM_FILE); // These link to generate results pages (stepc, the ID is a generate ID)
$gnt_jobs_db = get_job_list($results, "gnn", LEVEL2, GET_IDS_FROM_DB); // These link to analysis jobs (stepe, the ID is an analysis ID)
$gnt_child_jobs = get_job_list($results, "gnn", LEVEL3, GET_IDS_FROM_DB); // GNT jobs that are children of another GNT job
$gnt_jobs_file_assn = array();
$gnt_jobs_db_assn = array();
$gnt_child_jobs_assn = array();


// Find all EST IDs
$all_est_ids = array();
$est_sql = "SELECT generate_id FROM $est_db.generate WHERE generate_email = '$user_email'";
$results = $db->query($est_sql);
foreach ($results as $row) {
    $all_est_ids[$row["generate_id"]] = 1;
}


// Find all of the EST IDs that are used by these jobs and make sure we retrieve them in
// the color SSN and EST part (so that the date filtering doesn't remove recent GNT and CGFP
// jobs, even if the EST job is expired).
$extra_est_ids = array();
foreach ($gnt_jobs_db as $id => $job) {
    if (isset($all_est_ids[$id]))
        array_push($extra_est_ids, $id);
}
foreach ($gnt_jobs_file as $id => $job) {
    if (isset($all_est_ids[$id]))
        array_push($extra_est_ids, $id);
}
foreach ($sb_jobs_file as $id => $job) {
    if (isset($all_est_ids[$id]))
        array_push($extra_est_ids, $id);
}
$additional_ids_clause = "";
if (count($extra_est_ids))
    $additional_ids_clause = "OR generate_id IN (" . implode(",", $extra_est_ids) . ")";


$color_sql = "SELECT * FROM $est_db.generate WHERE generate_email = '$user_email' AND generate_type = 'COLORSSN' AND (generate_time_completed >= '$start_date' $additional_ids_clause)";
$results = $db->query($color_sql);
$color_jobs_file = get_job_list($results, "generate", LEVEL2, GET_IDS_FROM_FILE);
$color_jobs_db = get_job_list($results, "generate", LEVEL2, GET_IDS_FROM_DB);
$color_jobs_file_assn = array(); // Assigned color jobs
$color_jobs_db_assn = array(); // Assigned color jobs



//var_dump($results);
//die();

$est_sql = "SELECT generate_id, generate_key, generate_params, generate_type, generate_time_created, analysis_id, analysis_name FROM $est_db.generate LEFT JOIN $est_db.analysis ON generate.generate_id = analysis.analysis_generate_id WHERE generate_email = '$user_email' AND generate_type != 'COLORSSN' AND (generate_time_completed >= '$start_date' OR analysis_time_completed >= '$start_date' $additional_ids_clause) ORDER BY generate_id";
if ($recent_first)
    $est_sql .= " DESC";
$results = $db->query($est_sql);

$est_grouping = array();
$est_order = array();
foreach ($results as $row) {
    $gid = $row["generate_id"];
    if (!isset($est_grouping[$gid])) {
        array_push($est_order, $gid);
        $est_grouping[$gid] = array();
    }
    array_push($est_grouping[$gid], $row);
}

$topLevelClass = "top-level";
echo "<ul class='no-deco'>\n";
//echo "<ul class='tree'>\n";
foreach ($est_order as $gid) {
    $row = $est_grouping[$gid][0];
    $key = $row["generate_key"];
    $job_type = $row["generate_type"];
    $date = global_functions::format_short_date($row["generate_time_created"], true);

    $params = array();
    if (isset($row["generate_params"]))
        $params = global_functions::decode_object($row["generate_params"]);
    $job_name = isset($params["generate_job_name"]) ? $params["generate_job_name"] : "";
    $families = isset($params["generate_families"]) ? $params["generate_families"] : "";
    $families = implode(", ", explode(",", $families));
    $uniref = isset($params["generate_uniref"]) ? $params["generate_uniref"] : "";
    $uniref = $uniref ? "; UniRef$uniref" : "";
    
    if (!$job_name)
        $job_name = $job_type;
    if ($families)
        $job_name .= " [$families$uniref]";
    if ($show_extra)
        $job_name .= " (EST Job #$gid)";

    $level1_html = "";

    if (isset($gnt_jobs_file[$gid]) && $show_gnn) {
        $level1_html .= get_gnt_html($gnt_jobs_file[$gid], $gnt_child_jobs, $sb_jobs_file, "      ", LEVEL1, $gid);
        $gnt_jobs_file_assn[$gid] = 1;
    }

    if (isset($color_jobs_file[$gid]) && $show_color_ssn) {
        $level1_html .= get_colorssn_html($color_jobs_file[$gid], $sb_jobs_file, "       ", LEVEL1, $gid);
        $color_jobs_file_assn[$gid] = 1;
    }

    $level2_html = "";

    foreach ($est_grouping[$gid] as $row) {
        $aid = $row["analysis_id"];

        if (!$aid)
            continue;

        $ssn_name = $row["analysis_name"];
    
        $chtml = "";
        if (isset($color_jobs_db[$aid]) && $show_color_ssn) {
            $chtml .= get_colorssn_html($color_jobs_db[$aid], $sb_jobs_file, "          ", LEVEL2, $aid);
            $color_jobs_db_assn[$aid] = 1;
        }

        $ghtml = "";
        if (isset($gnt_jobs_db[$aid]) && $show_gnn) {
            $ghtml .= get_gnt_html($gnt_jobs_db[$aid], $gnt_child_jobs, $sb_jobs_file, "          ", LEVEL2, $aid);
            $gnt_jobs_db_assn[$aid] = 1;
        }

        $ssn_extra = $show_extra ? " (SSN Job #$aid)" : "";
        $level2_html .= "      <li class='$topLevelClass'><a href='efi-est/stepe.php?id=$gid&key=$key&analysis_id=$aid' class='hl-est' title='EST Job #$gid - SSN Creation Job'>$ssn_name$ssn_extra</a>";
        if ($chtml || $ghtml)
            $level2_html .= "\n        <ul class='tree'>\n$chtml$ghtml        </ul>\n";
        $level2_html .= "      </li>\n";
    }

    if (!$hide_empty || $level1_html || $level2_html) {
        echo "  <li class='$topLevelClass'><a href='efi-est/stepc.php?id=$gid&key=$key' class='hl-est' title='EST Job #$gid'>$job_name</a> <span class='date'>-- $date</span>";
        if ($level1_html || $level2_html)
            echo "\n    <ul class='tree'>\n$level1_html$level2_html    </ul>\n";
        echo "  </li>\n";
    }
}

echo "</ul>\n";

echo "\n\n<h3>Unassigned GNT Jobs</h3>\n";

$html = "";
foreach ($gnt_jobs_file as $id => $info) {
    if (!isset($gnt_jobs_file_assn[$id]))
        $html .= get_gnt_html($info, $gnt_child_jobs, array(), "  ", LEVEL2, $id);
}
foreach ($gnt_jobs_db as $id => $info) {
    if (!isset($gnt_jobs_db_assn[$id]))
        $html .= get_gnt_html($info, $gnt_child_jobs, array(), "  ", LEVEL2, $id);
}

if ($html) {
    echo "<ul>\n$html</ul>\n\n";
}


echo "\n\n<h3>Unassigned Color SSN Jobs</h3>\n";

$html = "";
foreach ($color_jobs_file as $id => $info) {
    if (!isset($color_jobs_file_assn[$id]))
        $html .= get_colorssn_html($info, array(), "  ", LEVEL2, $id);
}
foreach ($color_jobs_db as $id => $info) {
    if (!isset($color_jobs_db_assn[$id]))
        $html .= get_colorssn_html($info, array(), "  ", LEVEL2, $id);
}

if ($html) {
    echo "<ul>\n$html</ul>\n\n";
}


echo "\n\n<h3>Unassigned CGFP Jobs</h3>\n";

$html = "";
foreach ($sb_jobs_file as $id => $info) {
    if (!isset($sb_jobs_file_assn[$id]))
        $html .= get_cgfp_html($info, "  ", $id);
}

if ($html) {
    echo "<ul>\n$html</ul>\n\n";
}




?>


<script>
$(document).ready(function() {
    var redirectFilter = function() {
        var hideEmpty = $("#hide-empty").prop("checked") ? 1 : 0;
        var hideExtra = $("#show-extra").prop("checked") ? 1 : 0;
        var showColorSsn = $("#show-colorssn").prop("checked") ? 1 : 0;
        var showGnn = $("#show-gnn").prop("checked") ? 1 : 0;
        var showCgfp = $("#show-cgfp").prop("checked") ? 1 : 0;
        var recentFirst = $("#recent-first").prop("checked") ? 1 : 0;
        var numDays = $("#num-days").val();

        var qPath = "?" +
            "hide-empty=" + hideEmpty + "&" +
            "show-extra=" + hideExtra + "&" +
            "show-colorssn=" + showColorSsn + "&" +
            "show-gnn=" + showGnn + "&" +
            "show-cgfp=" + showCgfp + "&" +
            "recent-first=" + recentFirst + "&" +
            "num-days=" + numDays;
        window.location = qPath;
    };

    $("#hide-empty, #show-colorssn, #show-gnn, #show-cgfp, #recent-first, #show-extra").change(function (evt) { redirectFilter(); });
    $("#submit-num-days").click(function(evt) { redirectFilter(); });
    $("#num-days").on("keypress", function(e) { if (e.which == 13) redirectFilter(); });
}).tooltip();
</script>

<div style="margin-top: 100px"></div>
<?php require_once("inc/footer.inc.php"); ?>


<?php

// Returns a mapping of what we think is the "parent" ID to job info.  For example, if the
// query is for GNT jobs, the "parent" is the EST job that the SSN originated from.  Any
// jobs not having a "parent" are placed in the 0 index.  If $job_level == LEVEL1, then
// what is returned is a list of all of the jobs with the list index being the job ID.
function get_job_list($results, $table, $job_level = LEVEL1, $get_id_type = GET_IDS_FROM_ALL) {
    $jobs = array();
    if ($job_level != LEVEL1)
        $jobs[0] = array();

    foreach ($results as $row) {
        $id = $row["${table}_id"];
        $key = $row["${table}_key"];
        $date = global_functions::format_short_date($row["${table}_time_created"], true);
        $info = get_info($row, $table);
        if ($job_level == LEVEL1) {
            $jobs[$id] = array("id" => $id, "key" => $key, "file" => $info["file"], "date" => $date);
        } elseif ($job_level == LEVEL2) {
            $id_chain = get_id_chain($row, $table, $info);

            $main_id = 0;
            if (($get_id_type&GET_IDS_FROM_ALL || $get_id_type&GET_IDS_FROM_DB) && isset($info["source"]))
                $main_id = $info["source"];
            elseif (($get_id_type&GET_IDS_FROM_ALL || ($get_id_type&GET_IDS_FROM_FILE && !isset($info["source"]) &&
                    ($get_id_type&GET_IDS_IGNORE_PARENT || !isset($info["parent"])))) && count($id_chain) > 0)
                $main_id = $id_chain[0];

            if ($main_id) {
                if (!isset($jobs[$main_id]))
                    $jobs[$main_id] = array();
                array_push($jobs[$main_id], array("id" => $id, "key" => $key, "file" => $info["file"], "date" => $date));
            }
        } elseif ($job_level == LEVEL3) { // job is a child of another job of the same type
            if (isset($info["parent"])) {
                $main_id = $info["parent"];
                if ($main_id) {
                    if (!isset($jobs[$main_id]))
                        $jobs[$main_id] = array();
                    array_push($jobs[$main_id], array("id" => $id, "key" => $key, "file" => $info["file"], "date" => $date));
                }
            }
        }
    }

    return $jobs;
}


function get_id_chain($row, $table, $info) {
    $id_chain = array();
    if ($info["file"]) {
        $parts = explode("_", $info["file"]);
        if (count($parts) > 2 && is_numeric($parts[0])) { // && is_numeric($parts[1])) {
            foreach ($parts as $part) {
                if (!is_numeric($part))
                    break;
                array_push($id_chain, $part);
            }
        }
    }

    return $id_chain;
}


function get_info($row, $table) {
    $info = array();

    $params = array();
    if (isset($row["${table}_params"]))
        $params = global_functions::decode_object($row["${table}_params"]);

    $file = "";
    if (isset($params["${table}_filename"]))
        $file = $params["${table}_filename"];
    elseif (isset($row["${table}_filename"]))
        $file = $row["${table}_filename"];
    elseif (isset($params["${table}_fasta_file"]))
        $file = $params["${table}_fasta_file"];

    $est_source_id = 0;
    if (isset($row["${table}_est_source_id"]))
        $est_source_id = $row["${table}_est_source_id"];
    elseif (isset($params["${table}_color_ssn_source_id"]))
        $est_source_id = $params["${table}_color_ssn_source_id"];

    $gnt_source_id = 0;
    if (isset($row["${table}_gnt_source_id"]))
        $gnt_source_id = $row["${table}_gnt_source_id"];

    $parent_id = 0;
    if (isset($row["${table}_parent_id"]))
        $parent_id = $row["${table}_parent_id"];
    elseif (isset($params["${table}_parent_id"]))
        $parent_id = $params["${table}_parent_id"];

    $info["file"] = $file;
    if ($gnt_source_id)
        $info["source"] = $gnt_source_id;
    if ($est_source_id)
        $info["source"] = $est_source_id;
    if ($parent_id)
        $info["parent"] = $parent_id;

    return $info;
}


function add_quantify_jobs($sb_jobs_file, $db, $sb_db) {
    $ids = array_keys($sb_jobs_file);
    for ($j = 0; $j < count($ids); $j++) {
        $est_id = $ids[$j];
        $job_list = $sb_jobs_file[$est_id];
        for ($i = 0; $i < count($job_list); $i++) {
            $id_id = $sb_jobs_file[$est_id][$i]["id"];
    
            $q_sql = "SELECT * FROM $sb_db.quantify WHERE quantify_identify_id = $id_id";
            $results = $db->query($q_sql);
    
            $q_jobs = array();
            foreach ($results as $row) {
                $params = global_functions::decode_object($row["quantify_params"]);
                $mg_ids = substr($params["quantify_metagenome_ids"], 0, 60);
                $q_info = array("id" => $row["quantify_id"], "mgs" => $mg_ids);
                array_push($q_jobs, $q_info);
            }
            $sb_jobs_file[$est_id][$i]["quantify"] = $q_jobs;
        }
    }
    return $sb_jobs_file;
}


function get_gnt_html($gnt_jobs, $child_jobs, $sb_jobs, $indent = "        ", $level = LEVEL2, $parent_id = -1) {
    global $show_extra;

    $html = "";
    $class = $level == LEVEL1 ? "class='top-level'" : "";
    foreach ($gnt_jobs as $gnt_job) {
        $id = $gnt_job["id"];
        $key = $gnt_job["key"];
        $file = $gnt_job["file"];
        $date = (isset($child_jobs) && $child_jobs !== false) ? $gnt_job["date"] : "";
        $date_str = $date ? " <span class='date'>-- $date</span>" : "";
        
        $chtml = "";
        if (isset($child_jobs) && $child_jobs !== false && isset($child_jobs[$id]))
            $chtml = get_gnt_html($child_jobs[$id], false, $sb_jobs, "$indent    ", $level, $id);
        if ($sb_jobs !== false && isset($sb_jobs[$id]))
            $sb_html = get_cgfp_html($sb_jobs[$id], "$indent    ", $id);

        $parent_str = $parent_id >= 0 ? "; Parent=$parent_id" : "";
        $extra_info = $show_extra ? " (GNT Job #$id$parent_str)" : "";
        $html .= "$indent<li $class><a href='efi-gnt/stepc.php?id=$id&key=$key' class='hl-gnt' title='GNT Job #$id'>$file$extra_info</a> $date_str";
        $sb_html = "";
        if ($chtml || $sb_html)
            $html .= "\n$indent  <ul class='tree'>\n$chtml$sb_html$indent  </ul>\n";
        $html .= "$indent</li>\n";
    }
    return $html;
}


function get_colorssn_html($color_jobs, $sb_jobs, $indent = "        ", $level = LEVEL2, $parent_id = -1) {
    global $show_extra;

    $html = "";
    $class = $level == LEVEL1 ? "class='top-level'" : "";
    foreach ($color_jobs as $cjob) {
        $id = $cjob["id"];
        $key = $cjob["key"];
        $file = $cjob["file"];

        $sb_html = "";
        if (isset($sb_jobs[$id])) {
            $sb_html = get_cgfp_html($sb_jobs[$id], "$indent    ", $id);
        }

        $parent_str = $parent_id >= 0 ? "; Parent=$parent_id" : "";
        $extra_info = $show_extra ? " (Color SSN Job #$id$parent_str)" : "";
        $html .= "$indent<li $class><a href='efi-est/view_coloredssn.php?id=$id&key=$key' class='hl-est' title='Color SSN Job #$id'>$file$extra_info</a>";
        if ($sb_html)
            $html .= "$indent  <ul class='tree'>\n$sb_html$indent  </ul>\n";
        $html .= "$indent</li>\n";
    }
    return $html;
}


function get_cgfp_html($jobs, $indent, $parent_id = -1) {
    global $show_extra;

    $sb_html = "";
    foreach ($jobs as $sb_job) {
        $sb_id = $sb_job["id"];
        $sb_key = $sb_job["key"];
        $date = $sb_job["date"];
        $date_str = $date ? " <span class='date'>-- $date</span>" : "";

        $q_jobs = $sb_job["quantify"];
        $q_html = "";
        foreach ($q_jobs as $q_job) {
            $q_id = $q_job["id"];
            $mgs = $q_job["mgs"];
            $extra_info = $show_extra ? " (CGFP Quantify Job #$q_id; Parent=$sb_id)" : "";
            $q_html .= "$indent    <li><a href='efi-cgfp/stepe.php?id=$sb_id&key=$sb_key&quantify-id=$q_id' class='hl-cgfp' title='CGFP Quantify Job #$q_id'>$mgs [quantification]$extra_info</a>\n";
        }
        
        $parent_str = $parent_id >= 0 ? "; Parent=$parent_id" : "";
        $extra_info = $show_extra ? " (CGFP Job #$sb_id$parent_str)" : "";
        $sb_html .= "$indent<li><a href='efi-cgfp/stepc.php?id=$sb_id&key=$sb_key' class='hl-cgfp' title='CGFP Identify Job #$sb_id'>CGFP $sb_id [marker]$extra_info</a> $date_str";
        if ($q_html)
            $sb_html .= "$indent  <ul>\n$q_html$indent  </ul>\n";
        $sb_html .= "$indent</li>\n";
    }
    return $sb_html;
}



?>

