<?php

require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__DIR__ . "/est_shared.class.inc.php");

class stepa extends est_shared {

    ////////////////Private Variables//////////

    protected $db; //mysql database object
    protected $id;
    protected $key;
    protected $status;
    protected $evalue;
    protected $pbs_number;
    protected $finish_file = "1.out.completed";
    protected $output_dir = "output";
    protected $type;
    protected $num_sequences;
    protected $total_num_file_sequences;
    protected $num_matched_file_sequences;
    protected $num_unmatched_file_sequences;
    protected $num_family_sequences;
    protected $num_full_family_sequences;
    protected $accession_file = "allsequences.fa";
    protected $counts_file;
    protected $num_pbs_jobs = 1;
    protected $program;
    protected $fraction;
    protected $db_version;
    protected $is_sticky = false;
    protected $job_name = "";

    //private $alignment_length = "r_quartile_align.png";
    //private $length_histogram = "r_hist_length.png";
    //private $percent_identity = "r_quartile_perid.png";
    //private $number_of_edges = "r_hist_edges.png";

    private $alignment_length = "alignment_length.png";
    private $length_histogram = "length_histogram.png";
    private $length_histogram_uniprot = "length_histogram_uniprot.png";
    private $percent_identity = "percent_identity.png";
    private $number_of_edges = "number_of_edges.png";
    private $alignment_length_sm = "alignment_length_sm.png";
    private $length_histogram_sm = "length_histogram_sm.png";
    private $length_histogram_uniprot_sm = "length_histogram_uniprot_sm.png";
    private $percent_identity_sm = "percent_identity_sm.png";
    private $number_of_edges_sm = "number_of_edges_sm.png";
    ///////////////Public Functions///////////

    public function __construct($db, $id = 0) {
        parent::__construct($db, "generate");

        $this->db = $db;
        if ($id)
            $this->load_generate($id);
        $this->counts_file = functions::get_accession_counts_filename();
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
    public function get_num_sequences() { return $this->num_sequences; }
    public function get_total_num_file_sequences() { return $this->total_num_file_sequences; }
    public function get_num_matched_file_sequences() { return $this->num_matched_file_sequences; }
    public function get_num_unmatched_file_sequences() { return $this->num_unmatched_file_sequences; }
    public function get_num_family_sequences() { return $this->num_family_sequences; }  // UniRef size if option is for UniRef.
    public function get_num_full_family_sequences() { return $this->num_full_family_sequences; }
    public function get_program() { return $this->program; }
    public function get_fraction() { return $this->fraction; }
    public function get_finish_file() { return $this->get_output_dir() . "/" . $this->finish_file; }
    public function get_accession_file() {return $this->get_output_dir() . "/".  $this->accession_file;}
    public function get_accession_counts_file() {return $this->get_output_dir() . "/".  $this->counts_file;}
    public function get_accession_counts_file_full_path() {return functions::get_results_dir() . "/" . $this->get_output_dir() . "/".  $this->counts_file;}
    public function get_output_dir() {return $this->get_id() . "/" . $this->output_dir;}
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
        $results_path = functions::get_results_dir();
        $full_path = $results_path . "/" . $this->get_finish_file();
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

    public function get_num_sequence_from_file() {
        $results_path = functions::get_results_dir();
        $full_count_path = $results_path . "/" . $this->get_accession_counts_file();
        $full_path = $results_path . "/" . $this->get_accession_file();

        if (file_exists($full_count_path)) {
            $num_seq = array('total_ssn_nodes' => 0, 'file_seq' => 0, 'file_matched' => 0, 'file_unmatched' => 0, 'family' => 0, 'full_familh' => 0);
            $lines = file($full_count_path);
            foreach ($lines as $line) {
                list($key, $val) = explode("\t", rtrim($line));
                if (!$val)
                    $num_seq['total_ssn_nodes'] = intval($key);
                else if ($key == "Total")
                    $num_seq['total_ssn_nodes'] = intval($val);
                else if($key == "FileTotal")
                    $num_seq['file_seq'] = intval($val);
                else if($key == "FileMatched")
                    $num_seq['file_matched'] = intval($val);
                else if($key == "FileUnmatched")
                    $num_seq['file_unmatched'] = intval($val);
                else if ($key == "Family")
                    $num_seq['family'] = intval($val);
                else if ($key == "FullFamily")
                    $num_seq['full_family'] = intval($val);
            }
        } else if (file_exists($full_path)) {
            $exec = "grep '>' " . $full_path . " | sort | uniq | wc -l ";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_seq,) = explode(" ",$output);
        } else {
            $num_seq = 0;
        }

        return $num_seq;
    }

