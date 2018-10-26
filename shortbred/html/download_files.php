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
            "meta-cl-size"  => array("file_path" => $job_obj->get_metadata_cluster_sizes_file_path(), "suffix" => "_cluster_sizes.txt"),
            "meta-sp-cl"    => array("file_path" => $job_obj->get_metadata_swissprot_clusters_file_path(), "suffix" => "_swissprot_clusters.txt"),
            "meta-sp-si"    => array("file_path" => $job_obj->get_metadata_swissprot_singles_file_path(), "suffix" => "_swissprot_singletons.txt"),
        );
    } else {
        $want_mean = true;
        $file_info = array(
            // MEDIAN
            "q-prot"            => array("file_path" => $job_obj->get_protein_file_path(), "suffix" => "_protein_abundance_median.txt"),
            "q-clust"           => array("file_path" => $job_obj->get_cluster_file_path(), "suffix" => "_cluster_abundance_median.txt"),
            "q-prot-n"          => array("file_path" => $job_obj->get_normalized_protein_file_path(), "suffix" => "_protein_abundance_norm_median.txt"),
            "q-clust-n"         => array("file_path" => $job_obj->get_normalized_cluster_file_path(), "suffix" => "_cluster_abundance_norm_median.txt"),
            "q-prot-gn"         => array("file_path" => $job_obj->get_genome_normalized_protein_file_path(), "suffix" => "_protein_abundance_genome_norm_median.txt"),
            "q-clust-gn"        => array("file_path" => $job_obj->get_genome_normalized_cluster_file_path(), "suffix" => "_cluster_abundance_genome_norm_median.txt"),

            // MEAN
            "q-prot-mean"       => array("file_path" => $job_obj->get_protein_file_path($want_mean), "suffix" => "_protein_abundance_mean.txt"),
            "q-clust-mean"      => array("file_path" => $job_obj->get_cluster_file_path($want_mean), "suffix" => "_cluster_abundance_mean.txt"),
            "q-prot-n-mean"     => array("file_path" => $job_obj->get_normalized_protein_file_path($want_mean), "suffix" => "_protein_abundance_norm_mean.txt"),
            "q-clust-n-mean"    => array("file_path" => $job_obj->get_normalized_cluster_file_path($want_mean), "suffix" => "_cluster_abundance_norm_mean.txt"),
            "q-prot-gn-mean"    => array("file_path" => $job_obj->get_genome_normalized_protein_file_path($want_mean), "suffix" => "_protein_abundance_genome_norm_mean.txt"),
            "q-clust-gn-mean"   => array("file_path" => $job_obj->get_genome_normalized_cluster_file_path($want_mean), "suffix" => "_cluster_abundance_genome_norm_mean.txt"),

// DEPRECATED
//            "q-prot-m"      => array("file_path" => $job_obj->get_merged_protein_file_path(), "suffix" => "_merged_protein_abundance.txt"),
//            "q-clust-m"     => array("file_path" => $job_obj->get_merged_cluster_file_path(), "suffix" => "_merged_cluster_abundance.txt"),
//            "q-prot-m-n"    => array("file_path" => $job_obj->get_merged_normalized_protein_file_path(), "suffix" => "_merged_protein_abundance_norm.txt"),
//            "q-clust-m-n"   => array("file_path" => $job_obj->get_merged_normalized_cluster_file_path(), "suffix" => "_merged_cluster_abundance_norm.txt"),
//            "q-prot-m-gn"   => array("file_path" => $job_obj->get_merged_genome_normalized_protein_file_path(), "suffix" => "_merged_protein_abundance_genome_norm.txt"),
//            "q-clust-m-gn"  => array("file_path" => $job_obj->get_merged_genome_normalized_cluster_file_path(), "suffix" => "_merged_cluster_abundance_genome_norm.txt"),
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
    } elseif ($type == "q-mg-info") {
        $suffix = "_metagenome_desc.txt";
        $file_name = $job_obj->get_filename();
        $text_data = $job_obj->get_metagenome_info_as_text();
        $is_error = ! send_text_data($the_id, $q_id, $text_data, $file_name, $suffix);
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

function send_text_data($the_id, $q_id, $text_data, $download_name, $download_suffix) {
    $download_filename = "${the_id}_q${q_id}_" . pathinfo($download_name, PATHINFO_FILENAME) . $download_suffix;
    $content_size = strlen($text_data);
    sendHeaders($download_filename, $content_size);
    echo $text_data;
    return true;
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


?>
