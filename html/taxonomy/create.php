<?php
require_once(__DIR__ . "/../../init.php");

use \efi\global_settings;
use \efi\user_auth;
use \efi\est\functions;
use \efi\est\user_jobs;
use \efi\est\input_data;


$result['id'] = 0;
$result['MESSAGE'] = "";
$result['RESULT'] = 0;

$input = new input_data;
$input->is_debug = !isset($_SERVER["HTTP_HOST"]);
$input->is_taxonomy_job = true;

// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
if ($input->is_debug) {
    parse_str($argv[1], $_POST);
    if (isset($argv[2])) {
        $file_array = array();
        parse_str($argv[2], $file_array);
        foreach ($file_array as $parm => $file) {
            $fname = basename($file);
            $_FILES[$parm]['tmp_name'] = $file;
            $_FILES[$parm]['name'] = $fname;
            $_FILES[$parm]['error'] = 0;
        }
    }
}


if (isset($_POST['email'])) {
    $input->email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
} else {
    if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie())
        $input->email = user_auth::get_email_from_token($db, user_auth::get_user_token());
}

$num_job_limit = global_settings::get_num_job_limit();
$is_job_limited = user_jobs::check_for_job_limit($db, $input->email);

if (!isset($_POST['submit'])) {
    $result["MESSAGE"] = "Form is invalid.";
} elseif (!isset($input->email) || !$input->email) {
    $result["MESSAGE"] = "Please enter an e-mail address.";
} elseif ($is_job_limited) {
    $result["MESSAGE"] = "Due to finite computational resource constraints, you can only have $num_job_limit active or pending jobs within a 24 hour period.  Please try again when some of your jobs have completed.";
} else {
    $result['RESULT'] = true;

    $message = "";

    $option = $_POST['option_selected'];

    if (array_key_exists('job-name', $_POST))
        $input->job_name = $_POST['job-name'];
    if (array_key_exists('db-mod', $_POST))
        $input->db_mod = $_POST['db-mod'];
    $input->exclude_fragments = (isset($_POST['exclude-fragments']) && $_POST['exclude-fragments'] == "true") ? true : false;
    $input->tax_search = isset($_POST['tax_search']) ? parse_tax_search($_POST['tax_search']) : "";
    $input->is_taxonomy_job = true;
    $input->fraction = 1;

    switch($option) {
        case 'B':
            $generate = new efi\est\family($db);
            
            $input->families = $_POST['families_input'];

            $result = $generate->create($input);
            break;
        
        //Option C - Fasta Input
        case 'C':
        //Option D - accession list
        case 'D':
            if (isset($_FILES['file']) && $_FILES['file']['error'] === "")
                $_FILES['file']['error'] = 4;
    
            if ((isset($_FILES['file']['error'])) && ($_FILES['file']['error'] !== 0)) {
                $result['MESSAGE'] = "Error Uploading File: " . efi\est\functions::get_upload_error($_FILES['file']['error']);
                $result['RESULT'] = false;
            }
            else {
                if ($option == "C") {
                    $useFastaHeaders = true;
                    $includeAllSeq = isset($_POST['include-all-seq']) && $_POST['include-all-seq'] === "true";
                    $obj = new efi\est\fasta($db, 0, $useFastaHeaders ? "E" : "C");
                    $input->field_input = $_POST['fasta_input'];
                    $input->include_all_seq = $includeAllSeq;
                } else if ($option == "D") {
                    $obj = new efi\est\accession($db);
                    $input->field_input = $_POST['accession_input'];
                }

                if (isset($_FILES['file'])) {
                    $input->tmp_file = $_FILES['file']['tmp_name'];
                    $input->uploaded_filename = $_FILES['file']['name'];
                }
                $result = $obj->create($input);
            }
    
            break;
            
        default:
            $result['RESULT'] = false;
            $result['MESSAGE'] = "You need to select one of the above options.";
    
    }
}


if ($input->is_debug) {
    print "JSON: ";
}

$returnData = array('valid'=>$result['RESULT'],
                    'id'=>$result['id'],
                    'message'=>$result['MESSAGE']);


// This resets the expiration date of the cookie so that frequent users don't have to login in every X days as long
// as they keep using the app.
if (global_settings::get_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $cookieInfo = user_jobs::get_cookie_shared(user_jobs::get_user_token());
    $returnData["cookieInfo"] = $cookieInfo;
}

echo json_encode($returnData);

if ($input->is_debug) {
    print "\n\n";
}




function parse_tax_search($search_array) {
    if (!isset($search_array) || !is_array($search_array))
        return false;
    $store = array();
    $accepted = array("superkingdom" => true, "kingdom" => true, "phylum" => true, "class" => true, "order" => true, "family" => true, "genus" => true, "species" => true);
    for ($i = 0; $i < count($search_array); $i++) {
        $parts = explode(":", $search_array[$i]);
        $cat = strtolower($parts[0]);
        $text = strtolower($parts[1]);
        $text = preg_replace("/[^a-zA-Z0-9\.\- ]/", "", $text);
        $text = preg_replace("/^\s*(.*?)\s*$/", '$1', $text);
        if (isset($accepted[$cat]))
            array_push($store, $cat . ":" . $text);
    }
    return $store;
}