    public function set_num_sequences($num_seq) {
        $update = array();

        if (is_array($num_seq)) {
            $update["generate_num_seq"] = $num_seq['total_ssn_nodes'];
            $update["generate_total_num_file_seq"] = $num_seq['file_seq'];
            $update["generate_num_matched_file_seq"] = $num_seq['file_matched'];
            $update["generate_num_unmatched_file_seq"] = $num_seq['file_unmatched'];
            $update["generate_num_family_seq"] = $num_seq['family'];
            if (isset($num_seq['full_family']))
                $update["generate_num_full_family_seq"] = $num_seq['full_family'];
            else
                $update["generate_num_full_family_seq"] = $num_seq['family'];
        } else {
            $update["generate_num_seq"] = $num_seq;
        }

        $result = $this->update_results_object($this->get_id(), $update);

        if ($result) {
            if (is_array($num_seq)) {
                $this->num_sequences = $num_seq['total_ssn_nodes'];
                $this->total_num_file_sequences = $num_seq['file_seq'];
                $this->num_matched_file_sequences = $num_seq['file_matched'];
                $this->num_unmatched_file_sequences = $num_seq['file_unmatched'];
                $this->num_family_sequences = $num_seq['family'];
                if (isset($num_seq['full_family']))
                    $this->num_full_family_sequences = $num_seq['full_family'];
                else
                    $this->num_full_family_sequences = $num_seq['family'];
            }
            else {
                $this->num_sequences = $num_seq;
                $this->total_num_file_sequences = 0;
                $this->num_matched_file_sequences = 0;
                $this->num_unmatched_file_sequences = 0;
                $this->num_family_sequences = 0;
                $this->num_full_family_sequences = 0;
            }
            return true;
        }
        return false;
    }

    public function get_convergence_ratio() {
        $results_dir = functions::get_results_dir();
        $file = $results_dir . "/" . $this->get_output_dir();
        $file .= "/" . functions::get_convergence_ratio_filename();
        if (!file_exists($file))
            return -1;
        
        $file_handle = @fopen($file,"r") or die("Error opening " . $file . "\n");
        $ratio = fgets($file_handle);
        fclose($file_handle);

        if ($ratio)
            return floatval($ratio);
        else
            return -1;
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
        $sql .= "'" . $this->db->escape_string($json) . "'";
        $sql .= " WHERE generate_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);

        return $result;
    }

    public function get_alignment_plot($for_web = 0) {
        return ($for_web ? functions::get_results_dirname() : functions::get_results_dir()) . "/" . $this->get_output_dir() . "/" . $this->alignment_length;
    }
    public function alignment_plot_exists() {
        return file_exists($this->get_alignment_plot(0));
    }
    public function get_length_histogram_plot($for_web = 0) {
        return ($for_web ? functions::get_results_dirname() : functions::get_results_dir()) . "/" . $this->get_output_dir() . "/" . $this->length_histogram;
    }
    public function length_histogram_plot_exists() {
        return file_exists($this->get_length_histogram_plot(0));
    }
    public function get_uniprot_length_histogram_plot($for_web = 0) {
        return ($for_web ? functions::get_results_dirname() : functions::get_results_dir()) . "/" . $this->get_output_dir() . "/" . $this->length_histogram_uniprot;
    }
    public function uniprot_length_histogram_plot_exists() {
        return file_exists($this->get_uniprot_length_histogram_plot(0));
    }
    public function get_percent_identity_plot($for_web = 0) {
        return ($for_web ? functions::get_results_dirname() : functions::get_results_dir()) . "/" . $this->get_output_dir() . "/" . $this->percent_identity;
    }
    public function percent_identity_plot_exists() {
        return file_exists($this->get_percent_identity_plot(0));
    }
    public function get_number_edges_plot($for_web = 0) {
        return ($for_web ? functions::get_results_dirname() : functions::get_results_dir()) . "/" . $this->get_output_dir() . "/" . $this->number_of_edges;
    }
    public function number_edges_plot_exists() {
        return file_exists($this->get_number_edges_plot(0));
    }
    public function get_alignment_plot_sm() {
        $full_file = functions::get_results_dir() . "/" . $this->get_output_dir() . "/" . $this->alignment_length_sm;
        if (file_exists($full_file)) {
            return functions::get_results_dirname() . "/" . $this->get_output_dir() . "/" . $this->alignment_length_sm;
        } else {
            return "";
        }
    }
    public function get_length_histogram_plot_sm() {
        $full_file = functions::get_results_dir() . "/" . $this->get_output_dir() . "/" . $this->length_histogram_sm;
        if (file_exists($full_file)) {
            return functions::get_results_dirname() . "/" . $this->get_output_dir() . "/" . $this->length_histogram_sm;
        } else {
            return "";
        }
    }
    public function get_uniprot_length_histogram_plot_sm() {
        $full_file = functions::get_results_dir() . "/" . $this->get_output_dir() . "/" . $this->length_histogram_uniprot_sm;
        if (file_exists($full_file)) {
            return functions::get_results_dirname() . "/" . $this->get_output_dir() . "/" . $this->length_histogram_uniprot_sm;
        } else {
            return "";
        }
    }
    public function get_percent_identity_plot_sm() {
        $full_file = functions::get_results_dir() . "/" . $this->get_output_dir() . "/" . $this->percent_identity_sm;
        if (file_exists($full_file)) {
            return functions::get_results_dirname() . "/" . $this->get_output_dir() . "/" . $this->percent_identity_sm;
        } else {
            return "";
        }
    }
    public function get_number_edges_plot_sm() {
        $full_file = functions::get_results_dir() . "/" . $this->get_output_dir() . "/" . $this->number_of_edges_sm;
        if (file_exists($full_file)) {
            return functions::get_results_dirname() . "/" . $this->get_output_dir() . "/" . $this->number_of_edges_sm;
        } else {
            return "";
        }
    }

