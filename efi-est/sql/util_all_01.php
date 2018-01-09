<?php
chdir(dirname(__FILE__));
require_once '../includes/main.inc.php';


upgrade_families($db, __RUNNING__);
upgrade_families($db, __FAILED__);
upgrade_families($db, __NEW__);
upgrade_families($db, __FINISH__);

upgrade_accessions($db, __RUNNING__);
upgrade_accessions($db, __FAILED__);
upgrade_accessions($db, __NEW__);
upgrade_accessions($db, __FINISH__);

upgrade_blasts($db, __RUNNING__);
upgrade_blasts($db, __FAILED__);
upgrade_blasts($db, __NEW__);
upgrade_blasts($db, __FINISH__);

upgrade_fasta($db, __RUNNING__);
upgrade_fasta($db, __FAILED__);
upgrade_fasta($db, __NEW__);
upgrade_fasta($db, __FINISH__);

upgrade_colorssn($db, __RUNNING__);
upgrade_colorssn($db, __FAILED__);
upgrade_colorssn($db, __NEW__);
upgrade_colorssn($db, __FINISH__);


















function upgrade_colorssn($db, $status) {
    $running_jobs = functions::get_colorssns($db, $status);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $id = $job['generate_id'];
            $obj = new colorssn($db, $id);

            $data = array();
            $data['generate_fasta_file'] = $obj->get_uploaded_filename();

            update_params($data, $id, $db);
            update_seq_counts($obj, $id, $db);
        }
    }
}

function upgrade_blasts($db, $status) {
    $running_jobs = functions::get_blasts($db, $status);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $id = $job['generate_id'];
            $obj = new blast($db, $id);

            $data = array();
            $data['generate_evalue'] = $obj->get_evalue();
            $data['generate_fraction'] = $obj->get_fraction();
            $data["generate_blast_max_sequence"] = $obj->get_submitted_max_sequences();
            $data["generate_blast"] = $obj->get_blast_input();

            update_params($data, $id, $db);
            update_seq_counts($obj, $id, $db);
        }
    }
}

function upgrade_fasta($db, $status) {
    $running_jobs = functions::get_fastas($db, $status);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $id = $job['generate_id'];
            $obj = new fasta($db, $id);

            $data = array();
            $data = get_fam_shared($data, $obj);
            $data['generate_fasta_file'] = $obj->get_uploaded_filename();

            update_params($data, $id, $db);
            update_seq_counts($obj, $id, $db);
        }
    }
}

function upgrade_accessions($db, $status) {
    $running_jobs = functions::get_accessions($db, $status);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $id = $job['generate_id'];
            $obj = new accession($db, $id);

            $data = array();
            $data = get_fam_shared($data, $obj);
            $data['generate_fasta_file'] = $obj->get_uploaded_filename();

            update_params($data, $id, $db);
            update_seq_counts($obj, $id, $db);
        }
    }
}

function upgrade_families($db, $status) {
    $running_jobs = functions::get_families($db, $status);
    if (count($running_jobs)) {
        foreach ($running_jobs as $job) {
            $id = $job['generate_id'];
            $obj = new generate($db, $id);
    
            $data = array();
            $data['generate_domain'] = $obj->get_domain();
            $data['generate_sequence_identity'] = $obj->get_sequence_identity();
            $data['generate_length_overlap'] = $obj->get_length_overlap();
            $data['generate_uniref_version'] = $obj->get_uniref_version();
            $data['generate_no_demux'] = $obj->get_no_demux();
            $data = get_fam_shared($data, $obj);
    
            update_params($data, $id, $db);
            update_seq_counts($obj, $id, $db);
        }
    }
}



function get_fam_shared($data, $obj) {
    $data['generate_evalue'] = $obj->get_evalue();
    $data['generate_fraction'] = $obj->get_fraction();
    $data['generate_families'] = $obj->get_families_comma();
    return $data;
}










function update_seq_counts($obj, $id, $db) {
    $data = array();
    $data['generate_num_seq'] = $obj->get_num_sequences();
    $data['generate_total_num_file_seq'] = $obj->get_total_num_file_sequences();
    $data['generate_num_matched_file_seq'] = $obj->get_num_matched_file_sequences();
    $data['generate_num_unmatched_file_seq'] = $obj->get_num_unmatched_file_sequences();
    $data['generate_num_family_seq'] = $obj->get_num_family_sequences();
    update_results($data, $id, $db);
}


function update_results($array, $id, $db) {
    $json = json_encode($array);
    $sql = "UPDATE generate SET generate_results=";
    $sql .= "'" . mysql_real_escape_string($json) . "'";
    $sql .= " WHERE generate_id='$id' LIMIT 1";
    $result = $db->non_select_query($sql);
    //echo $sql . "\n\n";
}

function update_params($array, $id, $db) {
    $json = json_encode($array);
    $sql = "UPDATE generate SET generate_params=";
    $sql .= "'" . mysql_real_escape_string($json) . "'";
    $sql .= " WHERE generate_id='$id' LIMIT 1";
    echo $id, "\n";
    $result = $db->non_select_query($sql);
    //echo $sql . "\n\n";
}


?>
