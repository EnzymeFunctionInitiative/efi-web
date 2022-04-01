<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\DiagramJob;


class diagram_job extends arrow_api {

    private $beta;
    private $key;
    private $type;
    private $params;
    private $message = "";
    private $title = "";
    private $eol = PHP_EOL;
    private $db_mod = "";

    private $tax_job_id;
    private $tax_tree_id;
    private $tax_id_type;


    public function get_key() { return $this->key; }

    public function __construct($db, $id) {
        parent::__construct($db, $id, "diagram");
        $this->db = $db;
        $this->beta = settings::get_release_status();
        $this->load_job();
    }

    private function load_job() {
        $sql = "SELECT * FROM diagram WHERE diagram_id = " . $this->id;
        $result = $this->db->query($sql);
        $result = $result[0];
        $this->key = $result["diagram_key"];
        $this->email = $result["diagram_email"];
        $this->status = $result["diagram_status"];
        $this->type = $this->get_job_type($result["diagram_type"]);
        $this->title = $result["diagram_title"];
        $this->params = functions::decode_object($result["diagram_params"]);
        $this->pbs_number = $result["diagram_pbs_number"];
        if (isset($this->params["db_mod"]) && $this->params["db_mod"]) {
            // Get the actual module not the alias.
            $mod_info = global_settings::get_database_modules();
            foreach ($mod_info as $mod) {
                if ($mod[1] == $this->params["db_mod"]) {
                    $db_mod = $mod[0];
                }
            }
            $this->db_mod = $db_mod;
        }
        if (isset($this->params["tax_job_id"]) && isset($this->params["tax_id_type"]) && isset($this->params["tax_tree_id"])) {
            $this->tax_job_id = $this->params["tax_job_id"];
            $this->tax_id_type = $this->params["tax_id_type"];
            $this->tax_tree_id = $this->params["tax_tree_id"];
        }
    }

    public function process() {

        if ($this->type == DiagramJob::Uploaded || $this->type == DiagramJob::UploadedZip) {
            return $this->process_direct_zip_job();
        } elseif ($this->type == DiagramJob::BLAST) {
            return $this->process_blast_job();
        } elseif ($this->type == DiagramJob::IdLookup || $this->type == DiagramJob::FastaLookup) {
            return $this->process_lookup_job();
        } else {
            return false;
        }
    }

    private function handle_upload($is_uploaded_database = true) {
        $job_id = $this->id;

        $upload_dir = settings::get_uploads_dir();
        $out_dir = settings::get_diagram_output_dir() . "/$job_id";
        //$ext = settings::get_diagram_extension();
        $upload_prefix = settings::get_diagram_upload_prefix();
        
        $source = "$upload_dir/$upload_prefix$job_id";
        $is_zip_file = file_exists("$source.zip");
        $ext = $is_uploaded_database ? "." . ($is_zip_file ? "zip" : settings::get_diagram_extension()) : "";

        $source = "$source$ext";
        $target = "$out_dir/$job_id$ext";

        if (!file_exists($source))
            return false;

        $this->create_output_dir($out_dir);
        copy($source, $target);

        return $target;
    }

    private function create_output_dir($out_dir) {
        if (@file_exists($out_dir))
            functions::rrmdir($out_dir);
        if (!file_exists($out_dir))
            mkdir($out_dir);
        chdir($out_dir);
    }

    private function process_direct_zip_job() {
        $is_uploaded = true;
        $target = $this->handle_upload($is_uploaded);

        $args = " -zip-file \"$target\"";

        return $this->execute_job($args);
    }

    private function process_blast_job() {
        if (!array_key_exists("blast_seq", $this->params) || strlen($this->params["blast_seq"]) == 0) {
            $this->message = "BLAST sequence is invalid.";
            return false;
        }

        $out_dir = settings::get_diagram_output_dir() . "/" . $this->id;
        $this->create_output_dir($out_dir);

        $args = " -blast \"" . $this->params["blast_seq"] . "\"";
        $args .= " -evalue " . $this->params["evalue"];
        $args .= " -max-seq " . $this->params["max_num_sequence"];
        if (isset($this->params["seq_db_type"]))
            $args .= " -seq-db-type " . $this->params["seq_db_type"];

        return $this->execute_job($args);
    }