    public function download_graph($type) {
        $filename = "";
        if ($type == "ALIGNMENT") {
            $full_path = $this->get_alignment_plot(0);
            $filename = $this->get_alignment_plot();
        }
        elseif ($type == "HISTOGRAM") {
            $full_path = $this->get_length_histogram_plot(0);
            $filename = $this->get_length_histogram_plot();
        }
        elseif ($type == "HISTOGRAM_UNIPROT") {
            $full_path = $this->get_uniprot_length_histogram_plot(0);
            $filename = $this->get_uniprot_length_histogram_plot();
        }
        elseif ($type == "IDENTITY") {
            $full_path = $this->get_percent_identity_plot(0);
            $filename = $this->get_percent_identity_plot();
        }
        elseif ($type == "EDGES") {
            $full_path = $this->get_number_edges_plot(0);
            $filename = $this->get_number_edges_plot();
        }
        if (file_exists($full_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($full_path).'"');
            header('Content-Transfer-Encoding: binary');
            header('Connection: Keep-Alive');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($full_path));
            ob_clean();
            readfile($full_path);
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
        return $message;
    }
    
    // This can be overridden.
    protected function get_generate_results_script() {
        return "stepc.php";
    }


    protected function get_email_started_subject() { return "EFI-EST - Initial submission received"; }
    protected function get_email_started_body() {
        $plain_email = "The initial information needed for the generation of the data set is being fetched and ";
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
        $plain_email = "The initial information needed for the generation of the data set has been fetched and ";
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
        $max_seq = functions::get_max_seq();

        $plain_email = "";
        $plain_email .= "This computation will use " . number_format($this->get_num_sequences()) . "." . PHP_EOL;
        $plain_email .= "This number is too large--you are limited to ";
        $plain_email .=  number_format($max_seq) . " sequences." . PHP_EOL . PHP_EOL;
        $plain_email .= "Return to THE_URL" . PHP_EOL;
        $plain_email .= "to start a new job with a different set of Pfam/InterPro families.";
        $plain_email .= "Or, if you would like to generate a network with the Pfam/InterPro";
        $plain_email .= " families you have chosen, send an e-mail to efi@enzymefunction.org and";
        $plain_email .= " request an account on Biocluster.  We will provide you with instructions";
        $plain_email .= " to use our Unix scripts for network generation.  These scripts allow you";
        $plain_email .= " to use a larger number of processors and, also, provide more options for";
        $plain_email .= " generating the network files.  Your e-mail should provide a brief ";
        $plain_email .= "description of your project so that the EFI can assist you." . PHP_EOL . PHP_EOL;

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
                $val = functions::generate_key();
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
        $sql = "SELECT * FROM generate WHERE generate_id='" . $id . "' ";
        $sql .= "LIMIT 1";
        $result = $this->db->query($sql);
        $result = $result[0];

        $results_obj = array();
        if ($result) {
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
            if (array_key_exists('generate_num_seq', $results_obj))
                $this->num_sequences = $results_obj['generate_num_seq'];
            if (array_key_exists('generate_total_num_file_seq', $results_obj))
                $this->total_num_file_sequences = $results_obj['generate_total_num_file_seq'];
            if (array_key_exists('generate_num_matched_file_seq', $results_obj))
                $this->num_matched_file_sequences = $results_obj['generate_num_matched_file_seq'];
            if (array_key_exists('generate_num_unmatched_file_seq', $results_obj))
                $this->num_unmatched_file_sequences = $results_obj['generate_num_unmatched_file_seq'];
            if (array_key_exists('generate_num_family_seq', $results_obj))
                $this->num_family_sequences = $results_obj['generate_num_family_seq'];
            if (array_key_exists('generate_num_full_family_seq', $results_obj))
                $this->num_full_family_sequences = $results_obj['generate_num_full_family_seq'];
            
            $this->is_sticky = functions::is_job_sticky($this->db, $this->id, $this->email);
        }

        return $params_obj;
    }

