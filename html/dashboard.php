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

$num_default_days = global_settings::get_retention_days();

$hide_empty = isset($_GET["hide-empty"]) ? $_GET["hide-empty"] : true;
$num_days = isset($_GET["num-days"]) ? $_GET["num-days"] : $num_default_days;
$recent_first = isset($_GET["recent-first"]) ? $_GET["recent-first"] : 1;

$user_email = $IsLoggedIn;
$user_token = user_auth::get_user_token();
$user_groups = user_auth::get_user_groups($db, $user_token);

$sb_db = __EFI_SHORTBRED_DB_NAME__;
$est_db = __EFI_EST_DB_NAME__;
$gnt_db = __EFI_GNT_DB_NAME__;

$NoAdmin = true;
$HeaderAdditional = array("<link rel='stylesheet' type='text/css' href='css/tree.css' />");
require_once("inc/header.inc.php");

?>

<h2>Consolidated Job History</h2>

<p>This page provides an overview of the connections between EFI jobs. When it is not
possible to automatically link GNT and CGFP jobs to EST jobs then they are listed in the "Other Jobs"
sections at the bottom of the page.
</p>


Highlight
<label><input type="checkbox" id="show-colorssn" /> Color SSN</label>
<label><input type="checkbox" id="show-gnn" /> GNN</label>
<label><input type="checkbox" id="show-cgfp" /> CGFP</label>
<label><input type="checkbox" id="show-nuke" /> Everything</label>
<br>
<label><input type="checkbox" id="hide-empty" <?php echo $hide_empty ? "checked" : ""; ?> /> Hide EST jobs that have no analyze step</label>
<label><input type="checkbox" id="recent-first" <?php echo $recent_first ? "checked" : ""; ?> /> Show recent jobs first</label>
<label><input type="checkbox" id="show-extra" /> Show extra info</label>
<br>
<div style="margin-top: 10px">
<div style="float: right">
    <a href="#unassigned"><button class="mini"><i class="fas fa-angle-double-down"></i> Other Jobs</button></a>
    <a href="#training"><button class="mini"><i class="fas fa-angle-double-down"></i> Training Resources</button></a>
</div>
<div style="float: left">
    <label>Show last <input type="text" id="num-days" style="width: 50px" value="<?php echo $num_days; ?>" /> days <button class="mini" id="submit-num-days">Apply</button></label>
</div>
</div>
<div style="clear: both"></div>


<div style="margin-top: 20px"></div>

<h3>Jobs</h3>

<?php

if ($num_days && is_numeric($num_days) && $num_days > 0 && $num_days < 200)
    $start_date = global_functions::get_prior_date($num_days);
else
    $start_date = user_auth::get_start_date_window();


list($gnt_jobs_file, $gnt_jobs_db, $gnt_child_jobs,
    $color_jobs_file, $color_jobs_db, $sb_jobs_file,
    $gnt_jobs_file_assn, $gnt_jobs_db_assn, $gnt_jobs_child_assn, $color_jobs_file_assn, $color_jobs_db_assn) 
        = retrieve_and_display($start_date, $user_email, array());

echo <<<HTML
<a name="training"></a>
<h3>Training Resources</h3>

HTML;


retrieve_and_display("", "", $user_groups);

echo <<<HTML

<a name="unassigned"></a>
<h3>Other GNT Jobs</h3>

HTML;

function get_unassn_gnt($jobs_list, $child_jobs, $assn_list) {
    $ghtml = "";
    $child_assn = array();
    foreach ($jobs_list as $id => $info) {
        if (!isset($assn_list[$id])) {
            list($html, $assn) = get_gnt_html($info, $child_jobs, array(), "  ", LEVEL2, $id);
            $ghtml .= $html;
            foreach ($child_assn as $child_id => $junk) {
                $child_assn[$child_id] = 1;
            }
        }
    }
    return array($ghtml, $child_assn);
}

$ghtml = "";

list($html, $gnt_child_assn) = get_unassn_gnt($gnt_jobs_file, $gnt_child_jobs, $gnt_jobs_file_assn);
$ghtml .= $html;
foreach ($gnt_child_assn as $child_id => $junk) {
    $gnt_jobs_child_assn[$child_id] = 1;
}

list($html, $gnt_child_assn) = get_unassn_gnt($gnt_jobs_db, $gnt_child_jobs, $gnt_jobs_db_assn);
$ghtml .= $html;
foreach ($gnt_child_assn as $child_id => $junk) {
    $gnt_jobs_child_assn[$child_id] = 1;
}

list($html, $junk) = get_unassn_gnt($gnt_child_jobs, array(), $gnt_jobs_child_assn);
$ghtml .= $html;

