<?php

require_once('option_base.class.inc.php');
require_once('generate_helper.class.inc.php');

class blast extends option_base {


    private $blast_input;
    private $blast_sequence_max;
    public $fail_file = "1.out.failed";
    public $subject = "EFI-EST PFAM/Interpro";


    public function __construct($db,$id = 0) {
        parent::__construct($db, $id);
        $this->num_pbs_jobs = 11;
    }

    public function __destruct() {
    }

    public function get_submitted_max_sequences() { return $this->blast_sequence_max; }
        public function get_blast_input() { return $this->blast_input; }
        public function get_finish_file() { 
            return $this->get_output_dir() . "/" . $this->finish_file; 
        }
    public function get_fail_file() {
        return $this->get_output_dir() . "/" . $this->fail_file;
    }
    public function check_fail_file() {
        $results_path = functions::get_results_dir();
        $full_path = $results_path . "/" . $this->get_fail_file();
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
        return "BLAST";
    }

    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $insert_array['generate_blast_max_sequence'] = $data->max_seqs;
        $formatted_blast = $this->format_blast($data->field_input);
        $insert_array['generate_blast'] = $formatted_blast;
        return $insert_array;
    }


    protected function validate($data) {
        $result = parent::validate($data);

        if (($data->field_input != "") && (!$this->verify_blast_input($data->field_input))) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter a valid blast input</b></br>";
        }
        if (!$this->verify_max_seqs($data->max_seqs)) {
            $result->errors = true;	
            $result->message .= "<br><b>Please enter a valid maximum number of sequences</b></br>";
        }

        return $result;
    }

    protected function get_run_script() {
        return "blasthits-new.pl";
    }

    protected function get_run_script_args($outDir) {
        $parms = array();
        $parms = generate_helper::get_run_script_args($outDir, $parms, $this);

        $parms["-seq"] = "'" . $this->get_blast_input() . "'";
        if ($this->get_submitted_max_sequences() != "") {
            $parms["-nresults"] = $this->get_submitted_max_sequences();
        }
        else {
            $parms["-nresults"] = functions::get_default_blast_seq();
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

        return $result;
    }

    public function get_job_info($eol = "\r\n") {
        $message = "EFI-EST ID: " . $this->get_id() . $eol;
        $message .= "Computation Type: " . functions::format_job_type($this->get_type()) . $eol;
        $message .= "Blast Sequence: " . $eol;
        $message .= $this->get_formatted_blast() . $eol;
        $message .= "E-Value: " . $this->get_evalue() . $eol;
        $message .= "Maximum Blast Sequences: " . $this->get_submitted_max_sequences() . $eol;
        //$message .= "Selected Program: " . $this->get_program() . $eol;
        
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
        elseif ($max_seqs > functions::get_max_blast_seq()) {
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
