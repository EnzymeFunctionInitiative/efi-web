<?php
require_once(__DIR__ . "/../../init.php");
require_once(__EST_CONF_DIR__."/settings.inc.php");

use \efi\global_settings;
use \efi\user_auth;
use \efi\est\functions;
use \efi\est\user_jobs;
use \efi\est\input_data;
use \efi\sanitize;


$result["id"] = 0;
$result["MESSAGE"] = "";
$result["RESULT"] = 0;

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
            $_FILES[$parm]["tmp_name"] = $file;
            $_FILES[$parm]["name"] = $fname;
            $_FILES[$parm]["error"] = 0;
        }
    }
}


if (isset($_POST["email"])) {
    $input->email = sanitize::post_sanitize_email("email");
} else {
    if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie())
        $input->email = user_auth::get_email_from_token($db, user_auth::get_user_token());
}

$option = sanitize::post_sanitize_string("option_selected");
$job_name = sanitize::post_sanitize_string("job-name");


$is_error = false;
if (!isset($_POST["submit"])) {
    $result["MESSAGE"] = "Form is invalid.";
    $is_error = true;
} else if (!isset($input->email) || !$input->email) {
    $result["MESSAGE"] = "Please enter an e-mail address.";
    $is_error = true;
}

$num_job_limit = global_settings::get_num_job_limit();
$is_job_limited = user_jobs::check_for_job_limit($db, $input->email);