if ($ghtml) {
    echo "<ul>\n$ghtml</ul>\n\n";
}


echo "\n\n<h3>Other Color SSN Jobs</h3>\n";

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


echo "\n\n<h3>Other CGFP Jobs</h3>\n";

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
        var recentFirst = $("#recent-first").prop("checked") ? 1 : 0;
        var numDays = $("#num-days").val();

        var qPath = "?" +
            "hide-empty=" + hideEmpty + "&" +
            "recent-first=" + recentFirst + "&" +
            "num-days=" + numDays;
        window.location = qPath;
    };

    //$("#hide-empty, #show-colorssn, #show-gnn, #show-cgfp, #recent-first, #show-extra").change(function (evt) { redirectFilter(); });
    $("#hide-empty, #recent-first").change(function (evt) { redirectFilter(); });
    $("#show-colorssn").change(function (evt) { $(".colorssn").toggleClass("bold"); });
    $("#show-gnn").change(function (evt) { $(".gnn").toggleClass("bold"); });
    $("#show-cgfp").change(function (evt) { $(".cgfp").toggleClass("bold"); });
    $("#show-nuke").change(function (evt) { $("a").toggleClass("bold"); });
    $("#show-extra").change(function (evt) { $(".extra").toggle(); });
    $("#submit-num-days").click(function(evt) { redirectFilter(); });
    $("#num-days").on("keypress", function(e) { if (e.which == 13) redirectFilter(); });
    
    if ($("#show-colorssn").prop("checked"))
        $(".colorssn").addClass("bold");
    if ($("#show-gnn").prop("checked"))
        $(".gnn").addClass("bold");
    if ($("#show-cgfp").prop("checked"))
        $(".cgfp").addClass("bold");
    if ($("#show-nuke").prop("checked"))
        $("a").addClass("bold");
    if ($("#show-extra").prop("checked"))
        $(".extra").show();
    else
        $(".extra").hide();
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
    if (isset($params["filename"]))
        $file = $params["filename"];
    elseif (isset($params["${table}_fasta_file"]))
        $file = $params["${table}_fasta_file"];
    elseif (isset($row["${table}_filename"]))
        $file = $row["${table}_filename"];

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
    $html = "";
    $class = $level == LEVEL1 ? "class='top-level'" : "";
    $child_jobs_assn = array();
    foreach ($gnt_jobs as $gnt_job) {
        $id = $gnt_job["id"];
        $key = $gnt_job["key"];
        $file = $gnt_job["file"];
        $date = (isset($child_jobs) && $child_jobs !== false) ? $gnt_job["date"] : "";
        $date_str = $date ? " <span class='date'>-- $date</span>" : "";
        
        $chtml = "";
        if (isset($child_jobs) && $child_jobs !== false && isset($child_jobs[$id])) {
            list($chtml, $junk) = get_gnt_html($child_jobs[$id], false, $sb_jobs, "$indent    ", $level, $id);
            $child_jobs_assn[$id] = 1;
        }
        if ($sb_jobs !== false && isset($sb_jobs[$id]))
            $sb_html = get_cgfp_html($sb_jobs[$id], "$indent    ", $id);

        $parent_str = $parent_id >= 0 ? "; Parent=$parent_id" : "";
        $extra_info = make_extra(" (GNT Job #$id$parent_str)");
        $html .= "$indent<li $class><a href='efi-gnt/stepc.php?id=$id&key=$key' class='hl-gnt gnn' title='GNT Job #$id'>$file$extra_info</a> $date_str";
        $sb_html = "";
        if ($chtml || $sb_html)
            $html .= "\n$indent  <ul class='tree'>\n$chtml$sb_html$indent  </ul>\n";
        $html .= "$indent</li>\n";
    }
    return array($html, $child_jobs_assn);
}


function get_colorssn_html($color_jobs, $sb_jobs, $indent = "        ", $level = LEVEL2, $parent_id = -1) {
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
        $extra_info = make_extra(" (Color SSN Job #$id$parent_str)");
        $html .= "$indent<li $class><a href='efi-est/view_coloredssn.php?id=$id&key=$key' class='hl-color colorssn' title='Color SSN Job #$id'>$file$extra_info</a>";
        if ($sb_html)
            $html .= "$indent  <ul class='tree'>\n$sb_html$indent  </ul>\n";
        $html .= "$indent</li>\n";
    }
    return $html;
}