    private function process_lookup_job() {
        $job_id = $this->id;

        $out_dir = settings::get_diagram_output_dir() . "/" . $this->id;
        $this->create_output_dir($out_dir);

        if ($this->tax_job_id && $this->tax_id_type && isset($this->tax_tree_id)) {
            $tax_dir = global_settings::get_est_results_dir() . "/" . $this->tax_job_id . "/output";
            $tax_file = "$tax_dir/tax.json";
            $target = "$out_dir/$job_id.json";
            copy($tax_file, $target);

            $args = " --tax-file \"$target\" --tax-id-type $this->tax_id_type --tax-tree-id $this->tax_tree_id";
            if (isset($this->params["seq_db_type"]))
                $args .= " -seq-db-type " . $this->params["seq_db_type"];
        } else {
            $upload_dir = settings::get_uploads_dir();
            $upload_prefix = settings::get_diagram_upload_prefix();
            $upload_source = "$upload_dir/$upload_prefix$job_id.txt";
            $source = "$out_dir/$job_id.txt";
            copy($upload_source, $source);

            $args = "";
            if ($this->type == DiagramJob::IdLookup) {
                $args = " -id-file \"$source\"";
                if (isset($this->params["seq_db_type"]))
                    $args .= " -seq-db-type " . $this->params["seq_db_type"];
            } elseif ($this->type == DiagramJob::FastaLookup) {
                $args = " -fasta-file \"$source\"";
            }
        }

        return $this->execute_job($args);
    }

    private function execute_job($commandLine) {
        
        $target = $this->get_output_file();
        $sched = settings::get_cluster_scheduler();

        $binary = settings::get_process_diagram_script();
        $exec = "source /etc/profile\n";
        $exec .= "module load " . settings::get_gnn_module() . "\n";
        if ($this->db_mod)
            $exec .= "module load " . $this->db_mod . "\n";
        else
            $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= $binary . " ";
        $exec .= $commandLine;
        $exec .= " -output \"$target\"";
        if ($this->title)
            $exec .= " -title \"" . $this->title . "\"";
        if ($this->type)
            $exec .= " -job-type \"" . $this->type . "\"";
        if (array_key_exists("neighborhood_size", $this->params) && $this->params["neighborhood_size"])
            $exec .= " -nb-size " . $this->params["neighborhood_size"];
        if ($sched)
            $exec .= " -scheduler $sched";
        $exec .= " -job-id " . $this->id;

        print $exec;
        print "\n\n";

        $exit_status = 1;
        $output_array = array();
        $output = exec($exec, $output_array, $exit_status);
        $output = trim(rtrim($output));

        if ($sched == "slurm")
            $pbs_job_number = $output;
        else
            $pbs_job_number = substr($output, 0, strpos($output, "."));

        if (!$exit_status) {
            $update_sql = "UPDATE diagram " .
                "SET diagram_status = '" . __RUNNING__ . "', diagram_pbs_number = $pbs_job_number " .
                "WHERE diagram_id = " . $this->id;
            $this->db->non_select_query($update_sql);
            $this->email_started();
        } else {
            $current_time = date("Y-m-d H:i:s", time());
            $this->db->non_select_query("UPDATE diagram SET diagram_status = '" . __FAILED__ . "', " .
                                        "diagram_time_completed = '$current_time' WHERE diagram_id = " . $this->id);
            $this->email_failed();
            error_log("Error: $output");
        }

        return $exit_status == 0;
    }

