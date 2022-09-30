<?php
require_once(__DIR__."/../../init.php");

use \efi\cgfp\settings;
use \efi\cgfp\identify;
use \efi\cgfp\quantify;
use \efi\cgfp\quantify_example;
use \efi\cgfp\metagenome_db_manager;


const MERGED = 1;       // Both clusters and singletons
const SINGLETONS = 2;   // Only singletons in the SSN
const CLUSTERS = 3;     // Only SSN clusters >= 2 in size

const GROUP_ALL = 1;
const GROUP_JOB = 2;


$is_error = false;
$the_id = "";
$q_id = "";
$job_obj = NULL;

// There are two types of examples: dynamic and static.  The static example is a curated
// example pulled into the entry screen.  The dynamic examples are the same as other
// jobs, except they are stored in separate directories/tables.
$is_example = isset($_POST["x"]) ? true : false;

if (isset($_POST["static-example"])) {
    $ex_dir = settings::get_example_dir();
    if (file_exists($ex_dir)) {
        $web_path = settings::get_example_web_path();
        $job_obj = new quantify_example($db, $ex_dir, $web_path);
        $q_id = 1;
    } else {
        $is_error = true;
    }
} elseif (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
    if (isset($_POST["quantify-id"]) && is_numeric($_POST["quantify-id"])) {
        $q_id = $_POST["quantify-id"];
        $job_obj = new quantify($db, $q_id, $is_example);
    } else {
        $job_obj = new identify($db, $the_id, $is_example);
    }

    if ($job_obj->get_key() != $_POST["key"]) {
        $is_error = true;
    } elseif ($job_obj->is_expired()) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}

$get_results = MERGED;
if (isset($_POST["res"])) {
    switch ($_POST["res"]) {
        case "s":
            $get_results = SINGLETONS;
            break;
        case "c":
            $get_results = CLUSTERS;
            break;
        case "m":
        default:
            $get_results = MERGED;
            break;
    }
}

$use_mean = false;
if (isset($_POST["use-mean"])) {
    $use_mean = true;
}
$hits_only = false; // Return 1 for cells that have a value of any kind, 0 otherwise
if (isset($_POST["hits-only"])) {
    $hits_only = true;
    //$use_mean = true;
}


$result = array("valid" => false);

if ($is_error || !$q_id) {
    echo json_encode($result);
    exit(0);
}

$id_dir = $job_obj->get_identify_output_path();
$clust_file = $job_obj->get_genome_normalized_cluster_file_path($use_mean);


if (!file_exists($clust_file)) {
    echo json_encode($result);
    exit(0);
}

$req_clusters = array();
if (isset($_POST["clusters"])) {
    $req_clusters = parse_clusters($_POST["clusters"]);
}

$lower_thresh = 0;
if (isset($_POST["lower_thresh"]) && is_numeric($_POST["lower_thresh"]))
    $lower_thresh = $_POST["lower_thresh"];

$upper_thresh = 1000000;
if (isset($_POST["upper-thresh"]) && is_numeric($_POST["upper-thresh"]))
    $upper_thresh = $_POST["upper-thresh"];

$bodysites = array();
if (isset($_POST["bodysites"]))
    $bodysites = explode("|", $_POST["bodysites"]);

$group_cat = 0;
if (isset($_POST["group-cat"]) && is_numeric($_POST["group-cat"]))
    $group_cat = $_POST["group-cat"];

$fh = fopen($clust_file, "r");