    protected function decode_object($json) {
        //return json_decode($json, true);
        return functions::decode_object($json);
    }

    protected function encode_object($obj) {
        //return json_encode($obj);
        return functions::encode_object($obj);
    }

    protected function verify_email($email) {
        $email = strtolower($email);
        $hostname = "";
        if (strpos($email,"@")) {
            list($prefix,$hostname) = explode("@",$email);
        }

        $valid = 1;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid = 0;
        }
        elseif (($hostname != "") && (!checkdnsrr($hostname,"ANY"))) {
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

    protected function generate_key() {
        $key = uniqid (rand (),true);
        $hash = sha1($key);
        return $hash;

    }

//    public function shared_get_full_file_path($infix_type, $ext) {
//        $filename = $this->get_file_prefix() . $infix_type . $ext;
//        $output_dir = $this->get_output_dir();
//        $full_path = $output_dir . "/" . $filename;
//        return $full_path;
//    }
//
//    public function shared_get_relative_file_path($infix_type, $ext) {
//        $filename = $this->get_file_prefix() . $infix_type . "_co" . $this->get_cooccurrence() . "_ns" . $this->get_size() . $ext;
//        $output_dir = $this->get_output_dir();
//        $full_path = $output_dir . "/" . $this->get_id() . "/".  $filename;
//        return $full_path;
//    }
//    protected function get_shared_name() {
//    }

    //	private function available_pbs_slots() {
    //                $queue = new queue(functions::get_generate_queue());
    //                $num_queued = $queue->get_num_queued();
    //                $max_queuable = $queue->get_max_queuable();
    //                $num_user_queued = $queue->get_num_queued(functions::get_cluster_user());
    //                $max_user_queuable = $queue-> get_max_user_queuable();
    //
    //               	$result = false; 
    //		if ($max_queuable - $num_queued < $this->num_pbs_jobs) {
    //			$result = false;
    //			$msg = "ERROR: Queue " . functions::get_generate_queue() . " is full.  Number in the queue: " . $num_queued;
    //		}
    //		elseif ($max_user_queuable - $num_user_queued < $this->num_pbs_jobs) {
    //			$result = false;
    //			$msg = "ERROR: Number of Queued Jobs for user " . functions::get_cluster_user() . " is full.  Number in the queue: " . $num_user_queued;	
    //                }
    //		else {
    //			$result = true;
    //			$msg = "Number of queued jobs in queue " . functions::get_generate_queue() . ": " . $num_queued . ", Number of queued user jobs: " . $num_user_queued;
    //		}
    //		functions::log_message($msg);
    //		return $result;
    //        }


    //private function get_job_info() {
    //
    //	$message = "EFI-EST ID: " . $this->get_id() . "\r\n";
    //	$message .= "E-Value: " . $this->get_evalue() . "\r\n";	
    //            return $message;
    //}
    
    public function is_expired() {
        if (!$this->is_sticky && time() > $this->get_unixtime_completed() + functions::get_retention_secs()) {
            return true;
        } else {
            return false;
        }
    }

    public function get_analysis_jobs() {
        $sql = "SELECT analysis_id, analysis_status, analysis_name, analysis_min_length, analysis_max_length, analysis_evalue FROM analysis WHERE analysis_generate_id = $this->id";
        $rows = $this->db->query($sql);

        if (!$rows)
            return array();

        $jobs = array();
        foreach ($rows as $row) {
            $max_val = $row["analysis_max_length"] != __MAXIMUM__ ? $row["analysis_max_length"] : 0;
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

?>
