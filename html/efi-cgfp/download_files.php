<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__CGFP_DIR__ . "/includes/main.inc.php");
require_once(__CGFP_DIR__ . "/libs/identify.class.inc.php");
require_once(__CGFP_DIR__ . "/libs/quantify.class.inc.php");

$is_error = true;
$the_id = "";
$q_id = "";
$job_obj = NULL;

// There are two types of examples: dynamic and static.  The static example is a curated
// example pulled into the entry screen.  The dynamic examples are the same as other
// jobs, except they are stored in separate directories/tables.
$is_example = isset($_GET["x"]) ? true : false;

if (isset($_GET["example"])) {
    $example_dir = settings::get_example_dir();
    $example_web_path = settings::get_example_web_path();
    if (file_exists($example_dir) && $example_web_path) {
        $job_obj = new quantify_example($db, $example_dir, $example_web_path);
        $is_error = false;
    }
    $q_id = isset($_GET["identify"]) ? 0 : 1;
} elseif (isset($_GET["id"]) && is_numeric($_GET["id"]) && isset($_GET["key"])) {
    $the_id = $_GET["id"];
    if (isset($_GET["quantify-id"]) && is_numeric($_GET["quantify-id"])) {
        $q_id = $_GET["quantify-id"];
        $job_obj = new quantify($db, $q_id, $is_example);
    } else {
        $job_obj = new identify($db, $the_id, $is_example);
    }

    if ($job_obj->get_key() != $_GET["key"]) {
        $is_error = true;
    } elseif ($job_obj->is_expired()) {
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
        );
    }

    $prefix = "${the_id}_${q_id}_";
    if (isset($_GET["example"]))
        $prefix = "";

    if ($is_example)
        $rel_out_dir = settings::get_rel_example_http_output_dir();
    else
        $rel_out_dir = settings::get_rel_http_output_dir();

    if (isset($file_info[$type])) {
        $file_path = $file_info[$type]["file_path"];
        $suffix = $file_info[$type]["suffix"];
        $file_name = $job_obj->get_filename();
        $is_error = ! send_text_file($prefix, $file_path, $file_name, $suffix);
    } elseif ($type == "ssn-q") {
        $ssn_file = $job_obj->get_ssn_http_path();
        $url = $rel_out_dir . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-q-zip") {
        $ssn_file = $job_obj->get_zip_ssn_http_path();
        $url = $rel_out_dir . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-c") {
        $ssn_file = $job_obj->get_ssn_http_path();
        $url = $rel_out_dir . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "ssn-c-zip") {
        $ssn_file = $job_obj->get_output_ssn_zip_http_path();
        $url = $rel_out_dir . "/" . $ssn_file;
        header("Location: $url");
    } elseif ($type == "q-mg-info") {
        $suffix = "_metagenome_desc.txt";
        $file_name = $job_obj->get_filename();
        $text_data = $job_obj->get_metagenome_info_as_text();
        $is_error = ! send_text_data($prefix, $text_data, $file_name, $suffix);
    } else {
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}





function send_text_file($download_prefix, $file_path, $download_name, $download_suffix) {
    if (file_exists($file_path)) {
        $download_filename = $download_prefix . pathinfo($download_name, PATHINFO_FILENAME) . $download_suffix;
        $content_size = filesize($file_path);
        sendHeaders($download_filename, $content_size);
        readfile($file_path);
        return true;
    } else {
        return false;
    }
}

function send_text_data($download_prefix, $text_data, $download_name, $download_suffix) {
    $download_filename = $download_prefix . pathinfo($download_name, PATHINFO_FILENAME) . $download_suffix;
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