if (!$is_error && $option != "colorssn" && $option != "cluster" && $option != "nc" && $option != "cr" && !$job_name) {
    $result["MESSAGE"] = "Job name is required.";
} else if (!$is_error && $is_job_limited) {
    $result["MESSAGE"] = "Due to finite computational resource constraints, you can only have $num_job_limit active or pending jobs within a 24 hour period.  Please try again when some of your jobs have completed.";
} else if (!$is_error && isset($_POST["blast_input"]) && !preg_match("/\S/", $_POST["blast_input"])) {
    $result["MESSAGE"] = "Please enter a valid BLAST sequence.";
} else if (!$is_error) {
    $result["RESULT"] = true;

    $message = "";

    $input->job_name = $job_name;

    $input->evalue = sanitize::post_sanitize_num("evalue");
    $input->program = sanitize::post_sanitize_string("program");
    $input->program_sens = sanitize::post_sanitize_num("program-sens");
    $input->program_hits = sanitize::post_sanitize_num("program-hits");
    $input->fraction = sanitize::post_sanitize_num("fraction");
    $input->job_group = sanitize::post_sanitize_string("job-group");
    $input->db_mod = sanitize::post_sanitize_string("db-mod");
    if (global_settings::advanced_options_enabled())
        $input->cpu_x2 = sanitize::post_sanitize_flag("cpu-x2");
    if (global_settings::advanced_options_enabled())
        $input->large_mem = sanitize::post_sanitize_flag("large-mem", null);
    $input->exclude_fragments = sanitize::post_sanitize_flag("exclude-fragments");
    $input->tax_search = parse_tax_search();
    $input->tax_search_name = sanitize::post_sanitize_string("tax_name", "", "[^A-Za-z0-9_\-:\| ,]");

    $input->extra_ram = sanitize::post_sanitize_num("extra_ram", false);

    switch($option) {
        //Option A - BLAST Input
        case "A":
            $blast = new efi\est\blast($db);

            $input->blast_evalue = sanitize::post_sanitize_string("blast_evalue", "");
            $input->field_input = sanitize::post_sanitize_seq("blast_input", "");
            $input->max_seqs = sanitize::post_sanitize_string("blast_max_seqs", "");
            $input->blast_db_type = sanitize::post_sanitize_string("blast_db_type", "");
            set_family_vars($option, $input);

            if (!isset($_POST["evalue"]))
                $input->evalue = $input->blast_evalue; // in case we don't have family code enabled
            
            $result = $blast->create($input);
            break;
    
        //Option B - Pfam/InterPro
        case "B":
            $generate = new efi\est\family($db);
            
            $input->seq_id = sanitize::post_sanitize_string("pfam_seqid");
            $input->length_overlap = sanitize::post_sanitize_num("pfam_length_overlap");
            $input->no_demux = sanitize::post_sanitize_flag("pfam_demux");
            $input->random_fraction = sanitize::post_sanitize_flag("pfam_random_fraction");
            set_family_vars($option, $input);

            $result = $generate->create($input);
            break;

        case "opt_tax":
            $generate = new efi\est\taxonomy_job($db);

            set_family_vars($option, $input);

            $result = $generate->create($input);
            break;
    
        //Option C - Fasta Input
        case "C":
        //Option D - accession list
        case "D":
        //Option color SSN
        case "colorssn":
        case "cluster":
        case "nc":
        case "cr":
            $input->seq_id = 1;

            if (isset($_FILES["file"]) && $_FILES["file"]["error"] === "")
                $_FILES["file"]["error"] = 4;
    
            if ((isset($_FILES["file"]["error"])) && ($_FILES["file"]["error"] !== 0)) {
                $result["MESSAGE"] = "Error Uploading File: " . efi\est\functions::get_upload_error($_FILES["file"]["error"]);
                $result["RESULT"] = false;
            }
            else {
                $accession_seq_type = sanitize::post_sanitize_string("accession_seq_type");
                if (isset($accession_seq_type) && $accession_seq_type != "uniprot") {
                    if ($accession_seq_type === "uniref50")
                        $input->uniref_version = "50";
                    else
                        $input->uniref_version = "90";
                }
                $input->family_filter = sanitize::post_sanitize_string_relaxed("family_filter", "");

                set_family_vars($option, $input);

                if ($option === "C") {
                    $use_fasta_headers = sanitize::post_sanitize_flag("fasta_use_headers");
                    $obj = new efi\est\fasta($db, 0, $use_fasta_headers ? "E" : "C");
                    $input->field_input = sanitize::post_sanitize_seq("fasta_input", "");
                    $input->include_all_seq = sanitize::post_sanitize_flag("include-all-seq");

                } else if ($option === "D") {

                    $obj = new efi\est\accession($db);
                    $input->field_input = sanitize::post_sanitize_seq("accession_input", "");
                    $input->tax_job_id = sanitize::post_sanitize_string("accession_tax_job_id", "");
                    $input->tax_tree_id = sanitize::post_sanitize_string("accession_tax_tree_id", "");
                    $input->tax_id_type = sanitize::post_sanitize_string("accession_tax_id_type", "");
                    $input->tax_job_key = sanitize::post_sanitize_string("accession_tax_job_key", "");
                    $input->domain_family = sanitize::post_sanitize_string("domain_family");

                } else if ($option === "colorssn" || $option === "cluster" || $option === "nc" || $option === "cr") {

                    $input->color_ssn_source_id = sanitize::post_sanitize_num("ssn-source-id");
                    $input->color_ssn_source_idx = sanitize::post_sanitize_num("ssn-source-idx");
                    $input->color_ssn_source_key = sanitize::post_sanitize_string("ssn-source-key");

                    $input->efiref = sanitize::post_sanitize_string("efiref", "");
                    $input->skip_fasta = sanitize::post_sanitize_flag("skip_fasta");

                    $input->color_ssn_source_color_id = sanitize::post_sanitize_num("color-ssn-source-color-id");

                    if ($option === "cluster") {

                        $obj = new efi\est\cluster_analysis($db);
                        $input->make_hmm = sanitize::post_sanitize_string("make-hmm", "", "[^A-Z,]");
                        $input->aa_threshold = sanitize::post_sanitize_string("aa-threshold", " ", "[^0-9\., ]");
                        $input->hmm_aa = sanitize::post_sanitize_string("hmm-aa", "", "[^A-Za-z, ]");
                        $input->min_seq_msa = sanitize::post_sanitize_num("min-seq-msa", 0);
                        $input->max_seq_msa = sanitize::post_sanitize_num("max-seq-msa", 0);

                    } else if ($option === "nc") {

                        $obj = new efi\est\nb_conn($db);

                    } else if ($option === "cr") {

                        $obj = new efi\est\conv_ratio($db);
                        $input->ascore = sanitize::post_sanitize_num("ascore");

                    } else {

                        $obj = new efi\est\colorssn($db);

                    }
                }

                if (isset($_FILES["file"])) {
                    $input->tmp_file = $_FILES["file"]["tmp_name"];
                    $input->uploaded_filename = $_FILES["file"]["name"];
                }
                $result = $obj->create($input);
            }
    
            break;
            
        default:
            $result["RESULT"] = false;
            $result["MESSAGE"] = "You need to select one of the above options.";
    
    }

}






if ($input->is_debug) {
    print "JSON: ";
}

$returnData = array("valid"     => $result["RESULT"],
                    "id"        => $result["id"],
                    "message"   => $result["MESSAGE"]);


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









function parse_tax_search() {
    if (!isset($_POST["tax_search"]) || !is_array($_POST["tax_search"]))
        return false;
    $search_array = $_POST["tax_search"];

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


function set_family_vars($option, $input) {
    if ($option === "B" || $option === "opt_tax") {
        $input->min_seq_len = sanitize::post_sanitize_num("pfam_min_seq_len");
        $input->max_seq_len = sanitize::post_sanitize_num("pfam_max_seq_len");
    }

    $input->families = sanitize::post_sanitize_string_relaxed("families_input", "");
    $use_uniref = sanitize::post_sanitize_flag("families_use_uniref");
    if ($use_uniref && !isset($input->uniref_version)) {
        $input->uniref_version = sanitize::post_sanitize_num("families_uniref_ver", 90);
    }

    if ($option === "B" || $option === "D") {
        $input->domain = sanitize::post_sanitize_string("domain", "");
        if ($input->domain)
            $input->domain_region = sanitize::post_sanitize_string("domain_region");
    }
}



