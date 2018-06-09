<?php

require_once("../includes/main.inc.php");
require_once("../libs/identify.class.inc.php");
require_once("../libs/quantify.class.inc.php");

$is_error = true;
$the_id = "";
$q_id = "";
$job_obj = NULL;

if (isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_GET["key"])) {
    $the_id = $_GET["id"];
    if (isset($_GET["quantify-id"]) && is_numeric($_GET["quantify-id"])) {
        $q_id = $_GET["quantify-id"];
        $job_obj = new quantify($db, $q_id);
    } else {
        $job_obj = new identify($db, $the_id);
    }

    if ($job_obj->get_key() != $_GET["key"]) {
        $is_error = true;
    } elseif (time() < $job_obj->get_time_completed() + settings::get_retention_days()) {
        $is_error = true;
    } else {
        $is_error = false;
    }
}


if ($is_error) {
    error404();
}

if (isset($_GET["type"])) {

    $type = $_GET["type"];

    if (!$q_id) {
        $file_info = array(
            "markers"       => array("file_path" => $job_obj->get_marker_file_path(), "suffix" => ".faa"),
            "cdhit"         => array("file_path" => $job_obj->get_cdhit_file_path(), "suffix" => "_cdhit.txt"),
        );
    } else {
        $file_info = array(
            "q-prot"        => array("file_path" => $job_obj->get_protein_file_path(), "suffix" => "_protein_abundance.txt"),
            "q-clust"       => array("file_path" => $job_obj->get_cluster_file_path(), "suffix" => "_cluster_abundance.txt"),
            "q-prot-n"      => array("file_path" => $job_obj->get_normalized_protein_file_path(), "suffix" => "_protein_abundance_norm.txt"),
            "q-clust-n"     => array("file_path" => $job_obj->get_normalized_cluster_file_path(), "suffix" => "_cluster_abundance_norm.txt"),
            "q-prot-gn"     => array("file_path" => $job_obj->get_genome_normalized_protein_file_path(), "suffix" => "_protein_abundance_genome_norm.txt"),
            "q-clust-gn"    => array("file_path" => $job_obj->get_genome_normalized_cluster_file_path(), "suffix" => "_cluster_abundance_genome_norm.txt"),
            "q-prot-m"      => array("file_path" => $job_obj->get_merged_protein_file_path(), "suffix" => "_merged_protein_abundance.txt"),
            "q-clust-m"     => array("file_path" => $job_obj->get_merged_cluster_file_path(), "suffix" => "_merged_cluster_abundance.txt"),
            "q-prot-m-n"    => array("file_path" => $job_obj->get_merged_normalized_protein_file_path(), "suffix" => "_merged_protein_abundance_norm.txt"),
            "q-clust-m-n"   => array("file_path" => $job_obj->get_merged_normalized_cluster_file_path(), "suffix" => "_merged_cluster_abundance_norm.txt"),
            "q-prot-m-gn"   => array("file_path" => $job_obj->get_merged_genome_normalized_protein_file_path(), "suffix" => "_merged_protein_abundance_genome_norm.txt"),
            "q-clust-m-gn"  => array("file_path" => $job_obj->get_merged_genome_normalized_cluster_file_path(), "suffix" => "_merged_cluster_abundance_genome_norm.txt"),
        );
    }

    if (isset($file_info[$type])) {
        $file_path = $file_info[$type]["file_path"];
        $suffix = $file_info[$type]["suffix"];
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
    } elseif ($type == "ssn-q") {
        $ssn_file = $job_obj->get_ssn_http_path();
        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-q-zip") {
        $ssn_file = $job_obj->get_zip_ssn_http_path();
        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-c") {
        $ssn_file = $job_obj->get_ssn_http_path();
        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-c-zip") {
        $ssn_file = $job_obj->get_output_ssn_zip_http_path();
        $url = settings::get_rel_http_output_dir() . "/" . $ssn_file;
        header("Location: $url");
    } else {
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}




function send_text_file($the_id, $q_id, $file_path, $download_name, $download_suffix) {
    if (file_exists($file_path)) {
        $download_filename = "${the_id}_q${q_id}_" . pathinfo($download_name, PATHINFO_FILENAME) . $download_suffix;
        $content_size = filesize($file_path);
        sendHeaders($download_filename, $content_size);
        readfile($file_path);
        return true;
    } else {
        return false;
    }
}



function sendHeaders($download_filename, $content_size) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $content_size);
    ob_clean();
}


