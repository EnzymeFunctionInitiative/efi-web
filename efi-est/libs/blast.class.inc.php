<?php

require_once(__DIR__ . "/option_base.class.inc.php");
require_once(__DIR__ . "/generate_helper.class.inc.php");
require_once(__DIR__ . "/est_settings.class.inc.php");

class blast extends family_shared {


    private $blast_input;
    private $blast_sequence_max;
    private $blast_evalue; // the evalue to use for the SEQUENCE blast, not the family one
    public $fail_file = "1.out.failed";
    public $subject = "EFI-EST BLAST";


    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
        $this->num_pbs_jobs = 11;
    }

    public function __destruct() {
    }

    public function get_submitted_max_sequences() { return $this->blast_sequence_max; }
    public function get_blast_input() { return $this->blast_input; }
    public function get_blast_evalue() { return $this->blast_evalue; }
    public function check_fail_file() {
        $results_path = functions::get_results_dir();
        $full_path = $results_path . "/" . $this->get_output_dir() . "/" . $this->fail_file;
        return file_exists($full_path);
    }
    public function get_formatted_blast() {
        $width = 80;
        $break = "\r\n";
        $cut = true;
        $formatted_blast = str_replace(" ","",$this->get_blast_input());
        return wordwrap($formatted_blast,$width,$break,$cut);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "BLAST";
    }

    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $insert_array['generate_blast_max_sequence'] = $data->max_seqs;
        $insert_array['generate_blast_evalue'] = $data->blast_evalue;
        $formatted_blast = self::format_blast($data->field_input);
        $insert_array['generate_blast'] = $formatted_blast;
        return $insert_array;
    }


    protected function validate($data) {
        $result = parent::validate($data);

        // Remove any header line
        if (preg_match('/^>/', $data->field_input)) {
            $parts = preg_split('/[\r\n]+/', $data->field_input);
            $data->field_input = implode("", array_slice($parts, 1));
        }

        if (($data->field_input != "") && (!$this->verify_blast_input($data->field_input))) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter a valid blast input</b></br>";
        }
        if (!$this->verify_max_seqs($data->max_seqs)) {
            $result->errors = true;	
            $result->message .= "<br><b>Please enter a valid maximum number of sequences</b></br>";
        }
        if (!$this->verify_evalue($data->blast_evalue)) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter a valid E-Value</b></br>";
        }

        return $result;
    }

    protected function get_run_script() {
        return "create_blast_job.pl";
    }

    protected function get_run_script_args($outDir) {
        //$parms = array();
        $parms = parent::get_run_script_args($outDir);
        //$parms = generate_helper::get_run_script_args($outDir, $parms, $this);

        $parms["-seq"] = "'" . $this->get_blast_input() . "'";
        $parms["-blast-evalue"] = $this->blast_evalue; // initial blast evalue
        if ($this->get_submitted_max_sequences() != "") {
            $parms["-nresults"] = $this->get_submitted_max_sequences();
        }
        else {
            $parms["-nresults"] = est_settings::get_default_blast_seq();
        }
        $parms["-seq-count-file"] = $this->get_accession_counts_file_full_path();

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        $this->blast_input = $result['generate_blast'];
        $this->blast_sequence_max = $result['generate_blast_max_sequence'];
        if (! array_key_exists('generate_blast_evalue', $result))
            $this->blast_evalue = $result['generate_evalue'];  // legacy cases
        else
            $this->blast_evalue = $result['generate_blast_evalue'];

        return $result;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        $message .= "BLAST Sequence: " . PHP_EOL;
        $message .= $this->get_formatted_blast() . PHP_EOL;
        $message .= "E-Value: " . $this->blast_evalue . PHP_EOL;
        $fams = $this->get_families_comma();
        if ($fams) {
            $message .= "Pfam/InterPro Families: " . $fams . PHP_EOL;
            $message .= "Pfam/InterPro Families BLAST E-Value: " . $this->get_evalue() . PHP_EOL;
        }
        $message .= "Maximum BLAST Sequences: " . $this->get_submitted_max_sequences() . PHP_EOL;
        //$message .= "Selected Program: " . $this->get_program() . PHP_EOL;
        
        return $message;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    private function verify_blast_input($blast_input) {
        $blast_input = strtolower($blast_input);
        $valid = 1;

        if (!strlen($blast_input)) {
            $valid = 0;
        }
        if (strlen($blast_input) > 65534) {
            $valid = 0;
        }
        if (preg_match('/[^a-z-* \n\t\r]/',$blast_input)) {
            $valid = 0;
        }
        return $valid;
    }


    public static function format_blast($blast_input) {
        //$search = array(" ","\n","\r\n","\r","\t");
        $search = array("\r\n","\r"," ");
        $replace = "";
        $formatted_blast = str_ireplace($search,$replace,$blast_input);
        return $formatted_blast;
    }

    protected function verify_max_seqs($max_seqs) {
        $valid = 0;
        if ($max_seqs == "") {
            $valid = 0;
        }
        elseif (!preg_match("/^[1-9][0-9]*$/",$max_seqs)) {
            $valid = 0;
        }
        elseif ($max_seqs > est_settings::get_max_blast_seq()) {
            $valid = 0;
        }
        else {
            $valid = 1;
        }
        return $valid;
    }

    protected function available_pbs_slots() {
        $queue = new queue(functions::get_generate_queue());
        $num_queued = $queue->get_num_queued();
        $max_queuable = $queue->get_max_queuable();
        $num_user_queued = $queue->get_num_queued(functions::get_cluster_user());
        $max_user_queuable = $queue-> get_max_user_queuable();

        $result = false;
        if ($max_queuable - $num_queued < $this->num_pbs_jobs + functions::get_blasthits_processors()) {
            $result = false;
            $msg = "Generate ID: " . $this->get_id() . " - ERROR: Queue " . functions::get_generate_queue() . " is full.  Number in the queue: " . $num_queued;
        }
        elseif ($max_user_queuable - $num_user_queued < $this->num_pbs_jobs + functions::get_blasthits_processors()) {
            $result = false;
            $msg = "Generate ID: " . $this->get_id() . " - ERROR: Number of Queued Jobs for user " . functions::get_cluster_user() . " is full.  Number in the queue: " . $num_user_queued;
        }
        else {
            $result = true;
            $msg = "Generate ID: " . $this->get_id() . " - Number of queued jobs in queue " . functions::get_generate_queue() . ": " . $num_queued . ", Number of queued user jobs: " . $num_user_queued;
        }
        functions::log_message($msg);
        return $result;
    }

}

?>
