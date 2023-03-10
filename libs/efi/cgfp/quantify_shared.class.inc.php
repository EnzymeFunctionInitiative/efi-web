<?php
namespace efi\cgfp;

require_once(__DIR__."/../../../init.php");

use \efi\cgfp\functions;
use \efi\cgfp\settings;
use \efi\cgfp\identify;
use \efi\cgfp\cgfp_shared;
use \efi\file_types;


abstract class quantify_shared extends cgfp_shared {

    protected $metagenome_ids;
    protected $ref_db = "";
    protected $identify_search_type = "";
    protected $identify_diamond_sens = "";
    protected $identify_cdhit_sid = "";
    protected $identify_parent_id = 0;
    protected $mg_db_name = "";
    protected $mg_db_id = 0;
    protected $job_name = "";

    
    
    
    public function get_metagenome_ids() {
        return $this->metagenome_ids;
    }
    public function get_ref_db() {
        return $this->ref_db;
    }
    public function get_identify_search_type() {
        return $this->identify_search_type;
    }
    public function get_diamond_sensitivity() {
        return $this->identify_diamond_sens;
    }
    public function get_identify_cdhit_sid() {
        return $this->identify_cdhit_sid;
    }
    public function get_metagenome_db_name() {
        return $this->mg_db_name;
    }
    public function get_metagenome_db_id() {
        return $this->mg_db_id;
    }
    public function get_job_name() {
        return $this->job_name;
    }


    public function __construct($db, $is_example = false, $is_debug = false) {
        parent::__construct($db, job_types::Quantify, $is_example, $is_debug);
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // EMAIL FUNCTIONS
    //

    protected function get_email_started_subject() {
        $subject = "EFI-CGFP - Quantify marker submission received";
        return $subject;
    }

    protected function get_email_started_message() {
        $plain_email = "";
        $plain_email .= "The markers are being quantified against the selected metagenome(s).";
        $plain_email .= $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_cancelled_subject() {
        $subject = "EFI-CGFP - Job cancelled";
        return $subject;
    }

    protected function get_email_cancelled_message() {
        $plain_email = "";
        $plain_email .= "The ShortBRED-Quantify job was cancelled." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_failure_subject() {
        $subject = "EFI-CGFP - Quantify marker computation failed";
        return $subject;
    }

    protected function get_email_failure_message($result) {
        $plain_email = "";
        $plain_email .= "The ShortBRED marker quantify computation failed.  ";
        if ($result) {
            $plain_email .= "Reason: $result" . $this->eol . $this->eol;
        }
        $plain_email .= "Please contact us for further assistance." . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_email_completed_subject() {
        $subject = "EFI-CGFP - ShortBRED quantify marker computation completed";
        return $subject;
    }

    protected function get_email_completed_message() {
        $plain_email = "";
        $plain_email .= "The ShortBRED  quantify marker computation succeeded." . $this->eol . $this->eol;
        $plain_email .= "To view results, go to THE_URL" . $this->eol . $this->eol;
        return $plain_email;
    }

    protected function get_completed_url() {
        $url = settings::get_web_root() . "/stepe.php";
        return $url;
    }

    protected function get_completed_url_params() {
        $query_params = array('id' => $this->get_identify_id(), 'quantify-id' => $this->get_id(), 'key' => $this->get_key());
        return $query_params;
    }

    protected function get_job_info() {
        $message = "EFI-CGFP Job ID: " . $this->get_identify_id() . $this->eol;
        $message .= "Quantify ID: " . $this->get_id() . $this->eol;
        return $message;
    }



    protected abstract function load_job();



    public abstract function get_metadata();

    public function get_metagenome_data() {

        $mg_data = array();
    
        $clust_file = $this->get_file_path(file_types::FT_sbq_cluster_abundance_genome_norm_median); //get_genome_normalized_cluster_file_path();
    
        if (!file_exists($clust_file)) {
            $mgs = $this->get_metagenome_ids();
            $site_info = metagenome_db_manager::get_metagenome_db_site_info($this->mg_db_id);
            foreach ($mgs as $mg_id) {
                array_push($mg_data, array($mg_id, $site_info["site"][$mg_id]));
            }
            return $mg_data;
        }
    
        $fh = fopen($clust_file, "r");
    
        if ($fh) {
            $header_line = trim(fgets($fh));
            $headers = explode("\t", $header_line);
            $start_idx = 1;
            if (in_array("Cluster Size", $headers))
                $start_idx = 2;
        
            $site_info = metagenome_db_manager::get_metagenome_db_site_info($this->mg_db_id);
            $metagenomes_hdr = array_slice($headers, $start_idx);
            foreach ($metagenomes_hdr as $mg_id) {
                $info = array($mg_id, "");
                if (isset($site_info["site"][$mg_id]))
                    $info[1] = $site_info["site"][$mg_id];
                array_push($mg_data, $info);
            }
        }
        fclose($fh);
    
        return $mg_data;
    }

    public function get_metagenome_info_as_text() {
        $text_data = "Metagenome ID\tBody Site\n";
        $mg_data = $this->get_metagenome_data();
        foreach ($mg_data as $row) {
            $mg_id = $row[0];
            $bodysite = $row[1];
            $text_data .= "$mg_id\t$bodysite\n";
        }
        return $text_data;
    }

    public function get_file_size($file_type) {
        return $this->get_file_size_base($file_type, self::Quantify);
    }
    public function get_file_path($file_type) {
        return $this->get_file_path_base($file_type, self::Quantify);
    }
    public function get_file_name($file_type) {
        return $this->get_file_name_base($file_type, self::Quantify);
    }
}


