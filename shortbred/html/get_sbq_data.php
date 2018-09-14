<?php

require_once("../includes/main.inc.php");
require_once("../libs/identify.class.inc.php");
require_once("../libs/quantify.class.inc.php");

const MERGED = 1;       // Both clusters and singletons
const SINGLETONS = 2;   // Only singletons in the SSN
const CLUSTERS = 3;     // Only SSN clusters >= 2 in size

const GROUP_ALL = 1;
const GROUP_JOB = 2;


$is_error = false;
$the_id = "";
$q_id = "";
$job_obj = NULL;

if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
    if (isset($_POST["quantify-id"]) && is_numeric($_POST["quantify-id"])) {
        $q_id = $_POST["quantify-id"];
        $job_obj = new quantify($db, $q_id);
    } else {
        $job_obj = new identify($db, $the_id);
    }

    if ($job_obj->get_key() != $_POST["key"]) {
        $is_error = true;
    } elseif (time() < $job_obj->get_time_completed() + settings::get_retention_days()) {
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


$result = array("valid" => false);

if ($is_error) {
    echo json_encode($result);
    exit(0);
}

if ($q_id) {
    $id_dir = $job_obj->get_identify_output_path();
    $clust_file = $job_obj->get_genome_normalized_cluster_file_path();
} else {
    $id_dir = $job_obj->get_results_path();
    $clust_file = $id_dir . "/" . quantify::get_genome_normalized_cluster_file_name();
}

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


$fh = fopen($clust_file, "r");

if ($fh) {
    $header_line = trim(fgets($fh));
    $headers = explode("\t", $header_line);
    $start_idx = 1;
    if (in_array("Cluster Size", $headers))
        $start_idx = 2;

    $metagenomes = array_slice($headers, $start_idx);

    // SORT AND MAP METAGENOMES SO THAT THEY CAN BE GROUPED BY BODY SITE
    $mg_lookup_temp = array();
    for ($i = 0; $i < count($metagenomes); $i++) {
        $mg_lookup_temp[$metagenomes[$i]] = $i;
    }
    $site_info = get_mg_db_info();
    $sort_fn = function($a, $b) use ($site_info) {
        if (!isset($site_info["site"][$a])) return 0;
        elseif (!isset($site_info["site"][$b])) return 0;
        $cmp = strcmp($site_info["order"][$a], $site_info["order"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($site_info["site"][$a], $site_info["site"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($site_info["gender"][$a], $site_info["gender"][$b]);
        if ($cmp != 0) return $cmp;
        $cmp = strcmp($a, $b);
        return $cmp;
    };
    usort($metagenomes, $sort_fn);
    $mg_lookup = array();
    $site_color = array();
    for ($i = 0; $i < count($metagenomes); $i++) {
        $mg_lookup[$mg_lookup_temp[$metagenomes[$i]]] = $i;
    }

    //TODO: this is only needed until legacy jobs (prior to 8/4/2018) are eased out of the system.
    $cluster_sizes = get_cluster_sizes("$id_dir/cluster.sizes");

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
            $mg_idx = $mg_lookup[$i];
            $val = $parts[$start_idx + $i];
            
            if ($val > $max)
                $max = $val;
            if ($val < $min)
                $min = $val;

            // Skip the value if it's lower than the input threshold.
            if ($val < $lower_thresh)
                $val = 0;
            
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
        if (is_numeric($part)) {
            $nums[$part] = true;
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



function get_mg_db_info() {
    $mg_dbs = settings::get_metagenome_db_list();

    $mg_db_list = explode(",", $mg_dbs);

    $info = array("site" => array(), "gender" => array());

    foreach ($mg_db_list as $mg_db) {
        $fh = fopen($mg_db, "r");
        if ($fh === false)
            continue;

        $meta = get_mg_metadata($mg_db);

        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                continue; // skip comments

            $mg_id = $data[0];

            $pos = strpos($data[1], "-");
            $site = trim(substr($data[1], $pos+1));
            $site = str_replace("_", " ", $site);
            $info["site"][$mg_id] = $site;
            $info["gender"][$mg_id] = $data[2];
            $info["color"][$mg_id] = isset($meta[$site]) ? $meta[$site]["color"] : "";
            $info["order"][$mg_id] = isset($meta[$site]) ? $meta[$site]["order"] : "";
        }

        fclose($fh);
    }

    return $info;
}

function get_mg_metadata($mg_db) {

    $info = array(); # map site to color

    $scheme_file = "$mg_db.metadata";
    if (file_exists($scheme_file)) {
        $fh = fopen($scheme_file, "r");
        if ($fh === false)
            return false;
    } else {
        return false;
    }

    while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
        if (isset($data[0]) && $data[0] && $data[0][0] == "#")
            continue; // skip comments

        $site = str_replace("_", " ", $data[0]);
        $color = $data[1];
        $order = isset($data[2]) ? $data[2] : 0;
        $info[$site] = array('color' => $color, 'order' => $order);
    }

    fclose($fh);

    return $info;
}

function get_cluster_sizes($file) {
    $sizes = array();

    if (!file_exists($file))
        return $sizes;

    $fh = fopen($file, "r");
    while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
        $sizes[$data[0]] = $data[1];
    }
    fclose($fh);

    return $sizes;
}

?>
