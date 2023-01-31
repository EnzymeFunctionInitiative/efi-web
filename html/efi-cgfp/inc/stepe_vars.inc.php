<?php 
require_once(__DIR__."/../../../init.php");

use \efi\table_builder;
use \efi\cgfp\settings;
use \efi\cgfp\result_table;
use \efi\file_types;


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

$size_data = array(
    file_types::FT_sbq_ssn => $job_obj->get_file_size(file_types::FT_sbq_ssn),
    file_types::FT_sbq_protein_abundance_median => $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_median),
    file_types::FT_sbq_cluster_abundance_median => $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_median),
    file_types::FT_sbq_protein_abundance_norm_median => $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_norm_median),
    file_types::FT_sbq_cluster_abundance_norm_median => $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_norm_median),
    file_types::FT_sb_cdhit => $job_obj->get_file_size(file_types::FT_sb_cdhit),
    file_types::FT_sb_markers => $job_obj->get_file_size(file_types::FT_sb_markers),
);

$gn_file = $job_obj->get_file_path(file_types::FT_sbq_cluster_abundance_genome_norm_median);
if (file_exists($gn_file)) {
    $size_data[file_types::FT_sbq_protein_abundance_genome_norm_median] = $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_genome_norm_median);
    $size_data[file_types::FT_sbq_cluster_abundance_genome_norm_median] = $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_genome_norm_median);
}


$mean_cluster_file = $job_obj->get_file_path(file_types::FT_sbq_cluster_abundance_mean);
$mean_gn_file = $job_obj->get_file_path(file_types::FT_sbq_cluster_abundance_genome_norm_mean);

if (file_exists($mean_cluster_file)) {
    $size_data[file_types::FT_sbq_protein_abundance_mean] = $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_mean);
    $size_data[file_types::FT_sbq_cluster_abundance_mean] = $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_mean);
    $size_data[file_types::FT_sbq_protein_abundance_norm_mean] = $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_norm_mean);
    $size_data[file_types::FT_sbq_cluster_abundance_norm_mean] = $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_norm_mean);
    if (file_exists($mean_gn_file)) {
        $size_data[file_types::FT_sbq_protein_abundance_genome_norm_mean] = $job_obj->get_file_size(file_types::FT_sbq_protein_abundance_genome_norm_mean);
        $size_data[file_types::FT_sbq_cluster_abundance_genome_norm_mean] = $job_obj->get_file_size(file_types::FT_sbq_cluster_abundance_genome_norm_mean);
    }
}

$cluster_file_size = $job_obj->get_file_size(file_types::FT_sb_meta_cluster_sizes);
$sp_cluster_file_size = $job_obj->get_file_size(file_types::FT_sb_meta_sp_clusters);
$sp_single_file_size = $job_obj->get_file_size(file_types::FT_sb_meta_cp_sing);
if ($cluster_file_size)
    $size_data[file_types::FT_sb_meta_cluster_sizes] = $cluster_file_size;
if ($sp_cluster_file_size)
    $size_data[file_types::FT_sb_meta_sp_clusters] = $sp_cluster_file_size;
if ($sp_single_file_size)
    $size_data[file_types::FT_sb_meta_cp_sing] = $sp_single_file_size;
$size_data[file_types::FT_sbq_meta_info] = "<1";





$table_string = $table->as_string();

$res_table = new result_table($id_query_string, $size_data);


