<?php
namespace efi\est;

require_once(__DIR__ . "/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\training\example_config;


class job {

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

    protected $is_example = false;
    private $load_table = "generate";

    private $alignment_length_filename = "alignment_length.png";
    private $alignment_length_new = "alignment_length_new.png";
    private $alignment_length_new_html = "alignment_length_new.html";
    private $percent_identity_filename = "percent_identity.png";
    private $percent_identity_new = "percent_identity_new.png";
    private $percent_identity_new_html = "percent_identity_new.html";
    private $number_of_edges_filename = "number_of_edges.png";
    private $alignment_length_sm_filename = "alignment_length_sm.png";
    private $percent_identity_sm_filename = "percent_identity_sm.png";
    private $number_of_edges_sm_filename = "number_of_edges_sm.png";
    private $length_histogram_filename = "length_histogram.png";


    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, "generate");

        if ($is_example) {
            $this->is_example = true;
            $this->init_example($id);
        }

        $this->db = $db;
        if ($id)
            $this->load_generate($id);
        $this->counts_file = functions::get_accession_counts_filename();
    }

    private function init_example($id) {
        $config = example_config::get_example_data($id);
        $this->load_table = example_config::get_est_generate_table($config);
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

    private function get_plot_path_shared($for_web, $file_name) {
        if ($this->is_example)
            $dir = ($for_web ? functions::get_results_example_dirname($this->is_example) : $this->ex_data_dir);
        else
            $dir = ($for_web ? functions::get_results_dirname() : functions::get_results_dir());
        return $dir . "/" . $this->get_output_dir() . "/" . $file_name;
    }
    public function get_alignment_plot($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->alignment_length_filename);
    }
    public function get_alignment_plot_new($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->alignment_length_new);
    }
    public function get_alignment_plot_new_html($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->alignment_length_new_html);
    }
    public function alignment_plot_exists() {
        return file_exists($this->get_alignment_plot(0));
    }
    public function alignment_plot_new_exists() {
        return file_exists($this->get_alignment_plot_new(0));
    }
    public function get_length_histogram_plot($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->length_histogram_filename);
    }
    public function length_histogram_plot_exists() {
        return file_exists($this->get_length_histogram_plot(0));
    }
    public function get_percent_identity_plot($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->percent_identity_filename);
    }
    public function get_percent_identity_plot_new($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->percent_identity_new);
    }
    public function get_percent_identity_plot_new_html($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->percent_identity_new_html);
    }
    public function percent_identity_plot_exists() {
        return file_exists($this->get_percent_identity_plot(0));
    }
    public function percent_identity_plot_new_exists() {
        return file_exists($this->get_percent_identity_plot_new(0));
    }
    public function get_number_edges_plot($for_web = 0) {
        return $this->get_plot_path_shared($for_web, $this->number_of_edges_filename);
    }
    public function number_edges_plot_exists() {
        return file_exists($this->get_number_edges_plot(0));
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
    public function get_alignment_plot_sm() {
        return $this->get_plot_webpath_sm_shared($this->alignment_length_sm_filename);
    }
    public function get_percent_identity_plot_sm() {
        return $this->get_plot_webpath_sm_shared($this->percent_identity_sm_filename);
    }
    public function get_number_edges_plot_sm() {
        return $this->get_plot_webpath_sm_shared($this->number_of_edges_sm_filename);
    }

    public function get_length_histogram_filename($type, $small = false) {
        if ($type)
            $type = "_$type";
        else
            $type = "";
        $filename = "length_histogram$type";
        if ($small)
            $filename .= "_sm";
        $filename .= ".png";
        return $filename;
    }
    public function get_length_histogram_download_type($type) {
        if ($type)
            return "HISTOGRAM_" . strtoupper($type);
        else
            return "HISTOGRAM";
    }
    public function get_length_histogram($type, $for_web = false, $small = false) {
        $filename = $this->get_length_histogram_filename($type, $small);
        if ($for_web)
            $path = $this->get_plot_webpath_sm_shared($filename);
        else
            $path = $this->get_plot_path_shared(0, $filename);
        return $path;
    }

    public function get_graphs_version() {
        $path = $this->get_plot_webpath_sm_shared($this->length_histogram_filename);
        return strlen($path) > 0 ? 1 : 2;
    }

    public function download_graph($type) {
        $filename = "";
        if ($type == "ALIGNMENT") {
            $full_path = $this->get_alignment_plot(0);
            $filename = $this->alignment_length_filename;
        } elseif (strlen($type) > 9 && substr($type, 0, 9) == "HISTOGRAM") {
            $histo = substr($type, 10);
            if ($histo == "UNIPROT")
                $histo = "uniprot";
            elseif ($histo == "UNIREF")
                $histo = "uniref";
            elseif ($histo == "UNIPROT_DOMAIN")
                $histo = "uniprot_domain";
            elseif ($histo == "UNIREF_DOMAIN")
                $histo = "uniref_domain";
            else
                $histo = "";
            if ($histo) {
                $full_path = $this->get_length_histogram($histo, false);
                $filename = $this->get_length_histogram_filename($histo);
            }
        } elseif ($type == "HISTOGRAM") {
            $full_path = $this->get_length_histogram("", false, false);
            $filename = $this->get_length_histogram_filename("", false);
        } elseif ($type == "IDENTITY") {
            $full_path = $this->get_percent_identity_plot(0);
            $filename = $this->percent_identity_filename;
        } elseif ($type == "EDGES") {
            $full_path = $this->get_number_edges_plot(0);
            $filename = $this->number_of_edges_filename;
        }
        $job_name = global_functions::safe_filename($this->get_job_name());
        $id = $this->get_id();
        if ($job_name)
            $filename = "${job_name}_$filename";
        $filename = "${id}_$filename";
        if (file_exists($full_path)) {
            global_functions::send_image_file_for_download($filename, $full_path);
#            header('Content-Description: File Transfer');
#            header('Content-Type: application/octet-stream');
#            header('Content-Disposition: attachment; filename="'.$filename.'"');
#            header('Content-Transfer-Encoding: binary');
#            header('Connection: Keep-Alive');
#            header('Expires: 0');
#            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
#            header('Pragma: public');
#            header('Content-Length: ' . filesize($full_path));
#            ob_clean();
#            readfile($full_path);
        }
        else {
            return false;
        }
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
        $sql = "SELECT * FROM $table WHERE generate_id='" . $id . "' ";
        $sql .= "LIMIT 1";
        $result = $this->db->query($sql);

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

    protected function verify_email($email) {
        $email = strtolower($email);
        $valid = 1;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid = 0;
        }
        return $valid;

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
}

