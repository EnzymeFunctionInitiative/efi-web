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
$clust_file = $id_dir . "/" . quantify::get_cluster_file_name();

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

        $info = array("number" => $cluster, "abundance" => array());
        for ($i = 0; $i < count($metagenomes); $i++) {
            $info["abundance"][$i] = $parts[1 + $i];
        }

        array_push($clusters, $info);
    }

    $result["clusters"] = $clusters;
    $result["metagenomes"] = $metagenomes;
    $result["valid"] = true;
    
    fclose($fh);
} else {
}

echo json_encode($result);


#if (isset($_POST["type"])) {
#
#    $type = $_POST["type"];
#
#    if ($type == "markers") {
#        $marker_file = $job_obj->get_marker_file_path();
#        if (file_exists($marker_file)) {
#            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . ".faa";
#            $content_size = filesize($marker_file);
#            sendHeaders($download_filename, $content_size);
#            readfile($marker_file);
#            exit(0);
#        } else {
#            $is_error = true;
#        }
#    } elseif ($type == "q-prot") {
#        $protein_file = $job_obj->get_protein_file_path();
#        if (file_exists($protein_file)) {
#            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance.txt";
#            $content_size = filesize($protein_file);
#            sendHeaders($download_filename, $content_size);
#            readfile($protein_file);
#            exit(0);
#        } else {
#            $is_error = true;
#        }
#    } elseif ($type == "q-clust") {
#        $cluster_file = $job_obj->get_cluster_file_path();
#        if (file_exists($cluster_file)) {
#            $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance.txt";
#            $content_size = filesize($cluster_file);
#            sendHeaders($download_filename, $content_size);
#            readfile($cluster_file);
#            exit(0);
#        } else {
#            $is_error = true;
#        }
#    } elseif ($type == "q-prot-m") {
#        $protein_file = $job_obj->get_merged_protein_file_path();
#        if (file_exists($protein_file)) {
#            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance.txt";
#            $content_size = filesize($protein_file);
#            sendHeaders($download_filename, $content_size);
#            readfile($protein_file);
#            exit(0);
#        } else {
#            $is_error = true;
#        }
#    } elseif ($type == "q-clust-m") {
#        $cluster_file = $job_obj->get_merged_cluster_file_path();
#        if (file_exists($cluster_file)) {
#            $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance.txt";
#            $content_size = filesize($cluster_file);
#            sendHeaders($download_filename, $content_size);
#            readfile($cluster_file);
#            exit(0);
#        } else {
#            $is_error = true;
#        }
#    } elseif ($type == "ssn-q") {
#        $ssn_file = $job_obj->get_ssn_http_path();
#        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
#        header("Location: $url");
##        $marker_file = $job_obj->get_marker_file_path();
##        if (file_exists($marker_file)) {
##            $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . ".faa";
##            $content_size = filesize($marker_file);
##            sendHeaders($download_filename, $content_size);
##            readfile($marker_file);
##            exit(0);
##        } else {
##            $is_error = true;
##        }
#    } elseif ($type == "ssn-c") {
#        $ssn_file = $job_obj->get_ssn_http_path();
#        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
#        header("Location: $url");
#        # We need to do a redirect because the output files can be very large and we don't want to (can't?)
#        # read the entire file into memory like php wants to do below.
#        #if (file_exists($xgmml_file)) {
#        #    $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_marker.xgmml";
#        #    $content_size = filesize($xgmml_file);
#        #    sendHeaders($download_filename, $content_size);
#        #    readfile($xgmml_file);
#        #    exit(0);
#        #} else {
#        #    $is_error = true;
#        #}
#    } else {
#        $is_error = true;
#    }
#}
#
#if ($is_error) {
#    error404();
#}
#
#
#
#
#
#function sendHeaders($download_filename, $content_size) {
#    header('Content-Description: File Transfer');
#    header('Content-Type: application/octet-stream');
#    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
#    header('Content-Transfer-Encoding: binary');
#    header('Connection: Keep-Alive');
#    header('Expires: 0');
#    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
#    header('Pragma: public');
#    header('Content-Length: ' . $content_size);
#    ob_clean();
#}




?>
