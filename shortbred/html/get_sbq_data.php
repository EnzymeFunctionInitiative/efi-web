<?php

require_once("../includes/main.inc.php");
require_once("../libs/identify.class.inc.php");
require_once("../libs/quantify.class.inc.php");

$is_error = false;
$the_id = "";
$q_id = "";
$job_obj = NULL;

if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isset($_POST["key"])) {
    $the_id = $_POST["id"];
#    if (isset($_POST["quantify-id"]) && is_numeric($_POST["quantify-id"])) {
#        $q_id = $_POST["quantify-id"];
#        $job_obj = new quantify($db, $q_id);
#    } else {
        $job_obj = new identify($db, $the_id);
#    }

    if ($job_obj->get_key() != $_POST["key"]) {
        $is_error = true;
    } elseif (time() < $job_obj->get_time_completed() + settings::get_retention_days()) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}

$result = array("valid" => false);

if ($is_error) {
    echo json_encode($result);
    exit(0);
}

$id_dir = $job_obj->get_results_path();
$clust_file = $id_dir . "/" . quantify::get_normalized_cluster_file_name();

if (!file_exists($clust_file)) {
    echo json_encode($result);
    exit(0);
}


$fh = fopen($clust_file, "r");

if ($fh) {
    $header_line = trim(fgets($fh));
    $headers = explode("\t", $header_line);
    $metagenomes = array_slice($headers, 1);
    
    $clusters = array();

    while (($line = fgets($fh)) !== false) {
        $line = trim($line);
        $parts = explode("\t", $line);
        $cluster = $parts[0];

        if ($cluster == "#N/A")
            continue;

        $info = array("number" => $cluster, "abundance" => array());
        $sum = 0;
        for ($i = 0; $i < count($metagenomes); $i++) {
            $info["abundance"][$i] = $parts[1 + $i];
            $sum += $parts[1 + $i];
        }

        if ($sum >= 1e-6)
            array_push($clusters, $info);
    }

    $site_info = get_mg_db_info();

    $sort_fn = function($a, $b) use ($site_info) {
        if (!isset($site_info["site"][$a]))
            return 0;
        elseif (!isset($site_info["site"][$b]))
            return 0;

        $cmp = strcmp($site_info["site"][$a], $site_info["site"][$b]);
        if ($cmp != 0)
            return $cmp;
        
        $cmp = strcmp($site_info["gender"][$a], $site_info["gender"][$b]);
        if ($cmp != 0)
            return $cmp;

        $cmp = strcmp($a, $b);
        return $cmp;
    };

    usort($metagenomes, $sort_fn);

    $result["clusters"] = $clusters;
    $result["metagenomes"] = $metagenomes;
    $result["valid"] = true;
    
    fclose($fh);
} else {
}

echo json_encode($result);







function get_mg_db_info() {
    $mg_dbs = settings::get_metagenome_db_list();

    $mg_db_list = explode(",", $mg_dbs);

    $info = array("site" => array(), "gender" => array());

    foreach ($mg_db_list as $mg_db) {
        $fh = fopen($mg_db, "r");
        if ($fh === false)
            continue;

        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                continue; // skip comments

            $pos = strpos($data[1], "-");
            $site = trim(substr($data[1], $pos));
            $info["site"][$data[0]] = $site;
            $info["gender"][$data[0]] = $data[2];
        }

        fclose($fh);
    }

    return $info;
}

?>
