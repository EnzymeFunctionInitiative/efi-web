<?php
require_once '../includes/main.inc.php';
require_once '../libs/input.class.inc.php';
require_once '../libs/user_jobs.class.inc.php';

$result['id'] = 0;
$result['MESSAGE'] = "";
$result['RESULT'] = 0;

$input = new input_data;
$input->is_debug = !isset($_SERVER["HTTP_HOST"]);

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


$test = "";
foreach($_POST as $var) {
    $test .= " " . $var;
}

$input->email = $_POST['email'];

if (!isset($_POST['submit'])) {
    $result["MESSAGE"] = "Form is invalid.";
} elseif (!$input->email) {
    $result["MESSAGE"] = "Please enter an e-mail address.";
} else {
    $result['RESULT'] = true;

    foreach ($_POST as &$var) {
        $var = trim(rtrim($var));
    }
    $message = "";
    $option = $_POST['option_selected'];
    
    if (array_key_exists('evalue', $_POST))
        $input->evalue = $_POST['evalue'];
    if (array_key_exists('program', $_POST))
        $input->program = isset($_POST['program']) ? $_POST['program'] : "";
    if (array_key_exists('fraction', $_POST))
        $input->fraction = $_POST['fraction'];
    if (array_key_exists('job-group', $_POST))
        $input->job_group = $_POST['job-group'];
    if (array_key_exists('job-name', $_POST))
        $input->job_name = $_POST['job-name'];
    
    switch($option) {
        //Option A - Blast Input
        case 'A':
            $blast = new blast($db);

            if (array_key_exists('families_input', $_POST))
                $input->families = $_POST['families_input'];
            $input->blast_evalue = $_POST['blast_evalue'];
            $input->field_input = $_POST['blast_input'];
            $input->max_seqs = $_POST['blast_max_seqs'];
            if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true")
                $input->uniref_version = "90";

            if (!isset($_POST['evalue']))
                $input->evalue = $input->blast_evalue; // in case we don't have family code enabled
            
            $result = $blast->create($input);
            break;
    
        //Option B - PFam/Interpro
        case 'B':
        case 'E':
            $generate = new generate($db);
            
            $input->families = $_POST['families_input'];
            $input->domain = $_POST['pfam_domain'];
            if (isset($_POST['pfam_seqid']))
                $input->seq_id = $_POST['pfam_seqid'];
            if (isset($_POST['pfam_length_overlap']))
                $input->length_overlap = $_POST['pfam_length_overlap'];
            if (isset($_POST['pfam_uniref_version']))
                $input->uniref_version = $_POST['pfam_uniref_version'];
            if (isset($_POST['pfam_demux']))
                $input->no_demux = $_POST['pfam_demux'] == "true" ? true : false;
            if (isset($_POST['pfam_random_fraction']))
                $input->random_fraction = $_POST['pfam_random_fraction'] == "true" ? true : false;
            if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true")
                $input->uniref_version = "90";
    
            $result = $generate->create($input);
            break;
    
        //Option C - Fasta Input
        case 'C':
        //Option D - accession list
        case 'D':
        //Option color SSN
        case 'colorssn':
            $input->seq_id = 1;

            if (isset($_FILES['file']) && $_FILES['file']['error'] === "")
                $_FILES['file']['error'] = 4;
    
            if ((isset($_FILES['file']['error'])) && ($_FILES['file']['error'] !== 0)) {
                $result['MESSAGE'] = "Error Uploading File: " . functions::get_upload_error($_FILES['file']['error']);
                $result['RESULT'] = false;
            }
            else {
                if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true")
                    $input->uniref_version = "90";

                if ($option == "C" || $option == "E") {
                    $useFastaHeaders = $_POST['fasta_use_headers'];
                    $obj = new fasta($db, 0, $useFastaHeaders == "true" ? "E" : "C");
                    $input->field_input = $_POST['fasta_input'];
                    $input->families = $_POST['families_input'];
                } else if ($option == "D") {
                    $obj = new accession($db);
                    $input->field_input = $_POST['accession_input'];
                    $input->families = $_POST['families_input'];
                    if (isset($_POST['accession_use_uniref'])) {
                        $input->expand_homologs = $_POST['accession_use_uniref'] == "true" ? true : false;
                        if ($input->expand_homologs) {
                            $input->uniref_version = $_POST['accession_uniref_version'];
                        }
                    } else {
                        $input->expand_homologs = false;
                    }
                } else if ($option == "colorssn") {
                    $obj = new colorssn($db);
                    if (isset($_POST['ssn-source-id']))
                        $input->color_ssn_source_id = $_POST['ssn-source-id'];
                    if (isset($_POST['ssn-source-idx']))
                        $input->color_ssn_source_idx = $_POST['ssn-source-idx'];
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
if (global_settings::is_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
    $cookieInfo = user_jobs::get_cookie_shared(user_jobs::get_user_token());
    $returnData["cookieInfo"] = $cookieInfo;
}

echo json_encode($returnData);

if ($input->is_debug) {
    print "\n\n";
}

?>
