<?php 
require_once(__DIR__."/../../init.php");

use \efi\table_builder;
use \efi\cgfp\settings;


// Before including this file, some vars must be set:
//   $id_tbl_val
//   $job_obj
//   $table_format (html or tab)
//   $id_query_string
//   $identify_only_id_query_string


if (!isset($IsExample) || $IsExample !== true)
    $IsExample = false;


$table = new table_builder($table_format);

$filename = $job_obj->get_filename();
$min_seq_len = $job_obj->get_min_seq_len();
$max_seq_len = $job_obj->get_max_seq_len();
$search_type = strtoupper($job_obj->get_search_type());
$ref_db = strtoupper($job_obj->get_ref_db());
$id_search_type = strtoupper($job_obj->get_identify_search_type());
$diamond_sens = $job_obj->get_diamond_sensitivity();
$cdhit_sid = $job_obj->get_identify_cdhit_sid();

$table->add_row("Input filename", $filename);
if ($id_tbl_val)
    $table->add_row_with_html("Identify/Quantify ID", $id_tbl_val);
if ($min_seq_len)
    $table->add_row("Minimum sequence length", $min_seq_len);
if ($max_seq_len)
    $table->add_row("Maximum sequence length", $max_seq_len);
if (settings::get_diamond_enabled()) {
    $table->add_row("Identify search type", $id_search_type);
    $table->add_row("Reference database", $ref_db);
    $table->add_row("CD-HIT identity for ShortBRED family definition", $cdhit_sid);
    if ($diamond_sens && $diamond_sens != "normal") //TODO: fix hardcoded constant
        $table->add_row("DIAMOND sensitivity", $diamond_sens);
    $table->add_row("Quantify search type", $search_type);
}

$metadata = $job_obj->get_metadata();
ksort($metadata, SORT_NUMERIC);
foreach ($metadata as $order => $row) {
    $val = is_numeric($row[1]) ? number_format($row[1]) : $row[1];
    $table->add_row_with_class($row[0], $val, "stats-row");
}


$hm_parm_string = $id_query_string . "&search-type=$id_search_type&ref-db=$ref_db&d-sens=$diamond_sens&cdhit-sid=$cdhit_sid";

$use_mean = true;
$mean_cluster_file = $job_obj->get_cluster_file_path($use_mean);
$mean_gn_file = $job_obj->get_genome_normalized_cluster_file_path($use_mean);

$size_data = array(
    "ssn_zip" => $job_obj->get_ssn_zip_file_size(),
    "protein" => $job_obj->get_protein_file_size(false),
    "cluster" => $job_obj->get_cluster_file_size(false),
    "protein_norm" => $job_obj->get_normalized_protein_file_size(false),
    "cluster_norm" => $job_obj->get_normalized_cluster_file_size(false),
    "cdhit" => $job_obj->get_cdhit_file_size(),
    "markers" => $job_obj->get_marker_file_size(),
);
if ($job_obj->get_ssn_file_size())
    $size_data["ssn"] = $job_obj->get_ssn_file_size();
else
    $size_data["ssn"] = 0;

$gn_file = $job_obj->get_genome_normalized_cluster_file_path();
if (file_exists($gn_file)) {
    $size_data["protein_genome_norm"] = $job_obj->get_genome_normalized_protein_file_size(false);
    $size_data["cluster_genome_norm"] = $job_obj->get_genome_normalized_cluster_file_size(false);
}

if (file_exists($mean_cluster_file)) {
    $size_data["protein_mean"] = $job_obj->get_protein_file_size(true);
    $size_data["cluster_mean"] = $job_obj->get_cluster_file_size(true);
    $size_data["protein_norm_mean"] = $job_obj->get_normalized_protein_file_size(true);
    $size_data["cluster_norm_mean"] = $job_obj->get_normalized_cluster_file_size(true);
    if (file_exists($mean_gn_file)) {
        $size_data["protein_genome_norm_mean"] = $job_obj->get_genome_normalized_protein_file_size(true);
        $size_data["cluster_genome_norm_mean"] = $job_obj->get_genome_normalized_cluster_file_size(true);
    }
}

$clusterSizesFileSize = $job_obj->get_metadata_cluster_sizes_file_size();
$swissProtClustersFileSize = $job_obj->get_metadata_swissprot_clusters_file_size();
$swissProtSinglesFileSize = $job_obj->get_metadata_swissprot_singles_file_size();
if ($clusterSizesFileSize)
    $size_data["meta-cl-size"] = $clusterSizesFileSize;
if ($swissProtClustersFileSize)
    $size_data["meta-sp-cl"] = $swissProtClustersFileSize;
if ($swissProtSinglesFileSize)
    $size_data["meta-sp-si"] = $swissProtSinglesFileSize;
$size_data["mg-info"] = "<1";