if ($fh) {
    $header_line = trim(fgets($fh));
    $headers = explode("\t", $header_line);
    $start_idx = 1;
    if (in_array("Cluster Size", $headers))
        $start_idx = 2;

    $mg_db_index = $job_obj->get_metagenome_db_id();
    $site_info = metagenome_db_manager::get_metagenome_db_site_info($mg_db_index, $bodysites, $group_cat);
    $metagenomes_hdr = array_slice($headers, $start_idx);
    $metagenomes = array();
    foreach ($metagenomes_hdr as $mg_id) {
        if (isset($site_info["order"][$mg_id]))
            array_push($metagenomes, $mg_id);
    }

    // SORT AND MAP METAGENOMES SO THAT THEY CAN BE GROUPED BY BODY SITE
    $file_col_map = array();
    for ($i = 0; $i < count($metagenomes_hdr); $i++) {
        $file_col_map[$metagenomes_hdr[$i]] = $i;
    }
    $sort_fn = function($a, $b) use ($site_info) {
        if (!isset($site_info["site"][$a])) return 0;
        elseif (!isset($site_info["site"][$b])) return 0;
        $cmp = strcmp($site_info["order"][$a], $site_info["order"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($site_info["site"][$a], $site_info["site"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($site_info["secondary"][$a], $site_info["secondary"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($a, $b);
        return $cmp;
    };
    usort($metagenomes, $sort_fn);
    $mg_order_map = array();
    $site_color = array();
    for ($i = 0; $i < count($metagenomes); $i++) {
        $mg_order_map[$metagenomes[$i]] = $i;
    }

    $clusters = array();
    $singles = array();

    $min = 1000000;
    $max = -1000000;

    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        $parts = explode("\t", $line);
        $cluster = $parts[0];
        $size = $start_idx == 2 ? $parts[1] : 2;
        if (substr($cluster, 0, 1) == "S")
            $size = 1;
        
        //TODO: this is only needed until legacy jobs (prior to 8/4/2018) are eased out of the system.
        if (isset($cluster_sizes[$cluster]))
            $size = $cluster_sizes[$cluster];
        //TODO: this is only needed until legacy jobs (prior to 8/4/2018) are eased out of the system.
        if ($size == 1 && $cluster[0] != "S")
            $cluster = "S$cluster";

        if ($cluster == "#N/A")
            continue;

        $cluster_number = $cluster; // $size == 1 ? "S$cluster" : $cluster;
        $info = array("number" => $cluster_number, "abundance" => array_fill(0, count($metagenomes), 0));
        $sum = 0;
        for ($i = 0; $i < count($metagenomes); $i++) {
            $mg_id = $metagenomes[$i];
            $mg_idx = $mg_order_map[$mg_id];
            $col_idx = $start_idx + $file_col_map[$mg_id];
            $val = $parts[$col_idx];
            
            if ($val > $max)
                $max = $val;
            if ($val < $min)
                $min = $val;

        #print("$cluster,$i,$sum,$val,");
            // Skip the value if it's lower than the input threshold.
            if ($hits_only && $val > 0)
                $val = 1;
            elseif ($val < $lower_thresh)
                $val = 0;
            elseif ($val > $upper_thresh)
                $val = 0;
        #print("$val,\n");
            
            $sum += $val;

            $info["abundance"][$mg_idx] = $val;
        }

        // We do these checks after getting the values in order to obtain the min/max extents of the
        // data.  This is to allow consistent scaling between different plot types.
        //
        // If the user requested specific clusters, only return results for those clusters.
        if (count($req_clusters) && !isset($req_clusters[$cluster]))
            continue;
        if ($get_results == SINGLETONS && $size > 1 && !isset($req_clusters[$cluster]))
            continue;
        elseif ($get_results == CLUSTERS && $size < 2 && !isset($req_clusters[$cluster]))
            continue;

        if ($sum >= 1e-6) {
            if ($size == 1)
                array_push($singles, $info);
            else
                array_push($clusters, $info);
        }
    }

    usort($singles, "cmp_singles");
    $clusters = array_merge($clusters, $singles);

    $result["clusters"] = $clusters;
    $result["metagenomes"] = $metagenomes;
    $result["site_info"] = $site_info;
    $result["valid"] = true;
    $result["extent"] = array("min" => $min, "max" => $max);
    
    fclose($fh);
} else {
}

echo json_encode($result);





function cmp_singles($a, $b) {
    $aa = $a["number"];
    $bb = $b["number"];
    if (substr($aa, 0, 1) == "S")
        $aa = substr($aa, 1);
    if (substr($bb, 0, 1) == "S")
        $bb = substr($bb, 1);
    return $aa - $bb;
}


function parse_clusters($params) {
    $params = str_replace(" ", "", $params);
    $parts = explode(",", $params);
    $nums = array();
    foreach ($parts as $part) {
        $part = strtoupper($part);
        if (is_numeric($part)) {
            $nums[$part] = true;
        } elseif (strpos($part, "S") !== false) { // Singletons
            $range_parts = explode("-", $part);
            if (count($range_parts) == 2) {
                $start = substr($range_parts[0], 1);
                $end = substr($range_parts[1], 1);
                $the_range = range($start, $end);
                foreach ($the_range as $range_val)
                    $nums["S$range_val"] = true;
            } else {
                $nums[$part] = true;
            }
        } else {
            $range_parts = explode("-", $part);
            if (count($range_parts) == 2) {
                $the_range = range($range_parts[0], $range_parts[1]);
                foreach ($the_range as $range_val)
                    $nums[$range_val] = true;
            }
        }
    }
    return $nums;
}




?>
