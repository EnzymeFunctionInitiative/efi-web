<?php

require_once("settings.class.inc.php");
require_once("Mail.php");
require_once("Mail/mime.php");

abstract class job_shared {

    const DEFAULT_DIAMOND_SENSITIVITY = "sensitive";
    const DEFAULT_CDHIT_SID = "85";
    const REFDB_UNIPROT = "uniprot";
    const REFDB_UNIREF90 = "uniref90";
    const REFDB_UNIREF50 = "uniref50";
    const DEFAULT_REFDB = "uniprot";

    private $id;
    private $pbs_number;
    private $key;
    private $status;
    private $email;
    private $time_created;
    private $time_started;
    private $time_completed;
    private $parent_id = 0;
    private $search_type = "";
    private $filename = "";
    private $min_seq_len = "";
    private $max_seq_len = "";

    private $db;
    private $beta;
    protected $eol = PHP_EOL;

    function __construct($db) {
        $this->db = $db;
        $this->beta = settings::get_release_status();
    }

    protected abstract function get_table_name();

    public function get_id() {
        return $this->id;
    }
    public function set_id($theId) {
        $this->id = $theId;
    }

    public function get_key() {
        return $this->key;
    }
    public function set_key($newKey) {
        $this->key = $newKey;
    }

    public function get_pbs_number() {
        return $this->pbs_number;
    }
    protected function set_pbs_number($theNumber) {
        $this->pbs_number = $theNumber;
        $this->update_pbs_number();
    }

    protected function set_status($status) {
        $this->status = $status;
        $this->update_status($status);
    }
    public function get_status() {
        return $this->status;
    }

    protected function set_email($email) {
        $this->email = $email;
    }
    protected function get_email() {
        return $this->email;
    }

    protected function set_parent_id($parent_id) {
        $this->parent_id = $parent_id;
    }
    public function get_parent_id() {
        return $this->parent_id;
    }

    public function get_search_type() {
        return $this->search_type;
    }
    public function set_search_type($search_type) {
        return $this->search_type = $search_type;
    }
    
    public function get_filename() {
        return $this->filename;
    }
    protected function set_filename($filename) {
        $this->filename = $filename;
    }
    
    public function get_min_seq_len() {
        return $this->min_seq_len;
    }
    public function get_max_seq_len() {
        return $this->max_seq_len;
    }
    protected function set_min_seq_len($min_seq_len) {
        $this->min_seq_len = $min_seq_len;
    }
    protected function set_max_seq_len($max_seq_len) {
        $this->max_seq_len = $max_seq_len;
    }

    public function mark_job_as_failed() {
        $this->set_status(__FAILED__);
        $this->set_time_completed();
    }

    public function mark_job_as_cancelled() {
        $this->set_status(__CANCELLED__);
        $this->set_time_completed();
        $this->email_cancelled();
    }

