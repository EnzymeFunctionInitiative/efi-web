<?php

require_once("functions.class.inc.php");
require_once("settings.class.inc.php");
require_once("job_shared.class.inc.php");
require_once("identify.class.inc.php");

abstract class quantify_shared extends job_shared {

    protected $identify_id;
    protected $metagenome_ids;
    protected $ref_db = "";
    protected $identify_search_type = "";
    protected $identify_diamond_sens = "";
    protected $identify_cdhit_sid = "";
    protected $identify_parent_id = 0;
    protected $mg_db_name = "";
    protected $mg_db_id = 0;

    
    
    
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


    public function __construct($db) {
        parent::__construct($db);
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
        $query_params = array('id' => $this->identify_id, 'quantify-id' => $this->get_id(), 'key' => $this->get_key());
        return $query_params;
    }

    protected function get_job_info() {
        $message = "EFI-CGFP Job ID: " . $this->identify_id . $this->eol;
        $message .= "Quantify ID: " . $this->get_id() . $this->eol;
        $message .= "Time Submitted: " . $this->get_time_created() . $this->eol;
        return $message;
    }






    public abstract function get_identify_output_path($parent_id = 0);
    protected abstract function get_quantify_output_path();
    protected abstract function get_ssn_file_path_shared();
    public abstract function get_metadata();

    public function get_protein_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_protein_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }
    public function get_cluster_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_cluster_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }
    public function get_normalized_protein_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_normalized_protein_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }
    public function get_normalized_cluster_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_normalized_cluster_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }
    public function get_genome_normalized_protein_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_genome_normalized_protein_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }
    public function get_genome_normalized_cluster_file_path($use_mean = false) {
        $path = $this->get_quantify_output_path() . "/" . self::get_genome_normalized_cluster_file_name();
        if ($use_mean)
            $path .= ".mean";
        return $path;
    }

    public function get_protein_file_size($use_mean = false) {
        $path = $this->get_protein_file_path($use_mean);
        return self::get_web_filesize($path);
    }
    public function get_cluster_file_size($use_mean = false) {
        $path = $this->get_cluster_file_path($use_mean);
        return self::get_web_filesize($path);
    }
    public function get_normalized_protein_file_size($use_mean = false) {
        $path = $this->get_normalized_protein_file_path($use_mean);
        return self::get_web_filesize($path);
    }
    public function get_normalized_cluster_file_size($use_mean = false) {
        $path = $this->get_normalized_cluster_file_path($use_mean);
        return self::get_web_filesize($path);
    }
    public function get_genome_normalized_protein_file_size($use_mean = false) {
        $path = $this->get_genome_normalized_protein_file_path($use_mean);
        return self::get_web_filesize($path);
    }
    public function get_genome_normalized_cluster_file_size($use_mean = false) {
        $path = $this->get_genome_normalized_cluster_file_path($use_mean);
        return self::get_web_filesize($path);
    }

    public static function get_protein_file_name() {
        return "protein_abundance.txt";
    }
    public static function get_normalized_protein_file_name() {
        return "protein_abundance_normalized.txt";
    }
    public static function get_genome_normalized_protein_file_name() {
        return "protein_abundance_genome_normalized.txt";
    }
    public static function get_cluster_file_name() {
        return "cluster_abundance.txt";
    }
    public static function get_normalized_cluster_file_name() {
        return "cluster_abundance_normalized.txt";
    }
    public static function get_genome_normalized_cluster_file_name() {
        return "cluster_abundance_genome_normalized.txt";
    }

    public function get_ssn_zip_file_path() {
        $path = $this->get_ssn_file_path_shared() . ".zip";
        return $path;
    }
    public function get_ssn_file_size() {
        $file = $this->get_ssn_file_path_shared();
        return self::get_web_filesize($file);
    }
    public function get_ssn_zip_file_size() {
        $file = $this->get_ssn_zip_file_path();
        return self::get_web_filesize($file);
    }

    protected function get_table_name() {
        return job_types::Quantify;
    }


    public function get_metadata_swissprot_singles_file_path() {
        $res_dir = $this->get_identify_output_path();
        return $this->get_metadata_swissprot_singles_file_shared($res_dir);
    }

    public function get_metadata_swissprot_clusters_file_path() {
        $res_dir = $this->get_identify_output_path();
        return $this->get_metadata_swissprot_clusters_file_shared($res_dir);
    }

    public function get_metadata_cluster_sizes_file_path() {
        $res_dir = $this->get_identify_output_path();
        return $this->get_metadata_cluster_sizes_file_shared($res_dir);
    }
    
    public function get_cdhit_file_path() {
        $res_dir = $this->get_identify_output_path();
        return $this->get_cdhit_file_shared($res_dir);
    }

    public function get_marker_file_path() {
        $res_dir = $this->get_identify_output_path($this->identify_parent_id);
        return $this->get_marker_file_shared($res_dir);
    }


    public function get_metagenome_data() {

        $mg_data = array();
    
        $clust_file = $this->get_genome_normalized_cluster_file_path();
    
        if (!file_exists($clust_file)) {
            $mgs = $this->get_metagenome_ids();
            foreach ($mgs as $mg_id) {
                array_push($mg_data, array($mg_id, ""));
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
}

?>