$arg_suffix = "";
$run_suffix = "";
$file_types = array(
    "protein" => "q-prot$arg_suffix",
    "cluster" => "q-clust$arg_suffix",
    "protein_norm" => "q-prot$arg_suffix-n",
    "cluster_norm" => "q-clust$arg_suffix-n",
    "protein_genome_norm" => "q-prot$arg_suffix-gn",
    "cluster_genome_norm" => "q-clust$arg_suffix-gn",
    "protein_mean" => "q-prot$arg_suffix-mean",
    "cluster_mean" => "q-clust$arg_suffix-mean",
    "protein_norm_mean" => "q-prot$arg_suffix-n-mean",
    "cluster_norm_mean" => "q-clust$arg_suffix-n-mean",
    "protein_genome_norm_mean" => "q-prot$arg_suffix-gn-mean",
    "cluster_genome_norm_mean" => "q-clust$arg_suffix-gn-mean",
    "ssn" => "ssn-q",
    "ssn_zip" => "ssn-q-zip",
    "cdhit" => "cdhit",
    "markers" => "markers",
    "meta-cl-size" => "meta-cl-size",
    "meta-sp-cl" => "meta-sp-cl",
    "meta-sp-si" => "meta-sp-si",
    "mg-info" => "q-mg-info",
);
$html_labels = array(
    "protein" => "Protein abundance data $run_suffix (median)",
    "cluster" => "Cluster abundance data $run_suffix (median)",
    "protein_norm" => "Normalized protein abundance data $run_suffix (median)",
    "cluster_norm" => "Normalized cluster abundance data $run_suffix (median)",
    "protein_genome_norm" => "Average genome size (AGS) normalized protein abundance data $run_suffix (median)",
    "cluster_genome_norm" => "Average genome size (AGS) normalized cluster abundance data $run_suffix (median)",
    "protein_mean" => "Protein abundance data $run_suffix (mean)",
    "cluster_mean" => "Cluster abundance data $run_suffix (mean)",
    "protein_norm_mean" => "Normalized protein abundance data $run_suffix (mean)",
    "cluster_norm_mean" => "Normalized cluster abundance data $run_suffix (mean)",
    "protein_genome_norm_mean" => "Average genome size (AGS) normalized protein abundance data $run_suffix (mean)",
    "cluster_genome_norm_mean" => "Average genome size (AGS) normalized cluster abundance data $run_suffix (mean)",
    "ssn" => "SSN with quantify results",
    "ssn_zip" => "SSN with quantify results (ZIP)",
    "cdhit" => "CD-HIT ShortBRED families by cluster",
    "markers" => "ShortBRED marker data",
    "meta-cl-size" => "Cluster sizes",
    "meta-sp-cl" => "SwissProt annotations by cluster",
    "meta-sp-si" => "SwissProt annotations by singletons",
    "mg-info" => "Description of selected metagenomes",
);

$dl_ssn_items = array("ssn", "ssn_zip", "mg-info");
$dl_misc_items = array("cdhit", "markers");
#$dl_misc_items = array("cdhit", "markers", "meta-cl-size", "meta-sp-cl", "meta-sp-si");
#$dl_median_items = array("protein", "cluster", "protein_norm", "cluster_norm", "protein_genome_norm", "cluster_genome_norm");
#$dl_mean_items = array("protein_mean", "cluster_mean", "protein_norm_mean", "cluster_norm_mean", "protein_genome_norm_mean", "cluster_genome_norm_mean");
$dl_median_items = array("protein", "cluster", "protein_genome_norm", "cluster_genome_norm");
$dl_mean_items = array("protein_mean", "cluster_mean", "protein_genome_norm_mean", "cluster_genome_norm_mean");


$table_string = $table->as_string();

function make_results_row($id_query_string, $arg, $btn_text, $size_data, $label) {
    $link_fn = function($arg, $text) use ($id_query_string) {
        return "<a href='download_files.php?type=$arg&$id_query_string'><button class='mini'>$text</button></a>";
    };

    $link_html = "";
    $size_html = "";
    if (is_array($arg)) {
        for ($i = 0; $i < count($arg); $i++) {
            $link_html .= "                " . $link_fn($arg[$i], $btn_text[$i]) . "\n";
        }
        if (isset($size_data))
            $size_html = implode(" / ", array_map(function($a) { return "$a MB"; }, $size_data));
    } else {
        $link_html = "                " . $link_fn($arg, $btn_text) . "\n";
        $size_html = $size_data . " MB";
    }

    echo <<<HTML
        <tr>
            <td style='text-align:center;'>
                $link_html
            </td>
            <td>$label</td>
HTML;
    if ($size_html)
        echo "             <td style='text-align:center;'>$size_html</td>\n";
    echo "        </tr>\n";
}


function outputResultsTable($items, $id_query_string, $size_data, $file_types, $html_labels) {

    foreach ($items as $type) {
        if (!is_array($type) && !isset($size_data[$type]))
            continue;
        if (is_array($type) && count($type) != 2)
            continue;

        echo <<<HTML
        <tr>
            <td style='text-align:center;'>
HTML;

        if (is_array($type)) { # assume we're joining an uncompressed and zipped into one line
            $label = $html_labels[$type[0]];
            $arg1 = $file_types[$type[0]];
            $arg2 = $file_types[$type[1]];
            echo "                <a href='download_files.php?type=$arg1&$id_query_string'><button class='mini'>Download</button></a>\n";
            echo "                <a href='download_files.php?type=$arg2&$id_query_string'><button class='mini'>Download (ZIP)</button></a>\n";
        } else {
            $arg = $file_types[$type];
            $label = $html_labels[$type];
            echo "                <a href='download_files.php?type=$arg&$id_query_string'><button class='mini'>Download</button></a>\n";
        }
    
        echo <<<HTML
            </td>
            <td>$label</td>
        </tr>
HTML;
    }
}

?>