    public function mark_job_as_archived() {
        // This marks the job as archived-failed. If the job is archived but the
        // time completed is non-zero, then the job successfully completed.
        if ($this->status == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }

    public function get_child_jobs() {
        $table = $this->get_table_name();
        $jobs = array();
        return $jobs;
    }


    protected function load_job_shared($result, $params) {
        $table = $this->get_table_name();
        $this->status = $result["${table}_status"];
        $this->time_created = $result["${table}_time_created"];
        $this->time_started = $result["${table}_time_started"];
        $this->time_completed = $result["${table}_time_completed"];
        $this->pbs_number = $result["${table}_pbs_number"];
        $parent_field = "${table}_parent_id";
        if (isset($result[$parent_field]) && $result[$parent_field])
            $this->parent_id = $result[$parent_field];

        if (isset($params["${table}_search_type"]) && settings::get_diamond_enabled())
            $this->search_type = $params["${table}_search_type"];
        else
            $this->search_type = "";

        $this->filename = $params["identify_filename"];
        $this->min_seq_len = isset($params['identify_min_seq_len']) ? $params['identify_min_seq_len'] : "";
        $this->max_seq_len = isset($params['identify_max_seq_len']) ? $params['identify_max_seq_len'] : "";
    }




    protected function is_job_running() {
        $sched = settings::get_cluster_scheduler();

        $job_num = $this->pbs_number;
        $output = "";
        $exit_status = "";
        $exec = "";

        if ($sched == "slurm") {
            $exec = "squeue --job $job_num 2> /dev/null | grep $job_num";
        } else {
            $exec = "qstat $job_num 2> /dev/null | grep $job_num";
        }

        exec($exec,$output,$exit_status);

        if (count($output) == 1) {
            return true;
        } else {
            return false;
        }
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //
    
    protected function get_job_info() {
        $message = "EFI-CGFP Job ID: " . $this->get_id() . $this->eol;
        $message .= "Time Submitted: " . $this->get_time_created() . $this->eol;
        return $message;
    }

    protected abstract function get_email_started_subject();
    protected abstract function get_email_started_message();
    protected abstract function get_email_failure_subject();
    protected abstract function get_email_failure_message($result);
    protected abstract function get_email_cancelled_subject();
    protected abstract function get_email_cancelled_message();
    protected abstract function get_email_completed_subject();
    protected abstract function get_email_completed_message();
    protected abstract function get_completed_url();
    protected function get_completed_url_params() {
        return array();
    }

    protected function email_started() {
        $subject = $this->beta . $this->get_email_started_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_started_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email);
    }

    protected function email_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email);
    }

    protected function email_cancelled() {
        $subject = $this->beta . $this->get_email_cancelled_subject();

        $plain_email = "";
        $plain_email .= $this->get_email_cancelled_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email);
    }

    protected function email_admin_failure($result = "") {
        $subject = $this->beta . $this->get_email_failure_subject();

        $plain_email = "";
        $plain_email .= "FAILED TO START JOB: $result" . $this->eol . $this->eol;
        $plain_email .= $this->get_email_failure_message($result);
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $to = global_settings::get_error_admin_email();
        $this->send_email($subject, $plain_email, "", $to);
    }

    protected function email_completed($result = "") {
        $subject = $this->beta . $this->get_email_completed_subject();

        $url = $this->get_completed_url();
        $params = $this->get_completed_url_params();
        $query_params = array('id'=>$this->get_id(), 'key'=>$this->get_key());
        if (count($params)) {
            $query_params = $params;
        }
        $full_url = $url . "?" . http_build_query($query_params);

        $plain_email = "";
        $plain_email .= $this->get_email_completed_message();
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        
        $plain_email .= "Cite us:" . $this->eol . $this->eol;
        $plain_email .= "R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, ";
        $plain_email .= "\"Democratized\" genomic enzymology web tools for functional assignment, ";
        $plain_email .= "Current Opinion in Chemical Biology, Volume 47, 2018, Pages 77-85, GNT_DOI";
        $plain_email .= $this->eol . $this->eol;
        $plain_email .= "These data will only be retained for " . settings::get_retention_days() . " days." . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $this->send_email($subject, $plain_email, $full_url);
    }

    private function send_email($subject, $plain_email, $full_url = "", $to = "") {
        if ($this->beta)
            $plain_email = "Thank you for using the EFI beta site." . $this->eol . $plain_email;

        if (!$to)
            $to = $this->get_email();
        $from = "EFI CGFP <" . settings::get_admin_email() . ">";

        $html_email = nl2br($plain_email, false);

        if ($full_url) {
            $plain_email = str_replace("THE_URL", $full_url, $plain_email);
            $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);
        }

        $gnt_doi_url = "https://doi.org/10.1016/j.cbpa.2018.09.009";
        $plain_email = str_replace("GNT_DOI", $gnt_doi_url, $plain_email);
        $html_email = str_replace("GNT_DOI", "<a href=\"" . htmlentities($gnt_doi_url) . "\">" . $gnt_doi_url. "</a>", $html_email);

        $message = new Mail_mime(array("eol" => $this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From "=> $from, "Subject" => $subject);
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to, $headers, $body);
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // TIME FUNCTIONS
    //

    protected static function get_current_time() {
        return date("Y-m-d H:i:s", time());
    }

    protected function get_time_started() {
        return $this->time_started;
    }

    public function get_time_completed() {
        return $this->time_completed;
    }

    protected function get_time_created() {
        return $this->time_created;
    }

    protected function set_time_started() {
        $tableName = $this->get_table_name();
        $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_started='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_started = $current_time;
    }
    
    protected function set_time_completed($current_time = false) {
        $tableName = $this->get_table_name();
        if ($current_time === false)
            $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_completed='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_completed = $current_time;
    }
    
    protected function set_time_created() {
        $tableName = $this->get_table_name();
        $current_time = self::get_current_time();
        $sql = "UPDATE ${tableName} SET ${tableName}_time_created='" . $current_time . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        $this->time_created = $current_time;
    }



    private function update_status($status) {
        $tableName = $this->get_table_name();
        $sql = "UPDATE ${tableName} ";
        $sql .= "SET ${tableName}_status='" . $status . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
    }

    private function update_pbs_number() {
        $tableName = $this->get_table_name();
        $sql = "UPDATE ${tableName} SET ${tableName}_pbs_number='" . $this->pbs_number . "' ";
        $sql .= "WHERE ${tableName}_id='" . $this->id . "'";
        $this->db->non_select_query($sql);
    }



    // Returns Time Started/Finished
    protected function get_extra_metadata($id, $tab_data) {
        $qid_col = "quantify_identify_id";
        if (!$id) {
            $id = $this->id;
            $qid_col = "quantify_id";
        }

        //HACK: to get the num_unique_seq text right and show/hide the num_filtered_seq line
        $table = $this->get_table_name();
        $sql = "SELECT identify_params, identify_time_created, identify_time_started AS time_started, identify_time_completed AS time_completed FROM identify WHERE identify_id = $id";
        if ($table == "quantify")
            $sql = "SELECT quantify_id, quantify_time_created, quantify_time_started AS time_started, quantify_time_completed AS time_completed, identify_params FROM quantify JOIN identify ON quantify_identify_id = identify_id WHERE $qid_col = $id";
        $result = $this->db->query($sql);

        $time_data = array();

        if ($result && isset($result[0]["identify_params"])) {
            $iparams = global_functions::decode_object($result[0]["identify_params"]);
            if (isset($iparams["identify_min_seq_len"]))
                $tab_data["min_seq_len"] = $iparams["identify_min_seq_len"];
            if (isset($iparams["identify_max_seq_len"]))
                $tab_data["max_seq_len"] = $iparams["identify_max_seq_len"];

            $time_data = array("Time Started -- Finished", functions::format_short_date($result[0]["time_started"]) . " -- " .
                                                        functions::format_short_date($result[0]["time_completed"]));
        }

        return $time_data;
    }

    protected function get_metadata_shared($meta_file, $id = 0) { // for quantify jobs, pass in the identify_id

        $tab_data = self::read_kv_tab_file($meta_file);
        $table_data = array();

        $time_data = $this->get_extra_metadata($id, $tab_data);
        if (count($time_data) > 0)
            $table_data[0] = $time_data;

        $pos_start = 1;
        foreach ($tab_data as $key => $value) {
            $attr = "";
            $pos = $pos_start;
            if ($key == "time_period") {
                $attr = "Time Started/Finished";
                $pos = max(0, $pos_start - 1);
            } elseif ($key == "num_ssn_clusters") {
                $attr = "Number of SSN clusters";
                $pos = $pos_start + 0;
            } elseif ($key == "num_ssn_singletons") {
                $attr = "Number of SSN singletons";
                $pos = $pos_start + 1;
            } elseif ($key == "is_uniref") {
                $attr = "SSN sequence source";
                $value = $value ? "UniRef$value" : "UniProt";
                $pos = $pos_start + 2;
            } elseif ($key == "num_metanodes") {
                $attr = "Number of SSN (meta)nodes";
                $pos = $pos_start + 3;
            } elseif ($key == "num_raw_accessions") {
                $attr = "Number of accession IDs in SSN";
                $pos = $pos_start + 4;
            # These are included elsewhere
            #} elseif ($key == "min_seq_len" && $value != "none") {
            #    $attr = "Minimum sequence length filter";
            #    $pos = $pos_start + 7;
            #} elseif ($key == "max_seq_len" && $value != "none") {
            #    $attr = "Maximum sequence length filter";
            #    $pos = $pos_start + 8;
            } elseif ($key == "num_cdhit_clusters") {
                $attr = "Number of CD-HIT ShortBRED families";
                if ($this->parent_id)
                    $attr .= " (from parent)";
                $pos = $pos_start + 9;
            } elseif ($key == "num_markers") {
                $attr = "Number of markers";
                if ($this->parent_id)
                    $attr .= " (from parent)";
                $pos = $pos_start + 10;
            } elseif ($key == "num_cons_seq_with_hits") {
                $attr = "Number of consensus sequences with hits";
                $pos = $pos_start + 100;
            } elseif (!$this->parent_id) {
                if ($key == "num_unique_seq") {
                    $attr = "Number of unique sequences in SSN";
                    if ($tab_data["min_seq_len"] != "none" || $tab_data["max_seq_len"] != "none")
                        $attr .= " after length filter";
                    $pos = $pos_start + 6;
                } elseif ($key == "num_filtered_seq" && ($tab_data["min_seq_len"] != "none" || $tab_data["max_seq_len"] != "none")) {
                    $attr = "Number of sequences after length filter";
                    $pos = $pos_start + 5;
                }
            }

            if ($attr)
                $table_data[$pos] = array($attr, $value);
        }

        return $table_data;
    }

    private static function read_kv_tab_file($file) {
        $delim = "\t";
        $fh = fopen($file, "r");
        $data = array();
        while (!feof($fh)) {
            $line = trim(fgets($fh, 1000));
            if (!$line)
                continue;

            $row = str_getcsv($line, $delim);
            $data[$row[0]] = $row[1];
        }
        fclose($fh);
        return $data;
    }

    public function get_metadata_swissprot_singles_file_size() {
        $file_path = $this->get_metadata_swissprot_singles_file_path();
        return self::get_web_filesize($file_path);
    }
    public function get_metadata_swissprot_clusters_file_size() {
        $file_path = $this->get_metadata_swissprot_clusters_file_path();
        return self::get_web_filesize($file_path);
    }
    public function get_metadata_cluster_sizes_file_size() {
        $file_path = $this->get_metadata_cluster_sizes_file_path();
        return self::get_web_filesize($file_path);
    }
    public function get_marker_file_size() {
        $file_path = $this->get_marker_file_path();
        return self::get_web_filesize($file_path);
    }
    public function get_cdhit_file_size() {
        $file_path = $this->get_cdhit_file_path();
        return self::get_web_filesize($file_path);
    }

    protected static function get_web_filesize($file_path) {
        if (!file_exists($file_path))
            return 0;

        $size = filesize($file_path);
        $mb_size = global_functions::bytes_to_megabytes(filesize($file_path));

        if ($mb_size)
            return $mb_size;
        else
            return "<1";
    }


    public abstract function get_metadata_swissprot_singles_file_path();
    public abstract function get_metadata_swissprot_clusters_file_path();
    public abstract function get_metadata_cluster_sizes_file_path();
    public abstract function get_marker_file_path();
    public abstract function get_cdhit_file_path();

    protected function get_metadata_swissprot_singles_file_shared($results_dir) {
        return "$results_dir/swissprot_singletons.tab";
    }
    protected function get_metadata_swissprot_clusters_file_shared($results_dir) {
        return "$results_dir/swissprot_clusters.tab";
    }
    protected function get_metadata_cluster_sizes_file_shared($results_dir) {
        return "$results_dir/cluster_sizes.tab";
    }
    protected function get_marker_file_shared($results_dir) {
        return "$results_dir/markers.faa";
    }
    protected function get_cdhit_file_shared($results_dir) {
        return "$results_dir/cdhit.txt";
    }

}

?>