    public function check_if_job_is_done() {

        $out_dir = $this->get_output_dir();
        $is_done = file_exists("$out_dir/" . DiagramJob::JobCompleted) || !$this->check_pbs_running();
        $is_error = file_exists("$out_dir/" . DiagramJob::JobError) || !file_exists($this->get_output_file());

        if ($is_done) {
            $current_time = date("Y-m-d H:i:s", time());
            
            $status = __FINISH__;
            if ($is_error)
                $status = __FAILED__;
            print $this->id . " has completed and has status = $status.\n";

            $this->db->non_select_query("UPDATE diagram SET diagram_status = '$status', " .
                "diagram_time_completed = '$current_time' WHERE diagram_id = " . $this->id);
            $this->set_diagram_version();

            if ($is_error)
                $this->email_failed();
            else
                $this->email_complete();
        }
    }

    protected function update_results_object($data) {
        $result = global_functions::update_results_object_tmpl($this->db, "diagram", "diagram", "results", $this->get_id(), $data);
        return $result;
    }

    private function email_failed() {

        $message = "";
        if ($this->type == DiagramJob::BLAST) {
            $message = "Check your input FASTA sequence.";
        }

        $email_title_bit = "failed to be retrieved";
        $email_body = "";
        $email_body .= "There was an error retrieving the neighborhood data for the job (job #" . $this->id . ").";
        if ($message)
            $email_body .= $this->eol . $message;
        $email_body .= $this->eol . $this->eol;

        $this->email_shared($email_title_bit, $email_body);
    }

    private function email_complete() {

        $email_title_bit = "ready";
        $email_body = "";
        $email_body .= "The diagram data file you uploaded is ready to be viewed at ";
        $email_body .= "THE_URL" . $this->eol . $this->eol;
        $email_body .= "These data will only be retained for " . settings::get_retention_days() . " days." . $this->eol . $this->eol;

        $this->email_shared($email_title_bit, $email_body);
    }

    private function email_started() {

        $email_title_bit = "started";
        $email_body = "";
        $email_body .= "The uploaded diagram data has started computation.";
        $email_body .= $this->eol . $this->eol;

        $this->email_shared($email_title_bit, $email_body);
    }

    private function email_shared($titleBit, $message) {
        $queryType = $this->get_query_string_id_field();
        $subject = $this->beta . "EFI-GNT - GNN diagrams $titleBit";
        $to = $this->email;
        $from = settings::get_admin_email();
        $from_name = "EFI GNT";

        if ($this->get_diagram_version() >= 3)
            $url = settings::get_web_root() . "/view_diagrams_v3.php";
        else
            $url = settings::get_web_root() . "/view_diagrams.php";
        $full_url = $url . "?" . http_build_query(array($queryType => $this->id, 'key' => $this->key));

        $plain_email = "";

        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-GNT." . $this->eol;

        //plain text email
        $plain_email .= $message;
        $plain_email .= settings::get_email_footer();

        $html_email = nl2br($plain_email, false);

        $plain_email = str_replace("THE_URL", $full_url, $plain_email);
        $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);

        \efi\email::send_email($to, $from, $subject, $plain_email, $html_email, $from_name);
    }

    private function get_job_type($type) {
        $type = strtoupper($type);
        if ($type == DiagramJob::Uploaded)
            return DiagramJob::Uploaded;
        elseif ($type == DiagramJob::UploadedZip)
            return DiagramJob::UploadedZip;
        elseif ($type == DiagramJob::BLAST)
            return DiagramJob::BLAST;
        elseif ($type == DiagramJob::IdLookup)
            return DiagramJob::IdLookup;
        elseif ($type == DiagramJob::FastaLookup)
            return DiagramJob::FastaLookup;
        elseif ($type == DiagramJob::GNN)
            return DiagramJob::GNN;
        else
            return DiagramJob::UNKNOWN;
    }

    private function get_query_string_id_field() {
        return functions::get_diagram_id_field($this->type);
    }

    private function get_output_file() {
        $out_dir = $this->get_output_dir();
        $target = "$out_dir/" . $this->id . "." . settings::get_diagram_extension();
        return $target;
    }

    public function get_output_dir($id = 0) {
        $out_dir = settings::get_diagram_output_dir() . "/" . $this->id;
        return $out_dir;
    }

    public function get_message() {
        return $this->message;
    }
}


