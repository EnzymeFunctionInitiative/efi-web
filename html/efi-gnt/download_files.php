<?php require_once(__DIR__."/../../init.php"); 
use \efi\gnt\settings;
use \efi\gnt\gnn;
use \efi\gnt\diagram_data_file;
use \efi\gnt\diagram_jobs;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\send_file;


// This is for GNDs only


$is_example = example_config::is_example();

$is_error = false;
$db_file = "";
$arrows = NULL;
$is_gnn = false;
$gnd_output_dir = settings::get_rel_output_dir();

$gnn_id = sanitize::validate_id("gnn-id", sanitize::GET);
$direct_id = sanitize::validate_id("direct-id", sanitize::GET);
$upload_id = sanitize::validate_id("upload-id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$type = sanitize::get_sanitize_string("type");

$id = 0;

if ($gnn_id !== false) {
    $id = $gnn_id;
    $gnn = new gnn($db, $gnn_id, $is_example);
    $is_gnn = true;

    if ($gnn->get_key() != $key) {
        $is_error = true;
    }
    elseif ($gnn->is_expired()) {
        $is_error = true;
    }

    $db_file = $gnn->get_diagram_data_file();
    if (!file_exists($db_file))
        $db_file = $gnn->get_diagram_data_file_legacy();
    $arrows = $gnn; // gnn class has shared API to diagram_data_file class for the purposes of this functionality
}
elseif ($direct_id !== false) {
    $id = $direct_id;
    $arrows = get_arrow_db($db, $direct_id, $key, $is_example);
    $db_file = $arrows->get_diagram_data_file();
    $gnd_output_dir = settings::get_rel_diagram_output_dir();
}
elseif ($upload_id !== false) {
    $id = $upload_id;
    $arrows = get_arrow_db($db, $upload_id, $key, $is_example);
    $db_file = $arrows->get_diagram_data_file();
    $gnd_output_dir = settings::get_rel_diagram_output_dir();
}
else {
    $is_error = true;
}

if ($is_error) {
    error404();
}

if (isset($type)) {
    if ($type == "data-file") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "{$id}_{$gnn_name}.sqlite";
        send_file::send($db_file, $dl_filename);
        exit(0);
    } elseif ($arrows === NULL) {
        $is_error = true;
    } elseif ($is_gnn == false && $type == "uniprot") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "{$id}_{$gnn_name}_UniProt_IDs.txt";
        $ids = $arrows->get_uniprot_ids();
        $content = "UniProt ID\tQuery ID\n";
        foreach ($ids as $upId => $otherId) {
            $content .= "$upId\t$otherId\n";
        }
        send_file::send_text($content, $dl_filename, send_file::SEND_FILE_BINARY);
        exit(0);
    } elseif ($is_gnn == false && $type == "unmatched") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "{$id}_{$gnn_name}_Unmatched_IDs.txt";
        $ids = $arrows->get_unmatched_ids();
        $content = implode("\n", $ids);
        send_file::send_text($content, $dl_filename, send_file::SEND_FILE_BINARY);
        exit(0);
    } elseif ($is_gnn == false && $type == "blast") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "{$id}_{$gnn_name}_BLAST_Sequence.txt";
        $content = $arrows->get_blast_sequence();
        send_file::send_text($content, $dl_filename, send_file::SEND_FILE_BINARY);
        exit(0);
    } elseif ($type == "bigscape") {
        $cluster_file = $arrows->get_bigscape_cluster_file();
        if ($cluster_file !== FALSE) {
            $gnn_name = $arrows->get_gnn_name();
            $dl_filename = "{$id}_{$gnn_name}_BiG-SCAPE_clusters.txt";
            send_file::send($cluster_file, $dl_filename);
            exit(0);
        }
    } else {
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}











function get_arrow_db($db, $id, $key, $is_example) {
    //TODO: handle is_example
    $arrows = new diagram_data_file($db, $id, $is_example);
    $dkey = diagram_jobs::get_key($db, $id, $is_example);
    $time_comp = diagram_jobs::get_time_completed($db, $id);

    if ($key === false || $key != $dkey) {
        $is_error = true;
    }
    elseif (!$arrows->is_loaded()) {
        $is_error = true;
    }
    elseif ($time_comp === false || time() < $time_comp + settings::get_retention_days()) {
        $is_error = true;
    }

    return $arrows;
}