function get_cgfp_html($jobs, $indent, $parent_id = -1) {
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
            $extra_info = make_extra(" (CGFP Quantify Job #$q_id; Parent=$sb_id)");
            $q_html .= "$indent    <li><a href='efi-cgfp/stepe.php?id=$sb_id&key=$sb_key&quantify-id=$q_id' class='hl-cgfp cgfp' title='CGFP Quantify Job #$q_id'>$mgs [quantification]$extra_info</a>\n";
        }
        
        $parent_str = $parent_id >= 0 ? "; Parent=$parent_id" : "";
        $extra_info = make_extra(" (CGFP Job #$sb_id$parent_str)");
        $sb_html .= "$indent<li><a href='efi-cgfp/stepc.php?id=$sb_id&key=$sb_key' class='hl-cgfp cgfp' title='CGFP Identify Job #$sb_id'>CGFP $sb_id [marker]$extra_info</a> $date_str";
        if ($q_html)
            $sb_html .= "$indent  <ul>\n$q_html$indent  </ul>\n";
        $sb_html .= "$indent</li>\n";
    }
    return $sb_html;
}


function make_extra($extra) {
    return "<span class='extra'>$extra</span>";
}


function get_group_select_statement($db, $table, $user_email, $group_clause, $time_completed, $job_type = "", $addl_or_cond = "", $addl_and_cond = "") {
    $sql = "SELECT $table.* FROM $db.$table ";
    if ($group_clause)
        $sql .= "LEFT OUTER JOIN $db.job_group ON $table.${table}_id = job_group.${table}_id WHERE $group_clause";
    else
        $sql .= "WHERE ${table}_email = '$user_email'";
    $sql .= " AND ${table}_status = 'FINISH'";
    if ($time_completed)
        $sql .= " AND ${table}_time_completed >= '$time_completed'";
    if ($job_type)
        $sql .= " AND ${table}_type = '$job_type'";
    if ($addl_or_cond)
        $sql .= " OR $addl_or_cond";
    if ($addl_and_cond)
        $sql .= " OR $addl_and_cond";
    return $sql;
}

// $user_groups can be empty, in which case only the user's jobs are returned.
function retrieve_and_display($start_date, $user_email, $user_groups) {
    global $sb_db;
    global $gnt_db;
    global $est_db;
    global $recent_first;
    global $db;

    $func = function ($val) { return "job_group.user_group = '$val'"; };
    $group_clause = implode(" OR ", array_map($func, $user_groups));

    $sb_jobs_file = array();
    $sb_sql = get_group_select_statement($sb_db, "identify", $user_email, $group_clause, $start_date);
    //$sb_sql = "SELECT * FROM $sb_db.identify WHERE identify_email = '$user_email' AND identify_time_completed >= '$start_date'";
    //$sb_sql = "SELECT identify_id, identify_key, identify_status, identify_params, quantify_id FROM $sb_db.identify LEFT JOIN $sb_db.quantify ON identify_id = quantify_identify_id WHERE identify_email = '$user_email' AND identify_time_completed >= '$start_date'";
    $results = $db->query($sb_sql);
    $sb_jobs_file = get_job_list($results, "identify", LEVEL2, GET_IDS_FROM_FILE|GET_IDS_IGNORE_PARENT);
    $sb_jobs_file = add_quantify_jobs($sb_jobs_file, $db, $sb_db);
    

    $gnt_sql = get_group_select_statement($gnt_db, "gnn", $user_email, $group_clause, $start_date);
    //$gnt_sql = "SELECT * FROM $gnt_db.gnn WHERE gnn_email = '$user_email' AND gnn_time_completed >= '$start_date'";
    $results = $db->query($gnt_sql);
    $gnt_jobs_file = get_job_list($results, "gnn", LEVEL2, GET_IDS_FROM_FILE); // These link to generate results pages (stepc, the ID is a generate ID)
    $gnt_jobs_db = get_job_list($results, "gnn", LEVEL2, GET_IDS_FROM_DB); // These link to analysis jobs (stepe, the ID is an analysis ID)
    $gnt_child_jobs = get_job_list($results, "gnn", LEVEL3, GET_IDS_FROM_DB); // GNT jobs that are children of another GNT job
    
    // Find all EST IDs
    $all_est_ids = array();
    $est_sql = "SELECT generate.generate_id FROM $est_db.generate";
    if ($group_clause)
        $est_sql .= " LEFT OUTER JOIN $est_db.job_group ON generate.generate_id = job_group.generate_id WHERE $group_clause";
    else
        $est_sql .= " WHERE generate_email = '$user_email'";
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
        $additional_ids_clause = "generate.generate_id IN (" . implode(",", $extra_est_ids) . ")";
    
    
    $color_sql = get_group_select_statement($est_db, "generate", $user_email, $group_clause, $start_date, "COLORSSN", $additional_ids_clause);
    //$color_sql = "SELECT * FROM $est_db.generate WHERE generate_email = '$user_email' AND generate_type = 'COLORSSN' AND (generate_time_completed >= '$start_date' $additional_ids_clause)";
    $results = $db->query($color_sql);
    $color_jobs_file = get_job_list($results, "generate", LEVEL2, GET_IDS_FROM_FILE);
    $color_jobs_db = get_job_list($results, "generate", LEVEL2, GET_IDS_FROM_DB);
    
    
    $est_sql = "SELECT generate.generate_id, generate_key, generate_params, generate_type, generate_time_created, analysis_id, analysis_name FROM $est_db.generate LEFT JOIN $est_db.analysis ON generate.generate_id = analysis.analysis_generate_id";
    if ($group_clause)
        $est_sql .= " LEFT OUTER JOIN $est_db.job_group ON generate.generate_id = job_group.generate_id WHERE $group_clause";
    else
        $est_sql .= " WHERE generate_email = '$user_email'";
    if ($additional_ids_clause)
        $additional_ids_clause = " OR " . $additional_ids_clause;
    $est_sql .= " AND generate_type != 'COLORSSN' AND generate_status = 'FINISH' AND " .
        "(generate_time_completed >= '$start_date' OR analysis_time_completed >= '$start_date' $additional_ids_clause) ORDER BY generate_id";
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
    
    list($gnt_jobs_file_assn, $gnt_jobs_db_assn, $gnt_jobs_child_assn, $color_jobs_file_assn, $color_jobs_db_assn) = output_tree($est_order, $est_grouping, $gnt_jobs_file, $gnt_jobs_db, $gnt_child_jobs, $color_jobs_file, $color_jobs_db, $sb_jobs_file);
    return array($gnt_jobs_file, $gnt_jobs_db, $gnt_child_jobs, $color_jobs_file, $color_jobs_db, $sb_jobs_file, $gnt_jobs_file_assn, $gnt_jobs_db_assn, $gnt_jobs_child_assn, $color_jobs_file_assn, $color_jobs_db_assn);
}


