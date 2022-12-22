<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\est\est_shared;
use \efi\est\settings;
use \efi\est\functions;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\send_file;


class stepa extends est_shared {

    protected $db; //mysql database object
    protected $id;
    protected $key;
    protected $status;
    protected $evalue;
    protected $pbs_number;
    protected $finish_file = "1.out.completed";
    protected $type;
    protected $accession_file = "allsequences.fa";
    protected $counts_file;
    protected $num_pbs_jobs = 1;
    protected $program;
    protected $fraction;
    protected $db_version;
    protected $job_name = "";
    protected $is_tax_only = false;

    protected $ex_data_dir = "";
    private $load_table = "generate";

    private $alignment_length_filename = "alignment_length";
    private $percent_identity_filename = "percent_identity";
    private $number_of_edges_filename = "number_of_edges";
    private $length_histogram_filename = "length_histogram"; // blast jobs are "length_histogram", all others are "length_histogram_uniprot"


    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, "generate", $is_example);

        if ($is_example) {
            $this->init_example($is_example);
        }

        $this->db = $db;
        if ($id)
            $this->load_generate($id);
        $this->counts_file = functions::get_accession_counts_filename();
    }

    private function init_example($id) {
        $config = example_config::get_example_data($id);
        $this->load_table = example_config::get_est_generate_table($config);
        $this->ex_data_dir = example_config::get_est_data_dir($config);
    }

    public function __destruct() {
    }
    public function get_type() { return $this->type; }
    public function get_status() { return $this->status; }
    public function get_id() { return $this->id; }
    public function get_email() { return $this->email; }
    public function get_key() { return $this->key; }
    public function get_evalue() { return $this->evalue; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_is_tax_only() { return $this->is_tax_only; }

    // Acceptable inputs:
    //      num_seq
    //      total_num_file_seq
    //      num_matched_file_seq
    //      num_unmatched_file_seq
    //      num_family_seq
    //      num_full_family_seq
    //      num_blast_seq
    //      num_file_replicated
    //      num_unique_seq

    public function get_counts($key) {
        return isset($this->counts["generate_$key"]) ? $this->counts["generate_$key"] :
            (isset($this->counts[$key]) ? $this->counts[$key] : false);
    }
    public function print_raw_counts() {
        print "<pre>";
        var_dump($this->counts);
        print "</pre>";
    }

    public function get_program() { return $this->program; }
    public function get_fraction() { return $this->fraction; }
    public function get_finish_file() { return global_functions::get_est_job_results_path($this->get_id()) . "/" . $this->finish_file; }
    public function get_accession_file() {return $this->get_output_dir() . "/".  $this->accession_file;}
    public function get_accession_counts_file() {return $this->get_output_dir() . "/".  $this->counts_file;}
    public function get_accession_counts_file_full_path() {return functions::get_results_dir() . "/" . $this->get_output_dir() . "/".  $this->counts_file;}
    public function get_output_dir() { return global_functions::get_est_job_results_relative_path($this->get_id()); }
    public function get_blast_input() { return ""; }
    public function get_families() { return array(); }
    public function get_db_version() { return $this->db_version; }
    public function get_job_name() { return $this->job_name; }
    public function is_cd_hit_job() { return FALSE; } //HACK: this is a temporary hack for research purposes

    // This function is here so we don't have to do a full instantiation of the object from the
    // database if we are just looking for the job type.
    public static function get_type_static($db, $id) {
        $sql = "SELECT generate_type FROM generate WHERE generate_id = $id";
        $result = $db->query($sql);
        if (!$result)
            return "";
        else
            return $result[0]["generate_type"];
    }

    public function set_pbs_number($pbs_number) {
        $sql = "UPDATE generate SET generate_pbs_number='" . $pbs_number . "' ";
        $sql .= "WHERE generate_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->pbs_number = $pbs_number;
    }

    public function check_pbs_running() {
        $sched = strtolower(functions::get_cluster_scheduler());
        $jobNum = $this->get_pbs_number();
        $output = "";
        $exit_status = "";
        $exec = "";
        if ($sched == "slurm")
            $exec = "squeue --job $jobNum 2> /dev/null | grep $jobNum";
        else
            $exec = "qstat $jobNum 2> /dev/null | grep $jobNum";
        exec($exec,$output,$exit_status);
        if (count($output) == 1) {
            return true;
        }
        else {
            return false;
        }

    }

    public function check_finish_file() {
        $full_path = $this->get_finish_file();
        return file_exists($full_path);

    }

    public function set_status($status) {
        $sql = "UPDATE generate ";
        $sql .= "SET generate_status='" . $status . "' ";
        $sql .= "WHERE generate_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->status = $status;
        }
    }

    public function load_num_sequence_from_file() {
        $results_path = functions::get_results_dir();
        $full_count_path = $results_path . "/" . $this->get_accession_counts_file();
        $full_path = $results_path . "/" . $this->get_accession_file();

        $update = array("num_seq" => 0);
        if (file_exists($full_count_path)) {
            $lines = file($full_count_path);
            foreach ($lines as $line) {
                list($key, $val) = explode("\t", rtrim($line));
                if ($key == "Total")
                    $update["num_seq"] = intval($val);

                // New style
                elseif ($key == "BlastRetrieved")
                    $update["num_blast_retr"] = intval($val);
                elseif ($key == "Family")
                    $update["num_family"] = intval($val);
                elseif ($key == "FullFamily")
                    $update["num_full_family"] = intval($val);
                elseif ($key == "FamilyOverlap")
                    $update["num_family_overlap"] = intval($val);
                elseif ($key == "UniRefOverlap")
                    $update["num_uniref_overlap"] = intval($val);
                elseif ($key == "User")
                    $update["num_user"] = intval($val);
                elseif ($key == "UserMatched")
                    $update["num_user_matched"] = intval($val);
                elseif ($key == "UserUnmatched")
                    $update["num_user_unmatched"] = intval($val);
                elseif ($key == "FastaNumHeaders")
                    $update["num_user_fasta_headers"] = intval($val);
                elseif ($key == "ConvergenceRatio")
                    $update["convergence_ratio"] = floatval($val);
                elseif ($key == "EdgeCount")
                    $update["num_edges"] = intval($val);
                elseif ($key == "UniqueSeq")
                    $update["num_unique"] = intval($val);
            }
        } else {
            if (file_exists($full_path)) {
                $exec = "grep '>' " . $full_path . " | sort | uniq | wc -l ";
                $output = exec($exec);
                $output = trim(rtrim($output));
                list($num_seq,) = explode(" ",$output);
            } else {
                $num_seq = 0;
            }
            $update["generate_num_seq"] = $num_seq;
        }

        $result = $this->update_results_object($this->get_id(), $update);
        $this->set_counts($update);
    }

    private function set_counts($results) {
        $this->counts = $results;
    }

    public function get_convergence_ratio() {
        $cr = $this->get_counts("convergence_ratio");
        return $cr !== false ? $cr : 0;
    }

    protected function update_results_object($id, $data) {
        $sql = "SELECT generate_results FROM generate WHERE generate_id='" . $id . "' ";
        $result = $this->db->query($sql);
        if (!$result)
            return NULL;
        $result = $result[0];
        $results_obj = $this->decode_object($result['generate_results']);

        foreach ($data as $key => $value)
            $results_obj[$key] = $value;
        
        $json = $this->encode_object($results_obj);
        
        $sql = "UPDATE generate SET generate_results=";
        $sql .= $this->db->escape_string($json);
        $sql .= " WHERE generate_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);

        return $result;
    }

    private function get_results_path() {
        if ($this->is_example)
            $dir = $this->ex_data_dir;
        else
            $dir = functions::get_results_dir();
        // /path/to/base/est/results_dir/JOB_ID  . "/" . results . "/" ...
        return $dir . "/" . $this->get_output_dir();
    }
    public function alignment_plot_new_exists() {
        return false;
    }
    public function get_percent_identity_plot_new_html($for_web = 0) {
        return "";
    }
    public function percent_identity_plot_new_exists() {
        return false;
    }

    private function get_plot_webpath_sm_shared($file_name) {
        $dir = "";
        $dirname = "";
        if ($this->is_example) {
            $dir = $this->ex_data_dir;
            $dirname = functions::get_results_example_dirname($this->is_example);
        } else {
            $dir = functions::get_results_dir();
            $dirname = functions::get_results_dirname();
        }
        $full_file = $dir . "/" . $this->get_output_dir() . "/" . $file_name;
        if (file_exists($full_file)) {
            return $dirname . "/" . $this->get_output_dir() . "/" . $file_name;
        } else {
            return "";
        }
    }

    public function get_graphs_version() {
        $path = $this->get_plot_webpath_sm_shared($this->length_histogram_filename);
        return strlen($path) > 0 ? 1 : 2;
    }

    protected function get_length_histogram_file_name($type) {
        if ($this->get_type() == "BLAST" && $type == "uniprot") {
            return $this->length_histogram_filename;
        } else {
            return $this->length_histogram_filename . "_$type";
        }
    }

    public function get_graph_info($type) {
        $file_name = "";
        $dir_path = $this->get_results_path();

        if ($type == "alignment") {
            $file_name = $this->alignment_length_filename;
        } elseif (strlen($type) > 9 && substr($type, 0, 9) == "histogram") {
            $type = substr($type, 10);
            $types = array("uniprot" => 1, "uniref" => 1, "uniref90" => 1, "uniref50" => 1, "uniprot_domain" => 1, "uniref_domain" => 1);
            if (isset($types[$type]))
                $file_name = $this->get_length_histogram_file_name($type);
        } elseif ($type == "histogram") {
            $file_name = $this->length_histogram_filename;
        } elseif ($type == "identity") {
            $file_name = $this->percent_identity_filename;
        } elseif ($type == "edges") {
            $file_name = $this->number_of_edges_filename;
        }
        $info = array("file_path" => false, "file_name" => false);
        $file_path = "$dir_path/$file_name";

        if (!file_exists("$file_path.png"))
            return false;

        $job_name = $this->get_job_name();
        $id = $this->get_id();
        if ($job_name)
            $file_name = "${job_name}_$file_name";
        $file_name = "${id}_$file_name";

        $info["file_path"] = "$file_path.png";
        $info["file_name"] = "$file_name.png";
        if (file_exists("${file_path}_sm.png"))
            $info["small_path"] = "${file_path}_sm.png";

        return $info;
    }

    public function graph_exists($type) {
        $info = $this->get_graph_info($type);
        return $info !== false;
    }

    // PARENT EMAIL-RELATED OVERLOADS

    // Called by all inheritees
    protected function get_email_job_info() {
        $message = "EFI-EST Job ID: " . $this->get_id() . PHP_EOL;
        $message .= "Computation Type: " . functions::format_job_type($this->get_type()) . PHP_EOL;
        if ($this->get_job_name())
            $message .= "Job Name: " . $this->get_job_name() . PHP_EOL;
        return $message;
    }
    
    // This can be overridden.
    protected function get_generate_results_script() {
        return "stepc.php";
    }


    protected function get_email_started_subject() { return "EFI-EST - Initial submission received"; }
    protected function get_email_started_body() {
        $plain_email = "The initial information needed for the generation of the dataset is being fetched and ";
        $plain_email .= "processed. The similarity between sequences is being calculated." . PHP_EOL . PHP_EOL;
        $plain_email .= "You will receive an email once the job has been completed." . PHP_EOL . PHP_EOL;
        $plain_email .= "To check on the status of this job, go to THE_URL" . PHP_EOL . PHP_EOL;
        $plain_email .= "If no new email is received after 48 hours, please contact us and mention the EFI-EST ";
        $plain_email .= "Job ID that corresponds to this email." . PHP_EOL . PHP_EOL;
        
        $full_url = functions::get_web_root() . "/" . functions::get_job_status_script();
        $full_url = $full_url . "?" . http_build_query(array('id' => $this->get_id(), 'key' => $this->get_key()));

        return array("body" => $plain_email, "url" => $full_url);
    }

    protected function get_email_completion_subject() { return "EFI-EST - Initial calculation complete"; }
    protected function get_email_completion_body() {
        $plain_email = "The initial information needed for the generation of the dataset has been fetched and ";
        $plain_email .= "processed. The similarity between sequences has been calculated." . PHP_EOL . PHP_EOL;
        $plain_email .= "To finalize your SSN, please go to THE_URL" . PHP_EOL . PHP_EOL;
        
        $full_url = functions::get_web_root() . "/" . $this->get_generate_results_script();
        $full_url = $full_url . "?" . http_build_query(array('id' => $this->get_id(), 'key' => $this->get_key()));

        return array("body" => $plain_email, "url" => $full_url);
    }

    protected function get_email_cancelled_subject() { return "EFI-EST - Job cancelled"; }
    protected function get_email_cancelled_body() {
        $plain_email = "The EFI-EST job was cancelled upon user request.";
        return $plain_email;
    }

    public function email_number_seq() {
        $subject = "EFI-EST - Too many sequences for initial computation";
        $full_url = functions::get_web_root();
        $max_seq = settings::get_max_seq();

        $url = global_settings::get_base_web_root() . "/feedback.php";

        $plain_email = "";
        $plain_email .= "This computation will use " . number_format($this->get_num_sequences()) . "." . PHP_EOL;
        $plain_email .= "This number is too large--you are limited to ";
        $plain_email .=  number_format($max_seq) . " sequences." . PHP_EOL . PHP_EOL;
        $plain_email .= "Return to THE_URL" . PHP_EOL;
        $plain_email .= "to start a new job with a different set of Pfam/InterPro families.";
        $plain_email .= "Or, if you would like to generate a network with the Pfam/InterPro";
        $plain_email .= " families you have chosen, submit feedback at $url and we may be able to assist you.";
        $plain_email .= PHP_EOL . PHP_EOL;

        $this->email_error($subject, array("body" => $plain_email, "url" => $full_url));
    }

    public function email_format_error() {
        $subject = "EFI-EST - Job failed; check input format";
        $full_url = functions::get_web_root();

        $plain_email = "";
        $plain_email .= "The job failed to finish.  If you uploaded a FASTA file, please check the ";
        $plain_email .= "input file format.  Submitted FASTA files must only include alphabetical ";
        $plain_email .= "amino acid codes and cannot include the translation stop (*) or indeterminate ";
        $plain_email .= "length gap (-) codes." . PHP_EOL . PHP_EOL;

        $this->email_error($subject, array("body" => $plain_email, "url" => $full_url));
    }


    public function email_bad_sequence() {
        $subject = "EFI-EST - Job failed; check input format";
        $full_url = functions::get_web_root();
        
        $plain_email = "";
        $plain_email .= "The job failed to finish. The input sequence was invalid or too short. ";
        $plain_email .= "Input sequences must only include alphabetical ";
        $plain_email .= "amino acid codes and cannot include the translation stop (*) or indeterminate ";
        $plain_email .= "length gap (-) codes or FASTA headers." . PHP_EOL . PHP_EOL;

        $this->email_error($subject, array("body" => $plain_email, "url" => $full_url));
    }

    public function email_general_failure() {
        $subject = "EFI-EST - Job failed";

        $plain_email = "";
        $plain_email .= "The job failed to finish.  Please report this problem to us with the ";
        $plain_email .= "contact information in this email.  Please include this email in any ";
        $plain_email .= "correspondence." . PHP_EOL . PHP_EOL;

        $this->email_error($subject, $plain_email);
    }


    public static function duplicate_job($db, $parent_id, $email) {
        $result = $db->query("SELECT * FROM generate WHERE generate_id = $parent_id");
        if (!$result)
            return false;

        $parent = $result[0];
        $copy = array();
        foreach ($parent as $key => $val) {
            if ($key == "generate_id")
                $val = "NULL";
            elseif ($key == "generate_parent_id")
                $val = $parent_id;
            elseif ($key == "generate_email")
                $val = $email;
            elseif ($key == "generate_key")
                $val = global_functions::generate_key();
            elseif ($key == "generate_time_created")
                $val = "NULL";
            elseif ($key == "generate_time_started" || $key == "generate_time_completed")
                $val = self::get_current_datetime();
            if ($val != "NULL")
                $copy[$key] = $val;
        }

        $new_id = $db->build_insert("generate", $copy);
        if ($new_id)
            return $new_id;
        else
            return false;
    }






    ///////////////Protected Functions///////////


    protected function load_generate($id) {
        $table = $this->load_table;
        $sql = "SELECT * FROM $table WHERE generate_id = :id LIMIT 1";
        $result = $this->db->query($sql, array("id" => $id));

        $results_obj = array();
        $params_obj = array();
        if ($result) {
            $result = $result[0];
            $this->id = $id;
            $this->key = $result['generate_key'];
            $this->pbs_number = $result['generate_pbs_number'];
            $this->time_created = $result['generate_time_created'];
            $this->status = $result['generate_status'];
            $this->time_started = $result['generate_time_started'];
            $this->time_completed = $result['generate_time_completed'];
            $this->type = $result['generate_type'];
            $this->email = $result['generate_email'];
            $this->program = $result['generate_program'];
            $this->is_tax_only = (isset($result['generate_is_tax_job']) && $result['generate_is_tax_job'] != 0);
            $this->db_version = functions::decode_db_version($result['generate_db_version']);
            
            $params_obj = $this->decode_object($result['generate_params']);
            $this->evalue = $params_obj['generate_evalue'];
            $this->fraction = $params_obj['generate_fraction'];
            $this->job_name = isset($params_obj['generate_job_name']) ? $params_obj['generate_job_name'] : "";
            
            $results_obj = $this->decode_object($result['generate_results']);
            $this->set_counts($results_obj);
            
            $this->is_sticky = functions::is_job_sticky($this->db, $this->id, $this->email);
        }

        return $params_obj;
    }

    protected function decode_object($json) {
        return global_functions::decode_object($json);
    }

    protected function encode_object($obj) {
        return global_functions::encode_object($obj);
    }

    protected function verify_evalue($evalue) {
        $max_evalue = 100;
        $valid = 1;
        if ($evalue == "") {
            $valid = 0;
        }
        if (!preg_match("/^\d+$/",$evalue)) {
            $valid = 0;

        }
        if ($evalue > $max_evalue) {
            $valid = 0;
        }
        return $valid;


    }

    protected function verify_fraction($fraction) {
        $valid = 1;
        if ($fraction == "") {
            $valid = 0;
        }
        if (!preg_match("/^\d+$/",$fraction)) {
            $valid = 0;
        }
        return $valid;

    }

    public function get_analysis_jobs() {
        if ($this->is_example)
            return array();

        $sql = "SELECT analysis_id, analysis_status, analysis_name, analysis_min_length, analysis_max_length, analysis_evalue FROM analysis WHERE analysis_generate_id = $this->id";
        $rows = $this->db->query($sql);

        if (!$rows)
            return array();

        $jobs = array();
        foreach ($rows as $row) {
            $max_val = $row["analysis_max_length"] != settings::get_ascore_maximum() ? $row["analysis_max_length"] : 0;
            $info = array(
                "name" => $row["analysis_name"],
                "ascore" => $row["analysis_evalue"],
                "min_len" => $row["analysis_min_length"],
                "max_len" => $max_val,
                "status" => $row["analysis_status"],
            );
            $jobs[$row["analysis_id"]] = $info;
        }

        return $jobs;
    }

    protected function get_results_dir() {
        $dir = "";
        if ($this->is_example) {
            $dir = $this->ex_data_dir;
            #$dirname = functions::get_results_example_dirname($this->is_example);
        } else {
            $dir = functions::get_results_dir();
            #$dirname = functions::get_results_dirname();
        }
        return $dir;
        #$full_file = $dir . "/" . $this->get_output_dir() . "/" . $file_name;
        #return $full_file;
    }
}

