<?php


function get_realtime_params($db, $P) {
    $P->id_key_query_string = "mode=rt";
    $P->gnn_name_text = "A";
    $P->window_title = "";
    $P->is_realtime_job = true;
    $P->gnn_id = -1;
    $P->gnn_key = "";
    $ids = isset($_GET["rt-ids"]) ? $_GET["rt-ids"] : "";
    if (preg_match("/^[A-Z0-9\.,]+$/i", $ids)) {
        $P->ids = implode("\n", explode(",", $ids));
    }

    return true;
}


function get_gnn_params($db, $P) {
    $P->gnn_key = $_GET["key"];
    $P->gnn_id = $_GET["gnn-id"];

    if ($P->is_example)
        $gnn = new gnn_example($db, $P->gnn_id);
    else
        $gnn = new gnn($db, $P->gnn_id);
    $P->cooccurrence = $gnn->get_cooccurrence();
    $P->nb_size = $gnn->get_size();
    $P->max_nb_size = $gnn->get_max_neighborhood_size();
    $P->gnn_name = $gnn->get_filename();
    $dot_pos = strpos($P->gnn_name, ".");
    $P->gnn_name = substr($P->gnn_name, 0, $dot_pos);
    
    if ($gnn->get_key() != $P->gnn_key) {
        return false;
    }
    elseif ($gnn->is_expired()) {
        return false;
        //error_404("That job has expired and doesn"t exist anymore.");
    }

    if ($P->is_bigscape_enabled)
        $P->bigscape_type = DiagramJob::GNN;

    $P->id_key_query_string = "gnn-id=$P->gnn_id&key=$P->gnn_key";
    if ($P->is_example)
        $P->id_key_query_string .= "&x=1";
    $P->gnn_name_text = "GNN <i>$P->gnn_name</i>";
    $P->window_title = " for GNN $P->gnn_name (#$P->gnn_id)";

    return true;
}


function get_upload_params($db, $P) {
    $P->gnn_key = $_GET["key"];
    $P->gnn_id = $_GET["gnn-id"];

    $arrows = new diagram_data_file($gnn_id);
    $key = diagram_jobs::get_key($db, $gnn_id);

    if ($P->gnn_key != $key) {
        return false;
    }
    elseif (!$arrows->is_loaded()) {
        return false;
        #error_404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $gnn_id");
    }

    $P->gnn_name = $arrows->get_gnn_name();
    $P->cooccurrence = $arrows->get_cooccurrence();
    $P->nb_size = $arrows->get_neighborhood_size();
    $P->max_nb_size = $arrows->get_max_neighborhood_size();
    $P->is_direct_job = $arrows->is_direct_job();

    if ($P->is_bigscape_enabled)
        $P->bigscape_type = DiagramJob::Uploaded;

    $P->id_key_query_string = "upload-id=$P->gnn_id&key=$P->gnn_key";
    $P->is_uploaded_diagram = true;
    $P->gnn_name_text = "filename <i>$P->gnn_name</i>";
    $P->window_title = " for uploaded filename $P->gnn_name";

    return true;
}


function get_direct_params($db, $P) {
    $validated = false;
    $arrows = false;
    $rs_id = "";
    $rs_ver = "";
    $P->gnn_key = $_GET["key"];
    $query_type = "";
    if (isset($_GET["rs-id"])) {
        $P->gnn_id = -1;
        $rs_id = $_GET["rs-id"];
        $rs_ver = $_GET["rs-ver"];
        $gnd_file = functions::validate_direct_gnd_file($rs_id, $rs_ver, $P->gnn_key);
        if ($gnd_file !== false) {
            $arrows = new direct_gnd_file($gnd_file);
            $validated = true;
        }
        $query_type = "rs-id";
        $P->is_superfamily_job = true;
    } else {
        $P->gnn_id = $_GET["direct-id"];

        $key = diagram_jobs::get_key($db, $P->gnn_id);
        $validated = $P->gnn_key == $key ? true : false;

        if ($validated)
            $arrows = new diagram_data_file($P->gnn_id);
        $query_type = "direct-id";
    }

    if (!$validated) {
        return false;
    }
    elseif (!$arrows->is_loaded()) {
        error_log($arrows->get_message());
        return false;
        //error_404("Oops, something went wrong. Please send us an e-mail and mention the following diagnostic code: $P->gnn_id");
    }

    $P->gnn_name = $arrows->get_gnn_name();
    $P->is_direct_job = true;
    $P->is_blast = $arrows->is_job_type_blast();
    $P->blast_seq = $arrows->get_blast_sequence();
    $P->job_type_text = $arrows->get_verbose_job_type();;
    $P->nb_size = $arrows->get_neighborhood_size();
    $P->max_nb_size = $arrows->get_max_neighborhood_size();

    if ($P->is_bigscape_enabled)
        $P->bigscape_type = DiagramJob::Uploaded;

    $unmatched_ids = array();

    if (!$P->is_superfamily_job) {
        $uniprot_ids = $arrows->get_uniprot_ids();
        #for ($i = 0; $i < count($uniprot_ids); $i++) {
        foreach ($uniprot_ids as $upId => $otherId) {
            if ($upId == $otherId)
                $P->uniprot_id_modal_text .= "<tr><td>$upId</td><td></td></tr>";
            else
                $P->uniprot_id_modal_text .= "<tr><td>$upId</td><td>$otherId</td></tr>";
        }
        $unmatched_ids = $arrows->get_unmatched_ids();
        for ($i = 0; $i < count($unmatched_ids); $i++) {
            $P->unmatched_id_modal_text .= "<div>" . $unmatched_ids[$i] . "</div>";
        }
    }

    $P->has_unmatched_ids = count($unmatched_ids) > 0;


    $query_id = $rs_id ? $rs_id : $P->gnn_id;
    $key_query = "&key=$P->gnn_key";
    $ver_query = $rs_id ? "&rs-ver=$rs_ver" : "";
    $P->id_key_query_string = "$query_type=$query_id$key_query$ver_query";
    if ($P->gnn_name) {
        $P->gnn_name_text = "<i>$P->gnn_name</i>";
        $P->window_title = " for $P->gnn_name (#$P->gnn_id)";
    } else if ($P->is_superfamily_job) {
        $cluster_name = ucfirst($rs_id);
        $P->gnn_name_text = "<i>$cluster_name</i>";
        $P->window_title = " for $cluster_name";
    } else {
        $P->gnn_name_text = "job #$P->gnn_id";
        $P->window_title = " for job #$P->gnn_id";
    }

    return true;
}


