<?php
require_once(__DIR__ . "/../../init.php");
//require_once(__DIR__."/../../conf/settings_paths.inc.php");
//require_once(__EST_DIR__."/includes/main.inc.php");
//require_once(__EST_DIR__."/libs/input.class.inc.php");
//require_once(__EST_DIR__."/libs/user_jobs.class.inc.php");
//require_once(__EST_DIR__."/libs/job_factory.class.inc.php");

use \efi\est\functions;
use \efi\est\user_jobs;
use \efi\global_settings;
use \efi\user_auth;


$result['id'] = 0;
$result['MESSAGE'] = "";
$result['RESULT'] = 0;

$input = new efi\est\input_data;
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

#$test = "";
#foreach($_POST as $var) {
#    $test .= " " . $var;
#}

if (isset($_POST['email'])) {
    $input->email = $_POST['email'];
} else {
    if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie())
        $input->email = user_auth::get_email_from_token($db, user_auth::get_user_token());
}

$num_job_limit = global_settings::get_num_job_limit();
$is_job_limited = user_jobs::check_for_job_limit($db, $input->email);

$option = $_POST['option_selected'];

if (!isset($_POST['submit'])) {
    $result["MESSAGE"] = "Form is invalid.";
} elseif (!isset($input->email) || !$input->email) {
    $result["MESSAGE"] = "Please enter an e-mail address.";
} elseif ($option != "colorssn" && $option != "cluster" && $option != "nc" && $option != "cr" && (!isset($_POST['job-name']) || !$_POST['job-name'])) {
    $result["MESSAGE"] = "Job name is required.";
} elseif ($is_job_limited) {
    $result["MESSAGE"] = "Due to finite computational resource constraints, you can only have $num_job_limit active or pending jobs within a 24 hour period.  Please try again when some of your jobs have completed.";
} elseif (isset($_POST['blast_input']) && !preg_match('/\S/', $_POST['blast_input'])) {
    $result["MESSAGE"] = "Please enter a valid BLAST sequence.";
} else {
    $result['RESULT'] = true;

    #foreach ($_POST as &$var) {
    #    $var = trim(rtrim($var));
    #}
    $message = "";
    
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
    if (array_key_exists('db-mod', $_POST))
        $input->db_mod = $_POST['db-mod'];
    if (array_key_exists('cpu-x2', $_POST) && global_settings::advanced_options_enabled())
        $input->cpu_x2 = $_POST['cpu-x2'] == "true" ? true : false;
    $input->exclude_fragments = (isset($_POST['exclude-fragments']) && $_POST['exclude-fragments'] == "true") ? true : false;

    switch($option) {
        //Option A - BLAST Input
        case 'A':
            $blast = new efi\est\blast($db);

            if (array_key_exists('families_input', $_POST))
                $input->families = $_POST['families_input'];
            $input->blast_evalue = $_POST['blast_evalue'];
            $input->field_input = $_POST['blast_input'];
            $input->max_seqs = $_POST['blast_max_seqs'];
            $input->blast_db_type = $_POST['blast_db_type'];
            if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true") {
                if (isset($_POST['families_uniref_ver']) && $_POST['families_uniref_ver'])
                    $input->uniref_version = $_POST['families_uniref_ver'];
                else
                    $input->uniref_version = "90";
            }

            if (!isset($_POST['evalue']))
                $input->evalue = $input->blast_evalue; // in case we don't have family code enabled
            
            $result = $blast->create($input);
            break;
    
        //Option B - Pfam/InterPro
        case 'B':
        case 'E':
            $generate = new efi\est\generate($db);
            
            $input->families = $_POST['families_input'];
            $input->domain = $_POST['domain'];
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
            if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true") {
                if (isset($_POST['families_uniref_ver']) && $_POST['families_uniref_ver'])
                    $input->uniref_version = $_POST['families_uniref_ver'];
                else
                    $input->uniref_version = "90";
            }
            if (isset($_POST['pfam_min_seq_len']) && is_numeric($_POST['pfam_min_seq_len']))
                $input->min_seq_len = $_POST['pfam_min_seq_len'];
            if (isset($_POST['pfam_max_seq_len']) && is_numeric($_POST['pfam_max_seq_len']))
                $input->max_seq_len = $_POST['pfam_max_seq_len'];
            if ($input->domain && isset($_POST["domain_region"]) && $_POST["domain_region"])
                $input->domain_region = $_POST["domain_region"];
            
            $result = $generate->create($input);
            break;
    
        //Option C - Fasta Input
        case 'C':
        //Option D - accession list
        case 'D':
        //Option color SSN
        case 'colorssn':
        case 'cluster':
        case 'nc':
        case 'cr':
            $input->seq_id = 1;

            if (isset($_FILES['file']) && $_FILES['file']['error'] === "")
                $_FILES['file']['error'] = 4;
    
            if ((isset($_FILES['file']['error'])) && ($_FILES['file']['error'] !== 0)) {
                $result['MESSAGE'] = "Error Uploading File: " . efi\est\functions::get_upload_error($_FILES['file']['error']);
                $result['RESULT'] = false;
            }
            else {
                if (isset($_POST['families_use_uniref']) && $_POST['families_use_uniref'] == "true") {
                    if (isset($_POST['families_uniref_ver']) && $_POST['families_uniref_ver'])
                        $input->uniref_version = $_POST['families_uniref_ver'];
                    else
                        $input->uniref_version = "90";
                }
                if (isset($_POST['accession_seq_type']) && $_POST['accession_seq_type'] != "uniprot") {
                    if ($_POST['accession_seq_type'] == "uniref50")
                        $input->uniref_version = "50";
                    else
                        $input->uniref_version = "90";
                }
                if ($option == "B" || $option == "D") {
                    if (isset($_POST["domain"]) && $_POST["domain"])
                        $input->domain = $_POST["domain"];
                    if (isset($_POST["domain_region"]) && $_POST["domain_region"])
                        $input->domain_region = $_POST["domain_region"];
                }

                if ($option == "C" || $option == "E") {
                    $useFastaHeaders = $_POST['fasta_use_headers'];
                    $includeAllSeq = isset($_POST['include-all-seq']) && $_POST['include-all-seq'] === "true";
                    $obj = new efi\est\fasta($db, 0, $useFastaHeaders == "true" ? "E" : "C");
                    $input->field_input = $_POST['fasta_input'];
                    $input->families = $_POST['families_input'];
                    $input->include_all_seq = $includeAllSeq;
                } else if ($option == "D") {
                    $obj = new efi\est\accession($db);
                    $input->field_input = $_POST['accession_input'];
                    $input->families = $_POST['families_input'];
                    if (isset($_POST["domain_family"]) && $_POST["domain_family"])
                        $input->domain_family = $_POST["domain_family"];
                } else if ($option == "colorssn" || $option == "cluster" || $option == "nc" || $option == "cr") {
                    if (isset($_POST['ssn-source-id']))
                        $input->color_ssn_source_id = $_POST['ssn-source-id'];
                    if (isset($_POST['ssn-source-idx']))
                        $input->color_ssn_source_idx = $_POST['ssn-source-idx'];
                    $input->extra_ram = (isset($_POST['extra_ram']) && is_numeric($_POST['extra_ram'])) ? $_POST['extra_ram'] : false;
                    $input->efiref = isset($_POST['efiref']) ? $_POST['efiref'] : "";
                    $input->skip_fasta = (isset($_POST['skip_fasta']) && $_POST['skip_fasta'] == "true");
                    if (isset($_POST['color-ssn-source-color-id']) && is_numeric($_POST['color-ssn-source-color-id']))
                        $input->color_ssn_source_color_id = $_POST['color-ssn-source-color-id'];
                    if ($option == "cluster") {
                        $obj = new efi\est\cluster_analysis($db);
                        $input->make_hmm = (isset($_POST['make-hmm']) && $_POST['make-hmm']) ? $_POST['make-hmm'] : "";
                        $input->aa_threshold = (isset($_POST['aa-threshold']) && $_POST['aa-threshold']) ? $_POST['aa-threshold'] : 0.8;
                        $input->hmm_aa = (isset($_POST['hmm-aa']) && $_POST['hmm-aa']) ? $_POST['hmm-aa'] : "";
                        $input->min_seq_msa = (isset($_POST['min-seq-msa']) && $_POST['min-seq-msa']) ? $_POST['min-seq-msa'] : 0;
                        $input->max_seq_msa = (isset($_POST['max-seq-msa']) && $_POST['max-seq-msa']) ? $_POST['max-seq-msa'] : 0;
                    } else if ($option == "nc") {
                        $obj = new efi\est\nb_conn($db);
                    } else if ($option == "cr") {
                        $input->ascore = $_POST['ascore'];
                        $obj = new efi\est\conv_ratio($db);
                    } else {
                        $obj = new efi\est\colorssn($db);
                    }
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

?>