function output_tree($est_order, $est_grouping, $gnt_jobs_file, $gnt_jobs_db, $gnt_child_jobs, $color_jobs_file, $color_jobs_db, $sb_jobs_file) {
    global $hide_empty;
    $color_jobs_file_assn = array(); // Assigned color jobs
    $color_jobs_db_assn = array(); // Assigned color jobs
    $sb_jobs_file_assn = array();
    $gnt_jobs_file_assn = array();
    $gnt_jobs_db_assn = array();
    $gnt_jobs_child_assn = array();

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
            $job_name .= " [$job_type; $families$uniref]";
        else
            $job_name .= " [$job_type]";
        $job_name .= make_extra(" (EST Job #$gid)");
    
        $level1_html = "";
    
        if (isset($gnt_jobs_file[$gid])) {
            //$level1_html .= get_gnt_html($gnt_jobs_file[$gid], $gnt_child_jobs, $sb_jobs_file, "      ", LEVEL1, $gid);
            list($html, $child_assn) = get_gnt_html($gnt_jobs_file[$gid], $gnt_child_jobs, $sb_jobs_file, "      ", LEVEL1, $gid);
            foreach ($child_assn as $child_id => $junk) {
                $gnt_jobs_child_assn[$child_id] = 1;
            }
            $level1_html .= $html;
            $gnt_jobs_file_assn[$gid] = 1;
        }
    
        if (isset($color_jobs_file[$gid])) {
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
            if (isset($color_jobs_db[$aid])) {
                $chtml .= get_colorssn_html($color_jobs_db[$aid], $sb_jobs_file, "          ", LEVEL2, $aid);
                $color_jobs_db_assn[$aid] = 1;
            }
    
            $ghtml = "";
            if (isset($gnt_jobs_db[$aid])) {
                //$ghtml .= get_gnt_html($gnt_jobs_db[$aid], $gnt_child_jobs, $sb_jobs_file, "          ", LEVEL2, $aid);
                list($html, $child_assn) = get_gnt_html($gnt_jobs_db[$aid], $gnt_child_jobs, $sb_jobs_file, "          ", LEVEL2, $aid);
                foreach ($child_assn as $child_id => $junk) {
                    $gnt_jobs_child_assn[$child_id] = 1;
                }
                $ghtml .= $html;
                $gnt_jobs_db_assn[$aid] = 1;
            }
    
            $ssn_extra = make_extra(" (SSN Job #$aid)");
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
    return array($gnt_jobs_file_assn, $gnt_jobs_db_assn, $gnt_jobs_child_assn, $color_jobs_file_assn, $color_jobs_db_assn);
}


?>