/*
 *
    if ($type == "markers") {
        $file_path = $job_obj->get_marker_file_path();
        $suffix =  . ".faa";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($marker_file)) {
//            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . ".faa";
//            $content_size = filesize($marker_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($marker_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    } elseif ($type == "cdhit") {
        $file_path = $job_obj->get_cdhit_file_path();
        $suffix = "_cdhit.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($cdhit_file)) {
//            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cdhit.txt";
//            $content_size = filesize($cdhit_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($cdhit_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    } elseif ($type == "q-prot") {
        $file_path = $job_obj->get_protein_file_path();
        $suffix = "_protein_abundance.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($protein_file)) {
//            $download_filename = "${the_id}_q${q_id}_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance.txt";
//            $content_size = filesize($protein_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($protein_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    } elseif ($type == "q-clust") {
        $file_path = $job_obj->get_cluster_file_path();
        $suffix = "_cluster_abundance.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($cluster_file)) {
//            $download_filename = "${the_id}_q${q_id}_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance.txt";
//            $content_size = filesize($cluster_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($cluster_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    
    // Individual quantify run, protein results
    } elseif ($type == "q-prot-m") {
        $file_path = $job_obj->get_merged_protein_file_path();
        $suffix = "_protein_abundance.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($protein_file)) {
//            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance.txt";
//            $content_size = filesize($protein_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($protein_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    
    // Individual quantify run, cluster results
    } elseif ($type == "q-clust-m") {
        $file_path = $job_obj->get_merged_cluster_file_path();
        $suffix = "_cluster_abundance.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($cluster_file)) {
//            $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance.txt";
//            $content_size = filesize($cluster_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($cluster_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    // All quantify results merged, protein results, normalized
    } elseif ($type == "q-prot-m-n") {
        $file_path = $job_obj->get_merged_normalized_protein_file_path();
        $suffix = "_protein_abundance_norm.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($protein_file)) {
//            $download_filename = $the_id . "_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance_norm.txt";
//            $content_size = filesize($protein_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($protein_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    
    // All quantify results merged, cluster results, normalized
    } elseif ($type == "q-clust-m-n") {
        $file_path = $job_obj->get_merged_normalized_cluster_file_path();
        $suffix = "_cluster_abundance_norm.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($cluster_file)) {
//            $download_filename = pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance_norm.txt";
//            $content_size = filesize($cluster_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($cluster_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    
    // Individual quantify run, protein results, normalized
    } elseif ($type == "q-prot-n") {
        $file_path = $job_obj->get_normalized_protein_file_path();
        $suffix = "_protein_abundance_norm.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($protein_file)) {
//            $download_filename = "${the_id}_q${q_id}_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_protein_abundance_norm.txt";
//            $content_size = filesize($protein_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($protein_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }

    // Individual quantify run, cluster results, normalized
    } elseif ($type == "q-clust-n") {
        $file_path = $job_obj->get_normalized_cluster_file_path();
        $suffix = "_cluster_abundance_norm.txt";
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($the_id, $q_id, $file_path, $file_name, $suffix);
//        if (file_exists($cluster_file)) {
//            $download_filename = "${the_id}_q${q_id}_" . pathinfo($job_obj->get_filename(), PATHINFO_FILENAME) . "_cluster_abundance_norm.txt";
//            $content_size = filesize($cluster_file);
//            sendHeaders($download_filename, $content_size);
//            readfile($cluster_file);
//            exit(0);
//        } else {
//            $is_error = true;
//        }
    


 */

?>
