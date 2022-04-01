<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\diagram_jobs;
use \efi\gnt\user_jobs;
use \efi\gnt\DiagramJob;


$id = 0;
$key = 0;
$message = "";
$valid = 0;
$cookieInfo = "";

if (isset($_POST["option"])) {

    $opt = $_POST["option"];

    $valid = 1;

    if (!isset($_POST["email"]) || !functions::verify_email($_POST["email"])) {
        $valid = 0;
        $message .= "<br><b>Please verify your e-mail address</b>";
    }

    if ($valid) {
        $email = $_POST["email"];
        $title = isset($_POST["title"]) ? $_POST["title"] : "";
        $dbMod = isset($_POST["db-mod"]) ? $_POST["db-mod"] : "";
        $seqType = isset($_POST["seq-type"]) ? $_POST["seq-type"] : "";

        $retval = "";
        if ($opt == "a") {
            $retval = create_blast_job($db, $email, $title, $dbMod, $seqType);
        } elseif ($opt == "c") {
            $retval = create_lookup_job($db, $email, $title, "fasta", DiagramJob::FastaLookup, $dbMod, "", null);
        } elseif ($opt == "d") {
            if (isset($_POST["tax-id"]) && isset($_POST["tax-key"]) && isset($_POST["tax-tree-id"]) && isset($_POST["tax-id-type"])) {
                $taxParms = array("tax_job_id" => $_POST["tax-id"], "tax_key" => $_POST["tax-key"], "tax_tree_id" => $_POST["tax-tree-id"], "tax_id_type" => $_POST["tax-id-type"]);
            }
            $retval = create_lookup_job($db, $email, $title, "ids", DiagramJob::IdLookup, $dbMod, $seqType, $taxParms);
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




function create_blast_job($db, $email, $title, $dbMod, $seqDbType) {

    $retval = array("id" => 0, "key" => "", "valid" => false, "message" => "");

    $blast_input = functions::remove_blast_header($_POST["sequence"]);
    
    // Ignore bad values
    $seqDbType = sanitize_seq_db_type($seqDbType);

    if (!isset($_POST["evalue"]) || !functions::verify_evalue($_POST["evalue"])) {
    
        $retval["message"] = "The given e-value is invalid.";
    
    } elseif (!isset($_POST["max-seqs"]) || !functions::verify_max_seqs($_POST["max-seqs"])) {
    
        $retval["message"] = "The given maximum sequence value is invalid.";

    } elseif (!isset($_POST["nb-size"]) || !functions::verify_neighborhood_size($_POST["nb-size"])) {

        $retval["message"] = "The neighborhood size is invalid.";

    } elseif (!isset($_POST["sequence"]) || !functions::verify_blast_input($blast_input)) {

        $retval["message"] = "The BLAST sequence is not valid.";

    } else {

        $retval["valid"] = true;
        $jobInfo = diagram_jobs::create_blast_job($db, $email, $title, $_POST["evalue"], $_POST["max-seqs"], $_POST["nb-size"], $blast_input, $dbMod, $seqDbType);
    
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


function create_lookup_job($db, $email, $title, $contentField, $jobType, $dbMod, $seqDbType, $taxParms) {

    $retval = array("id" => 0, "key" => "", "valid" => false, "message" => "");

    $hasInputContent = false;
    $hasFile = false;
    $hasTax = false;

    if (isset($taxParms)) {
        // Validate
        $info = functions::get_taxonomy_job_info($db, $taxParms["tax_job_id"], $taxParms["tax_key"]);
        if ($info !== false) {
            $hasTax = true;
        }
    } else {
        $fileType = "";
        $hasInputContent = isset($_POST[$contentField]) && strlen($_POST[$contentField]) > 0;
        $hasFile = isset($_FILES['file']);

        if ($hasFile) {
            $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (isset($_FILES['file']['error']) && ($_FILES['file']['error'] != 0)) {
                $retval["message"] .= "<br><b>Error uploading file: " . functions::get_upload_error($_FILES['file']['error']) . "</b>";
                $hasFile = false;
            }
            elseif (!functions::is_valid_id_file_type($fileType)) {
                $message .= "<br><b>Invalid filetype ($fileType).  The file has to be an " . settings::get_id_diagram_file_types() . " filetype.</b>";
                $hasFile = false;
            }
        }
    }

    // Ignore bad values
    $seqDbType = sanitize_seq_db_type($seqDbType);

    if (!$hasFile && !$hasInputContent && !$hasTax) {
    
        $retval["message"] = "Either a list of IDs or a file containing a list of IDs must be uploaded.";

    } elseif (!isset($_POST["nb-size"]) || !functions::verify_neighborhood_size($_POST["nb-size"])) {

        $retval["message"] = "The neighborhood size is invalid.";

    } else {

        $retval["valid"] = true;

        if ($hasFile)
            $jobInfo = diagram_jobs::create_file_lookup_job($db, $email, $title, $_POST["nb-size"], $_FILES["file"]["tmp_name"], $_FILES["file"]["name"], $jobType, $dbMod, $seqDbType);
        else
            $jobInfo = diagram_jobs::create_lookup_job($db, $email, $title, $_POST["nb-size"], $_POST[$contentField], $jobType, $dbMod, $seqDbType, $taxParms);

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


