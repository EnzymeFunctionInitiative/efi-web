<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\diagram_jobs;
use \efi\gnt\user_jobs;
use \efi\gnt\DiagramJob;
use \efi\sanitize;


$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookieInfo = "";

$opt = sanitize::post_sanitize_string("option");

if (isset($opt)) {
    $valid = 1;

    $email = sanitize::post_sanitize_email("email");
    if (!isset($email)) {
        $valid = 0;
        $message .= "<br><b>Please verify your e-mail address</b>";
    }

    if ($valid) {
        $title = sanitize::post_sanitize_string("title");
        $db_mod = sanitize::post_sanitize_string("db-mod");
        $seqType = sanitize::post_sanitize_string("seq-type");

        $retval = "";
        if ($opt == "a") {
            $retval = create_blast_job($db, $email, $title, $db_mod, $seqType);
        } elseif ($opt == "c") {
            $retval = create_lookup_job($db, $email, $title, "fasta", DiagramJob::FastaLookup, $db_mod, "", null);
        } elseif ($opt == "d") {
            $tax_parms = null;
            $tax_id = sanitize::validate_id("tax-id", sanitize::POST);
            $tax_key = sanitize::validate_key("tax-key", sanitize::POST);
            $tax_tree_id = sanitize::post_sanitize_num("tax-tree-id");
            $tax_id_type = sanitize::post_sanitize_string("tax-id-type");
            if ($tax_id !== false && $tax_key !== false && isset($tax_tree_id) && isset($tax_id_type)) {
                $tax_parms = array("tax_job_id" => $tax_id, "tax_key" => $tax_key, "tax_tree_id" => $tax_tree_id, "tax_id_type" => $tax_id_type);
            }
            $retval = create_lookup_job($db, $email, $title, "ids", DiagramJob::IdLookup, $db_mod, $seqType, $tax_parms);
        }

        if ($retval["valid"] === false) {
            $valid = 0;
            $message .= "<br>" . $retval["message"];
            $id = "";
            $key = "";
        } else {
            $id = $retval["id"];
            $key = $retval["key"];
        }
        
        //$userObj = new user_jobs();
        //$userObj->save_user($db, $email);
        //$cookieInfo = $userObj->get_cookie();
    }
}

$returnData = array(
    "valid" => $valid,
    "id" => $id,
    "key" => $key,
    "message" => $message,
);

// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
// as they keep using the app.
if ($valid && settings::get_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $cookieInfo = user_jobs::get_cookie_shared(user_jobs::get_user_token());
    $returnData["cookieInfo"] = $cookieInfo;
}

echo json_encode($returnData);




function create_blast_job($db, $email, $title, $db_mod, $seq_db_type) {

    $retval = array("id" => 0, "key" => "", "valid" => false, "message" => "");

    $seq = sanitize::post_sanitize_seq("sequence");
    $blast_input = functions::remove_blast_header($seq);
    
    // Ignore bad values
    $seq_db_type = sanitize_seq_db_type($seq_db_type);
    $nb_size = sanitize::post_sanitize_num("nb-size");
    $evalue = sanitize::post_sanitize_num("evalue");
    $maxSeqs = sanitize::post_sanitize_num("max-seqs");

    if (!isset($evalue) || !functions::verify_evalue($evalue)) {
    
        $retval["message"] = "The given e-value is invalid.";
    
    } elseif (!isset($maxSeqs) || !functions::verify_max_seqs($maxSeqs)) {
    
        $retval["message"] = "The given maximum sequence value is invalid.";

    } elseif (!isset($nb_size) || !functions::verify_neighborhood_size($nb_size)) {

        $retval["message"] = "The neighborhood size is invalid.";

    } elseif (!isset($blast_input) || !functions::verify_blast_input($blast_input)) {

        $retval["message"] = "The BLAST sequence is not valid.";

    } else {

        $retval["valid"] = true;
        $jobInfo = diagram_jobs::create_blast_job($db, $email, $title, $evalue, $maxSeqs, $nb_size, $blast_input, $db_mod, $seq_db_type);
    
        if ($jobInfo === false) {
            $retval["message"] .= " The job was unable to be created.";
            $retval["valid"] = false;
        } else {
            $retval["id"] = $jobInfo["id"];
            $retval["key"] = $jobInfo["key"];
        }
    }

    return $retval;
}


function create_lookup_job($db, $email, $title, $content_field, $job_type, $db_mod, $seq_db_type, $tax_parms) {

    $retval = array("id" => 0, "key" => "", "valid" => false, "message" => "");

    $has_input_content = false;
    $has_file = false;
    $has_tax = false;

    if (isset($tax_parms)) {
        // Validate
        $info = functions::get_taxonomy_job_info($db, $tax_parms["tax_job_id"], $tax_parms["tax_key"]);
        if ($info !== false) {
            $has_tax = true;
        }
    } else {
        $fileType = "";
        $has_input_content = isset($_POST[$content_field]) && strlen($_POST[$content_field]) > 0;
        $has_file = isset($_FILES['file']);

        if ($has_file) {
            $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (isset($_FILES['file']['error']) && ($_FILES['file']['error'] != 0)) {
                $retval["message"] .= "<br><b>Error uploading file: " . functions::get_upload_error($_FILES['file']['error']) . "</b>";
                $has_file = false;
            }
            elseif (!functions::is_valid_id_file_type($fileType)) {
                $retval["message"] .= "<br><b>Invalid filetype ($fileType).  The file has to be an " . settings::get_valid_file_type() . " filetype.</b>";
                $has_file = false;
            }
        }
    }

    // Ignore bad values
    $seq_db_type = sanitize_seq_db_type($seq_db_type);
    $nb_size = sanitize::post_sanitize_num("nb-size");

    if (!$has_file && !$has_input_content && !$has_tax) {
    
        $retval["message"] = "Either a list of IDs or a file containing a list of IDs must be uploaded.";

    } elseif (!isset($nb_size) || !functions::verify_neighborhood_size($nb_size)) {

        $retval["message"] = "The neighborhood size is invalid.";

    } else {

        $retval["valid"] = true;

        if ($has_file) {
            $jobInfo = diagram_jobs::create_file_lookup_job($db, $email, $title, $nb_size, $_FILES["file"]["tmp_name"], $_FILES["file"]["name"], $job_type, $db_mod, $seq_db_type);
        } else {
            $content = sanitize::post_sanitize_seq($content_field);
            $jobInfo = diagram_jobs::create_lookup_job($db, $email, $title, $nb_size, $content, $job_type, $db_mod, $seq_db_type, $tax_parms);
        }

        if ($jobInfo === false) {
            $retval["message"] .= " The job was unable to be created.";
            $retval["valid"] = false;
        } else {
            $retval["id"] = $jobInfo["id"];
            $retval["key"] = $jobInfo["key"];
        }
    }

    return $retval;
}


function sanitize_seq_db_type($dbType) {
    if ($dbType == "uniprot" ||
        $dbType == "uniprot-nf" ||
        $dbType == "uniref50" ||
        $dbType == "uniref50-nf" ||
        $dbType == "uniref90" ||
        $dbType == "uniref90-nf")
    {
        return $dbType;
    } else {
        return false;
    }
}


