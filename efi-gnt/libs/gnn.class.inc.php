<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__DIR__."/settings.class.inc.php");
require_once(__DIR__."/gnn_shared.class.inc.php");
require_once(__DIR__."/functions.class.inc.php");
require_once(__BASE_DIR__."/vendor/autoload.php");

require_once("Mail.php");
require_once("Mail/mime.php");


class gnn extends gnn_shared {

    const SEQ_UNIPROT = 1;
    const SEQ_UNIREF50 = 2;
    const SEQ_UNIREF90 = 3;
    const SEQ_UNIPROT_DOMAIN = 4;
    const SEQ_DOMAIN = 1;
    const SEQ_NO_DOMAIN = 2;

    ////////////////Private Variables//////////

    protected $email;
    protected $key;
    protected $filename;
    protected $basefilename;
    protected $size;
    protected $cooccurrence;
    protected $ssn_nodes;
    protected $ssn_edges;
    protected $gnn_nodes;
    protected $gnn_edges;
    protected $gnn_pfams;
    protected $log_file = "log.txt";
    protected $eol = PHP_EOL;
    protected $finish_file = "gnn.completed";
    protected $beta;
    protected $est_id = 0; // gnn_est_source_id, we use the EST job with this analysis ID
    protected $gnn_parent_id = 0; // parent GNT job
    // If gnn_parent_id is set, then if this is true the job is to use the 
    // existing SSN. If false, then the job is to use the new uploaded SSN with 
    // the existing job data.
    protected $child_filter_only = false; 
    protected $db_mod = "";
    protected $extra_ram = false;

    private $is_sync = false;

    ///////////////Public Functions///////////

    public function __construct($db, $id = 0, $is_sync = false, $load_gnn = true) {
        parent::__construct($db, $id, "gnn");

        if ($id && $load_gnn) {
            $this->load_gnn($id);
        }

        $this->beta = settings::get_release_status();
        $this->is_sync = $is_sync;
    }

    public function __destruct() {
    }

    public function get_email() { return $this->email; }
    public function get_key() { return $this->key; }
    public function get_size() { return $this->size; }
    public function get_cooccurrence() { return $this->cooccurrence; }
    public function get_filename() { return $this->filename; }
    public function get_ssn_nodes() { return $this->ssn_nodes; }
    public function get_ssn_edges() { return $this->ssn_edges; }
    public function get_gnn_nodes() { return $this->gnn_nodes; }
    public function get_gnn_edges() { return $this->gnn_edges; }
    public function get_source_id() { return $this->est_id; }
    public function get_gnn_name() { return $this->basefilename; }
    public function has_parent() { return $this->gnn_parent_id > 0; }

    private function get_full_input_ssn_path() {
        // If we are originating from an EST job, this field contains the full filename.
        if ($this->est_id) {
            return $this->filename; 

        // If we did a child job, and we are doing a re-filter only (e.g. using the same parent
        // SSN with different cooccurrence/window) then we return the parent SSN.
        } elseif ($this->gnn_parent_id && $this->child_filter_only) {
            $dir = $this->get_output_dir($this->gnn_parent_id);
            return $dir . "/" . $this->gnn_parent_id . ".xgmml";
            
        // Otherwise we return the uploaded file.
        } else {
            $uploads_dir = settings::get_uploads_dir();
            return $uploads_dir . "/" . $this->get_id() . "." . pathinfo($this->filename, PATHINFO_EXTENSION);
        }
    }
    
    protected function show_summary_details() {
        return true;
    }

    public function get_source_info() {
        if (!$this->est_id && !$this->gnn_parent_id)
            return false;
        elseif ($this->gnn_parent_id)
            return functions::get_gnn_key($this->db, $this->gnn_parent_id);
        else
            return functions::get_est_job_info_from_est_id($this->db, $this->est_id);
    }

    public function get_gnn_parent_id() {
        if ($this->gnn_parent_id)
            return $this->gnn_parent_id;
        else
            return false;
    }

    public function unzip_file() { 
        $file = $this->get_full_input_ssn_path();
        $parts = pathinfo($file);
        $ext = strtolower($parts['extension']);
        $dir = $parts['dirname'];
        $name = $parts['filename'];

        $outfile = $file;

        if ($ext == "zip") {
            $outfile = "$dir/$name.xgmml";
            $exec = "unzip -p $file > $outfile";
            $output_array = array();
            $output = exec($exec, $output_array, $exit_status);
        } else if ($ext == "gz") {
            $outfile = "$dir/$name.xgmml";
            $exec = "gunzip $file > $outfile";
            $output_array = array();
            $output = exec($exec, $output_array, $exit_status);
        }

        return $outfile; 
    }


    public function run_gnn_sync($is_debug = false) {
        $this->delete_outputs();
        $this->set_time_started();
        $id = $this->get_id();

        $output_dir = $this->get_output_dir();
        $ssnin = $this->unzip_file();
        $target_ssnin = $this->do_run_file_actions($ssnin, $is_debug);

        $exec = $this->get_run_exec_cmd($target_ssnin);

        $output_array = array();
        $output = exec($exec,$output_array,$exit_status);
        $output = trim(rtrim($output));

        $script = "$output_dir/submit_gnn.sh";
        $output = shell_exec($script);
        $error = 0;

        $this->set_time_completed();
        $formatted_output = implode("\n",$output_array);

        file_put_contents($this->get_log_file(), $exec . "\n");
        file_put_contents($this->get_log_file(), $formatted_output, FILE_APPEND);

        if ($error == 1) {
            return array('RESULT' => false, 'MESSAGE' => "The job crashed.");
        } elseif ($error == 2) {
            return array('RESULT' => false, 'MESSAGE' => "The job ran too long.");
        } else {
            $this->set_gnn_stats();
            $this->set_ssn_stats();
            return array('RESULT' => true, 'MESSAGE' => "");
        }
    }

    public function run_gnn_async($is_debug = false) {

        $ssnin = $this->get_full_input_ssn_path();
        // Copy SSN to target dir if necessary.  target_ssnin now points to the copied file rather
        // than the uploaded file.
        $target_ssnin = $this->do_run_file_actions($ssnin, $is_debug);

        $exec = $this->get_run_exec_cmd($target_ssnin);

        $exit_status = 1;

        file_put_contents($this->get_log_file(), $exec . "\n");

        $output_array = array();
        $output = exec($exec, $output_array, $exit_status);
        $output = trim(rtrim($output));

        $sched = settings::get_cluster_scheduler();
        if ($sched == "slurm")
            $pbs_job_number = $output;
        else
            $pbs_job_number = substr($output, 0, strpos($output, "."));

        if ($pbs_job_number && !$exit_status) {
            if (!$is_debug) {
                $this->set_pbs_number($pbs_job_number);
                $this->set_time_started();
                $this->email_started();
                $this->set_status(__RUNNING__);
                if ($this->est_id) {
                    $this->update_est_job_file_field($ssnin);
                }
            }

            return array('RESULT' => true, 'PBS_NUMBER' => $pbs_job_number, 'EXIT_STATUS' => $exit_status, 'MESSAGE' => 'Job Successfully Submitted');
        }
        else {
            return array('RESULT' => false, 'EXIT_STATUS' => $exit_status, 'MESSAGE' => $output_array[18]);
        }
    }

    private function do_run_file_actions($ssnin, $is_debug) {
        if ($this->is_sync)
            $out_dir = settings::get_sync_output_dir();
        else
            $out_dir = settings::get_output_dir();

        $out_dir .= "/" . $this->get_id();
        $target_ssnin = $out_dir . "/" . $this->get_id() . "." . pathinfo($ssnin, PATHINFO_EXTENSION);

        if (@file_exists($out_dir))
            functions::rrmdir($out_dir);
        if (!$is_debug && !file_exists($out_dir))
            mkdir($out_dir);

        chdir($out_dir);
        copy($ssnin, $target_ssnin);

        return $target_ssnin;
    }

    private function get_run_exec_cmd($target_ssnin) {
        $sched = settings::get_cluster_scheduler();
        $queue = settings::get_memory_queue();
        $binary = settings::get_gnn_script();
        $sync_binary = settings::get_sync_gnn_script();

        $want_clusters = true;
        $want_singles = false;
        
        $output_dir = $this->get_output_dir();

        $exec = "source /etc/profile\n";
        if ($this->db_mod)
            $exec .= "module load " . $this->db_mod . "\n";
        else
            $exec .= "module load " . settings::get_efidb_module() . "\n";
        $exec .= "module load " . settings::get_gnn_module() . "\n";

        if ($this->is_sync) {
            $exec .= $sync_binary . " ";
        } else {
            $exec .= $binary . " ";
        }
        $exec .= " -output-dir \"$output_dir\"";
        $exec .= " -queue " . $queue;
        $exec .= " -ssnin \"" . $target_ssnin . "\"";
        $exec .= " -nb-size " . $this->get_size();
        $exec .= " -cooc " . $this->get_cooccurrence();
        $exec .= " -gnn \"" . $this->get_gnn() . "\"";
        $exec .= " -ssnout \"" . $this->get_color_ssn() . "\"";
        $exec .= " -stats \"" . $this->get_stats_file() . "\"";
        $exec .= " -cluster-sizes \"" . $this->get_cluster_sizes_file() . "\"";
        $exec .= " -sp-clusters-desc \"" . $this->get_swissprot_desc_file($want_clusters) . "\"";
        $exec .= " -sp-singletons-desc \"" . $this->get_swissprot_desc_file($want_singles) . "\"";
        $exec .= " -warning-file \"" . $this->get_warning_file() . "\"";
        $exec .= " -pfam \"" . $this->get_pfam_hub() . "\"";
        $exec .= " -id-out \"" . $this->get_id_table_file() . "\"";
        $exec .= " -id-out-domain \"" . $this->get_id_table_file(true) . "\"";
        if ($this->extra_ram)
            $exec .= " -extra-ram";
        
        if (!$this->is_sync) {
            $exec .= " -pfam-zip \"" . $this->get_pfam_data_zip_file() . "\"";
            $exec .= " -all-pfam-zip \"" . $this->get_all_pfam_data_zip_file() . "\"";
            $exec .= " -split-pfam-zip \"" . $this->get_split_pfam_data_zip_file() . "\"";
            $exec .= " -all-split-pfam-zip \"" . $this->get_all_split_pfam_data_zip_file() . "\"";
            $exec .= " -uniprot-id-zip \"" . $this->get_cluster_data_zip_file(self::SEQ_NO_DOMAIN, self::SEQ_UNIPROT) . "\"";
            $exec .= " -uniprot-domain-id-zip \"" . $this->get_cluster_data_zip_file(gnn::SEQ_DOMAIN, self::SEQ_UNIPROT) . "\"";
            $exec .= " -uniref50-id-zip \"" . $this->get_cluster_data_zip_file(self::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF50) . "\"";
            $exec .= " -uniref50-domain-id-zip \"" . $this->get_cluster_data_zip_file(self::SEQ_DOMAIN, gnn::SEQ_UNIREF50) . "\"";
            $exec .= " -uniref90-id-zip \"" . $this->get_cluster_data_zip_file(self::SEQ_NO_DOMAIN, gnn::SEQ_UNIREF90) . "\"";
            $exec .= " -uniref90-domain-id-zip \"" . $this->get_cluster_data_zip_file(self::SEQ_DOMAIN, gnn::SEQ_UNIREF90) . "\"";
            $exec .= " -none-zip \"" . $this->get_pfam_none_zip_file() . "\"";
            $exec .= " -fasta-zip \"" . $this->get_fasta_zip_file(self::SEQ_NO_DOMAIN, self::SEQ_UNIPROT) . "\"";
            $exec .= " -fasta-domain-zip \"" . $this->get_fasta_zip_file(self::SEQ_DOMAIN, self::SEQ_UNIPROT) . "\"";
            $exec .= " -fasta-uniref90-zip \"" . $this->get_fasta_zip_file(self::SEQ_NO_DOMAIN, self::SEQ_UNIREF90) . "\"";
            $exec .= " -fasta-uniref90-domain-zip \"" . $this->get_fasta_zip_file(self::SEQ_DOMAIN, self::SEQ_UNIREF90) . "\"";
            $exec .= " -fasta-uniref50-zip \"" . $this->get_fasta_zip_file(self::SEQ_NO_DOMAIN, self::SEQ_UNIREF50) . "\"";
            $exec .= " -fasta-uniref50-domain-zip \"" . $this->get_fasta_zip_file(self::SEQ_DOMAIN, self::SEQ_UNIREF50) . "\"";
            $exec .= " -arrow-file \"" . $this->get_diagram_data_file() . "\"";
            $exec .= " -cooc-table \"" . $this->get_cooc_table_file() . "\"";
            $exec .= " -hub-count-file \"" . $this->get_hub_count_file() . "\"";
            if ($this->gnn_parent_id)
                $exec .= " -parent-dir " . $this->get_parent_dir();
        }
        if ($sched)
            $exec .= " -scheduler $sched";
        $exec .= " -job-id " . $this->get_id();

        return $exec;
    }

    public function complete_gnn() {
        $this->set_gnn_stats();
        $this->set_ssn_stats();
        $this->set_diagram_version();

        $this->set_status(__FINISH__);
        $this->set_time_completed();

        $this->email_complete();
    }

    public function error_gnn() {
        $this->set_status(__FAILED__);
        $this->email_error();
    }

    public function get_file_prefix() {
        return $this->get_id() . "_" . $this->basefilename;
    }

    public function get_log_file() {
        $filename = $this->log_file;
        $output_dir = $this->get_output_dir();
        $full_path = $output_dir . "/" . $filename;
        return $full_path;
    }

    public function get_color_ssn() {
        $name = "coloredssn";
        return $this->shared_get_full_file_path("_${name}", ".xgmml");
    }
    public function get_relative_color_ssn() {
        $name = "coloredssn";
        return $this->shared_get_relative_file_path("_${name}", ".xgmml");
    }
    public function get_color_ssn_zip() {
        $name = "coloredssn";
        return $this->shared_get_full_file_path("_${name}", ".zip");
    }

    public function get_gnn() {
        $name = "ssn_cluster_gnn";
        return $this->shared_get_full_file_path("_${name}", ".xgmml");
    }
    public function get_relative_gnn() {
        $name = "ssn_cluster_gnn";
        return $this->shared_get_relative_file_path("_${name}", ".xgmml");
    }
    public function get_gnn_zip() {
        $name = "ssn_cluster_gnn";
        return $this->shared_get_full_file_path("_${name}", ".zip");
    }

    public function get_pfam_hub() {
        $name = "pfam_family_gnn";
        return $this->shared_get_full_file_path("_${name}", ".xgmml");
    }
    public function get_relative_pfam_hub() {
        $name = "pfam_family_gnn";
        return $this->shared_get_relative_file_path("_${name}", ".xgmml");
    }
    public function get_pfam_hub_zipfile() {
        $name = "pfam_family_gnn";
        return $this->shared_get_full_file_path("_${name}", ".zip");
    }

    public function get_warning_file() {
        return $this->shared_get_full_file_path("_nomatches_noneighbors", ".txt");
    }
    public function get_relative_warning_file() {
        return $this->shared_get_relative_file_path("_nomatches_noneighbors", ".txt");
    }

    public function get_parent_dir() {
        $output_dir = $this->get_output_dir($this->gnn_parent_id);
        return $output_dir;
    }
    private function shared_get_dir($name) {
        $output_dir = $this->get_output_dir();
        $full_path = $output_dir . "/" . $name;
        return $full_path;
    }

    public function get_relative_color_ssn_zip_file() {
        $ssnFile = $this->get_relative_color_ssn();
        return preg_replace("/\.xgmml$/", ".zip", $ssnFile);
    }
    public function get_relative_gnn_zip_file() {
        $gnnFile = $this->get_relative_gnn();
        return preg_replace("/\.xgmml$/", ".zip", $gnnFile);
    }
    public function get_relative_pfam_hub_zip_file() {
        $pfamFile = $this->get_relative_pfam_hub();
        return preg_replace("/\.xgmml$/", ".zip", $pfamFile);
    }
    public function get_cluster_data_zip_file($domain_type, $seq_type) {
        $filename = self::get_data_zip_file_name($domain_type, $seq_type);
        return $this->shared_get_full_file_path("_${filename}_IDs", ".zip");
    }
    private static function get_data_zip_file_name($domain_type, $seq_type) {
        $type_suffix = $seq_type == self::SEQ_UNIREF50 ? "UniRef50" : ($seq_type == self::SEQ_UNIREF90 ? "UniRef90" : "UniProt");
        $dom_suffix = $domain_type == self::SEQ_DOMAIN ? "_Domain" : "";
        return "$type_suffix$dom_suffix";
    }
    public function get_relative_cluster_data_zip_file($domain_type, $seq_type) {
        if (!file_exists($this->get_cluster_data_zip_file($domain_type, $seq_type)))
            return "";
        $filename = self::get_data_zip_file_name($domain_type, $seq_type);
        return $this->shared_get_relative_file_path("_${filename}_IDs", ".zip");
    }

    public function get_fasta_zip_file($dom_type, $seq_type) {
        $suffix = $this->get_fasta_file_suffix($dom_type, $seq_type);
        return $this->shared_get_full_file_path("_FASTA$suffix", ".zip");
    }
    private function get_fasta_file_suffix($dom_type, $seq_type) {
        $dom_suffix = $dom_type == self::SEQ_DOMAIN ? "_Domain" : "";
        $type_suffix = $seq_type == self::SEQ_UNIREF90 ? "_UniRef90" : ($seq_type == self::SEQ_UNIREF50 ? "_UniRef50" : "");
        return "$type_suffix$dom_suffix";
    }
    public function get_relative_fasta_zip_file($dom_type, $seq_type) {
        $suffix = $this->get_fasta_file_suffix($dom_type, $seq_type);
        return $this->shared_get_relative_file_path("_FASTA$suffix", ".zip");
    }

    public function get_pfam_none_zip_file() {
        return $this->shared_get_full_file_path("_no_pfam_neighbors", ".zip");
    }
    public function get_relative_pfam_none_zip_file() {
        return $this->shared_get_relative_file_path("_no_pfam_neighbors", ".zip");
    }

    public function get_pfam_data_zip_file() {
        return $this->shared_get_full_file_path("_pfam_mapping", ".zip");
    }
    public function get_relative_pfam_data_zip_file() {
        return $this->shared_get_relative_file_path("_pfam_mapping", ".zip");
    }

    public function get_all_pfam_data_zip_file() {
        return $this->shared_get_full_file_path("_all_pfam_mapping", ".zip");
    }
    public function get_relative_all_pfam_data_zip_file() {
        $full_file = $this->get_all_pfam_data_zip_file();
        if (!file_exists($full_file))
            return "";
        return $this->shared_get_relative_file_path("_all_pfam_mapping", ".zip");
    }

    public function get_split_pfam_data_zip_file() {
        return $this->shared_get_full_file_path("_split_pfam_mapping", ".zip");
    }
    public function get_relative_split_pfam_data_zip_file() {
        $full_file = $this->get_split_pfam_data_zip_file();
        if (!file_exists($full_file))
            return "";
        return $this->shared_get_relative_file_path("_split_pfam_mapping", ".zip");
    }

    public function get_all_split_pfam_data_zip_file() {
        return $this->shared_get_full_file_path("_all_split_pfam_mapping", ".zip");
    }
    public function get_relative_all_split_pfam_data_zip_file() {
        $full_file = $this->get_all_split_pfam_data_zip_file();
        if (!file_exists($full_file))
            return "";
        return $this->shared_get_relative_file_path("_all_split_pfam_mapping", ".zip");
    }

    public function get_id_table_file($with_domain = false) {
        $with_domain_str = $with_domain ? "_domain" : "";
        return $this->shared_get_full_file_path("_mapping_table$with_domain_str", ".txt");
    }
    public function get_relative_id_table_file($with_domain = false) {
        $with_domain_str = $with_domain ? "_domain" : "";
        return $this->shared_get_relative_file_path("_mapping_table$with_domain_str", ".txt");
    }
    public function get_relative_swissprot_desc_file_path($want_clusters_file) {
        $filename = $this->get_swissprot_desc_filename($want_clusters_file);
        $path = $this->shared_get_web_path($filename);
        if (!$path) {
            $filename = $this->get_swissprot_desc_filename($want_clusters_file, true);
            $path = $this->shared_get_web_path($filename);
        }
        return $path;
    }

    public function get_stats_file() {
        $output_dir = $this->get_output_dir();
        $full_path = $output_dir . "/stats.txt";
        return $full_path;
    }
    
    private function get_swissprot_desc_file($want_clusters_file) {
        $name = $want_clusters_file ? "swissprot_clusters_desc" : "swissprot_singletons_desc";
        return $this->shared_get_full_file_path("_$name", ".txt");
    }
    public function get_relative_swissprot_desc_file($want_clusters_file) {
        $name = $want_clusters_file ? "swissprot_clusters_desc" : "swissprot_singletons_desc";
        return $this->shared_get_relative_file_path("_$name", ".txt");
    }

    public function get_cluster_sizes_file() {
        return $this->shared_get_full_file_path("_cluster_sizes", ".txt");
    }
    public function get_relative_cluster_sizes_file() {
        return $this->shared_get_relative_file_path("_cluster_sizes", ".txt");
    }

    public function get_diagram_zip_file() {
        return $this->shared_get_full_file_path("_arrow_data", ".zip");
    }
    public function get_diagram_data_file_legacy() {
        return $this->shared_get_full_file_path("_arrow_data", ".txt");
    }
    public function get_relative_diagram_data_file() {
        return $this->shared_get_relative_file_path("_arrow_data", ".sqlite");
    }
    public function get_relative_diagram_zip_file() {
        return $this->shared_get_relative_file_path("_arrow_data", ".zip");
    }
    public function does_job_have_arrows() {
        return file_exists($this->get_diagram_data_file());
    }

    public function get_cooc_table_file() {
        return $this->shared_get_full_file_path("_cooc_table", ".txt");
    }
    public function get_relative_cooc_table_file() {
        return $this->shared_get_relative_file_path("_cooc_table", ".txt");
    }

    public function get_hub_count_file() {
        return $this->shared_get_full_file_path("_hub_count", ".txt");
    }
    public function get_relative_hub_count_file() {
        return $this->shared_get_relative_file_path("_hub_count", ".txt");
    }

    public function shared_get_full_file_path($infix_type, $ext) {
        $filename = $this->get_file_prefix() . $infix_type . "_co" . $this->get_cooccurrence() . "_ns" . $this->get_size() . $ext;
        $output_dir = $this->get_output_dir();
        $full_path = $output_dir . "/" . $filename;
        return $full_path;
    }

    public function shared_get_relative_file_path($infix_type, $ext) {
        $filename = $this->get_file_prefix() . $infix_type . "_co" . $this->get_cooccurrence() . "_ns" . $this->get_size() . $ext;
        $output_dir = $this->get_rel_output_dir();
        $full_path = $output_dir . "/" . $this->get_id() . "/".  $filename;
        return $full_path;
    }

    public function get_color_ssn_filesize() {
        $file = $this->get_color_ssn();
        return $this->get_shared_file_size($file);
    }
    public function get_color_ssn_zip_filesize() {
        $file = $this->get_color_ssn_zip();
        return $this->get_shared_file_size($file);
    }
    public function get_gnn_filesize() {
        $file = $this->get_gnn();
        return $this->get_shared_file_size($file);
    }
    public function get_gnn_zip_filesize() {
        $file = $this->get_gnn_zip();
        return $this->get_shared_file_size($file);
    }
    public function get_warning_filesize() {
        $file = $this->get_warning_file();
        return $this->get_shared_file_size($file);
    }
    public function get_cluster_data_zip_filesize($domain_type, $seq_type) {
        $file = $this->get_cluster_data_zip_file($domain_type, $seq_type);
        return $this->get_shared_file_size($file);
    }
    public function get_pfam_none_zip_filesize() {
        $file = $this->get_pfam_none_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_pfam_data_zip_filesize() {
        $file = $this->get_pfam_data_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_all_pfam_data_zip_filesize() {
        $file = $this->get_all_pfam_data_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_split_pfam_data_zip_filesize() {
        $file = $this->get_split_pfam_data_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_all_split_pfam_data_zip_filesize() {
        $file = $this->get_all_split_pfam_data_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_stats_filesize() {
        $file = $this->get_stats_file();
        return $this->get_shared_file_size($file);
    }
    public function get_pfam_hub_filesize() {
        $file = $this->get_pfam_hub();
        return $this->get_shared_file_size($file);
    }
    public function get_pfam_hub_zip_filesize() {
        $file = $this->get_pfam_hub_zipfile();
        return $this->get_shared_file_size($file);
    }
    public function get_id_table_filesize($with_domain = false) {
        $file = $this->get_id_table_file($with_domain);
        return $this->get_shared_file_size($file);
    }
    public function get_cooc_table_filesize() {
        $file = $this->get_cooc_table_file();
        return $this->get_shared_file_size($file);
    }
    public function get_hub_count_filesize() {
        $file = $this->get_hub_count_file();
        return $this->get_shared_file_size($file);
    }
    public function get_diagram_data_filesize() {
        $file = $this->get_diagram_data_file();
        return $this->get_shared_file_size($file);
    }
    public function get_diagram_zip_filesize() {
        $file = $this->get_diagram_zip_file();
        return $this->get_shared_file_size($file);
    }
    public function get_swissprot_desc_filesize($want_clusters_file) {
        $file = $this->get_swissprot_desc_file($want_clusters_file);
        return $this->get_shared_file_size($file);
    }
    public function get_cluster_sizes_filesize() {
        $file = $this->get_cluster_sizes_file();
        return $this->get_shared_file_size($file);
    }

    private function get_shared_file_size($file) {
        if (file_exists($file))
            return round(filesize($file) / 1048576, 5);
        else
            return false;
    }

    public function get_file_size($web_path) {
        if (!$web_path)
            return 0;
        $web_path = str_replace("results/", "", $web_path);
        $full_path = settings::get_output_dir() . "/" . $web_path;
        return $this->get_shared_file_size($full_path);
    }

    // Legacy Jobs
    public function get_no_matches_file() {
        return $this->shared_get_full_file_path("_no_matches", ".xgmml");
    }
    public function get_relative_no_matches_file() {
        return $this->shared_get_relative_file_path("_no_matches", ".xgmml");
    }
    public function get_no_matches_filesize() {
        return round(filesize($this->get_no_matches_file()) / 1048576,2);
    }
    public function get_no_neighbors_file() {
        return $this->shared_get_full_file_path("_no_neighbors", ".xgmml");
    }
    public function get_relative_no_neighbors_file() {
        return $this->shared_get_relative_file_path("_no_neighbors", ".xgmml");
    }
    public function get_no_neighbors_filesize() {
        return round(filesize($this->get_no_neighbors_file()) / 1048576,2);
    }






    public function get_rel_output_dir() {
        if ($this->is_sync)
            return settings::get_rel_sync_output_dir();
        else
            return settings::get_rel_output_dir();
    }

    public function set_time_started() {
        $current_time = date("Y-m-d H:i:s",time());
        $sql = "UPDATE gnn SET gnn_time_started='" . $current_time . "' ";
        $sql .= "WHERE gnn_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->time_started = $current_time;
        } 
    }

    private function set_gnn_stats() {
        $result = $this->count_nodes_edges($this->get_gnn());
        $data = array("gnn_edges" => $result["edges"], "gnn_nodes" => $result["nodes"]);
        $query_result = $this->update_results_object($data);
        if ($query_result) {
            $this->gnn_nodes = $result['nodes'];
            $this->gnn_edges = $result['edges'];
        }
    }

    private function set_ssn_stats() {
        $result = $this->count_nodes_edges($this->get_color_ssn());
        $data = array("ssn_edges" => $result["edges"], "ssn_nodes" => $result["nodes"]);
        $query_result = $this->update_results_object($data);
        if ($query_result) {
            $this->ssn_nodes = $result['nodes'];
            $this->ssn_edges = $result['edges'];
        }
    }

    protected function update_results_object($data) {
        $result = global_functions::update_results_object_tmpl($this->db, "gnn", "gnn", "results", $this->get_id(), $data);
        return $result;
    }

    private function update_params_object($data) {
        $result = global_functions::update_results_object_tmpl($this->db, "gnn", "gnn", "params", $this->get_id(), $data);
        return $result;
    }

    //////////////////Private Functions////////////


    protected function load_gnn($id, $table = "gnn") {
        $sql = "SELECT * FROM $table WHERE gnn_id='" . $id . "' LIMIT 1";
        $result = $this->db->query($sql);
        if ($result) {
            $result = $result[0];
            $this->id = $result["gnn_id"];
            $this->email = $result["gnn_email"];
            $this->key = $result["gnn_key"];
            $this->time_created = $result["gnn_time_created"];
            $this->time_started = $result["gnn_time_started"];
            $this->time_completed = $result["gnn_time_completed"];
            $this->pbs_number = $result["gnn_pbs_number"];
            $this->status = $result["gnn_status"];
            if (isset($result["gnn_est_source_id"]))
                $this->est_id = $result["gnn_est_source_id"];

            $params_obj = global_functions::decode_object($result["gnn_params"]);
            $this->size = $params_obj['neighborhood_size'];
            $this->cooccurrence = $params_obj['cooccurrence'];
            $this->filename = $params_obj['filename'];

            $results_obj = global_functions::decode_object($result["gnn_results"]);
            $this->ssn_nodes = isset($results_obj['ssn_nodes']) ? $results_obj['ssn_nodes'] : "";
            $this->ssn_edges = isset($results_obj['ssn_edges']) ? $results_obj['ssn_edges'] : "";
            $this->gnn_nodes = isset($results_obj['gnn_nodes']) ? $results_obj['gnn_nodes'] : "";
            $this->gnn_edges = isset($results_obj['gnn_edges']) ? $results_obj['gnn_edges'] : "";
            $this->diagram_version = isset($results_obj['diagram_version']) ? $results_obj['diagram_version'] :
                                        (isset($results_obj['gnn_diagram_version']) ? $results_obj['gnn_diagram_version'] : arrow_api::DEFAULT_DIAGRAM_VERSION);

            if (isset($result["gnn_parent_id"]) && isset($result["gnn_child_type"])) {
                $this->gnn_parent_id = $result["gnn_parent_id"];
                $this->child_filter_only = $result["gnn_child_type"] == "filter";
            }

            $db_mod = "";
            if (isset($params_obj["db_mod"])) {
                // Get the actual module not the alias.
                $mod_info = global_settings::get_database_modules();
                foreach ($mod_info as $mod) {
                    if ($mod[1] == $params_obj["db_mod"]) {
                        $db_mod = $mod[0];
                    }
                }
            }
            $this->db_mod = $db_mod;

            $this->extra_ram = isset($params_obj['extra_ram']) && $params_obj['extra_ram'];

            $basefilename = $this->filename;
            if ($this->est_id) {
                $basefilename = pathinfo($basefilename, PATHINFO_BASENAME);
            }

            $fname = strtolower($basefilename);
            $ext_pos = strpos($fname, ".xgmml");
            if ($ext_pos === false)
                $ext_pos = strpos($fname, ".zip");
            if ($ext_pos !== false)
                $this->basefilename = substr($basefilename, 0, $ext_pos);

            $this->set_diagram_data_file($this->shared_get_full_file_path("_arrow_data", ".sqlite"));
            $this->set_gnn_name($this->basefilename);
        }	
    }


    private function delete_outputs() {
        if (file_exists($this->get_color_ssn())) {
            unlink($this->get_color_ssn());
        }
        if (file_exists($this->get_gnn())) {
            unlink($this->get_gnn());
        }
        if (file_exists($this->get_warning_file())) {
            unlink($this->get_warning_file());
        }

    }


    public function count_nodes_edges($xgmml_file) {
        $result = array('nodes' => 0, 'edges' => 0);
        if (file_exists($xgmml_file)) {
            // For now we use grep and wc to count how many edges and nodes there are in the network.
            // Some of the files we are using are very large (e.g. 22GB) and it's impossible to load
            // those into memory.
            $exec = "grep '<node' $xgmml_file | wc -l";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_nodes,) = explode(" ",$output);

            $exec = "grep '<edge' $xgmml_file | wc -l";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_edges,) = explode(" ",$output);

            $result['nodes'] = $num_nodes;
            $result['edges'] = $num_edges;

            //$xml = simplexml_load_file($xgmml_file);
            //foreach ($xml->edge as $edge) {
            //    $result['edges']++;
            //}
            //foreach($xml->node as $node) {
            //    $result['nodes']++;
            //    foreach ($node->att as $att) {
            //        if ($att->attributes()->name == 'pfam') {
            //            $result['pfams']++;
            //        }
            //    }
            //}	
        }
        return $result;

    }

    private function email_error() {
        $subject = $this->beta . "EFI-GNT - GNN computation failed";
        $to = $this->get_email();
        $from = "EFI GNT <" . settings::get_admin_email() . ">";

        $plain_email = "";

        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-GNT." . $this->eol;

        //plain text email
        $plain_email .= "The GNN computation for " . $this->get_id() . " failed. Please contact us ";
        $plain_email .= "to get further assistance." . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $html_email = nl2br($plain_email, false);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    private function email_started() {
        $subject = $this->beta . "EFI-GNT - SSN submission received";
        $to = $this->get_email();
        $from = "EFI GNT <" . settings::get_admin_email() . ">";

        $plain_email = "";

        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-GNT." . $this->eol;

        //plain text email
        $plain_email .= "The SSN that is the input needed to generate a GNN has been received and is being processed." . $this->eol . $this->eol;
        $plain_email .= "You will receive an email once the job has been completed." . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $html_email = nl2br($plain_email, false);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    public function email_complete() {
        $subject = $this->beta . "EFI-GNT - GNN computation completed";
        $to = $this->get_email();
        $from = "EFI GNT <" . settings::get_admin_email() . ">";
        $url = settings::get_web_root() . "/stepc.php";
        $full_url = $url . "?" . http_build_query(array('id'=>$this->get_id(), 'key'=>$this->get_key()));
        $cgfp_url = settings::get_cgfp_web_root();
        $gnt_doi_url = "https://doi.org/10.1016/j.cbpa.2018.09.009";
        $biochem_doi_url = "https://doi.org/10.1021/acs.biochem.9b00735";

        $plain_email = "";

        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-GNT." . $this->eol;

        //plain text email
        $plain_email .= "The GNN computation has completed." . $this->eol . $this->eol;
        $plain_email .= "To view results, go to THE_URL" . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;

        if ($cgfp_url) {
            $plain_email .= "A new tool for Computationally-Guided Functional Profiling (EFI-CGFP) has been added! ";
            $plain_email .= "Go to CGFP_URL to use it." . $this->eol . $this->eol;
        }

        $plain_email .= "Cite us:" . $this->eol . $this->eol;
        $plain_email .= "R&eacute;mi Zallot, Nils Oberg, and John A. Gerlt, ";
        $plain_email .= "The EFI Web Resource for Genomic Enzymology Tools: Leveraging Protein, Genome, and Metagenome Databases to Discover Novel Enzymes and Metabolic Pathways. ";
        $plain_email .= "Biochemistry 2019 58 (41), 4169-4182. BIOCHEM_DOI"; 
        $plain_email .= $this->eol . $this->eol;
        $plain_email .= "R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, ";
        $plain_email .= "\"Democratized\" genomic enzymology web tools for functional assignment, ";
        $plain_email .= "Current Opinion in Chemical Biology, Volume 47, 2018, Pages 77-85, GNT_DOI";
        $plain_email .= $this->eol . $this->eol;

        $plain_email .= "These data will only be retained for " . settings::get_retention_days() . " days." . $this->eol . $this->eol;
        $plain_email .= settings::get_email_footer();

        $html_email = nl2br($plain_email, false);

        $plain_email = str_replace("THE_URL", $full_url, $plain_email);
        $plain_email = str_replace("CGFP_URL", $cgfp_url, $plain_email);
        $plain_email = str_replace("GNT_DOI", $gnt_doi_url, $plain_email);
        $plain_email = str_replace("BIOCHEM_DOI", $biochem_doi_url, $plain_email);
        $html_email = str_replace("THE_URL", "<a href='" . htmlentities($full_url) . "'>" . $full_url . "</a>", $html_email);
        $html_email = str_replace("CGFP_URL", "<a href=\"" . htmlentities($cgfp_url) . "\">" . $cgfp_url . "</a>", $html_email);
        $html_email = str_replace("GNT_DOI", "<a href=\"" . htmlentities($gnt_doi_url) . "\">" . $gnt_doi_url. "</a>", $html_email);
        $html_email = str_replace("BIOCHEM_DOI", "<a href=\"" . htmlentities($biochem_doi_url) . "\">" . $biochem_doi_url. "</a>", $html_email);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    // Code to support asynchronous, cluster queued execution

    public function check_finish_file() {
        return file_exists($this->get_finish_file());
    }

    public function get_finish_file() {
        return $this->get_output_dir() . "/" . $this->finish_file;
    }

    public function get_output_dir($id = 0) {
        if (!$id)
            $id = $this->get_id();
        $base_dir = "";
        if ($this->is_sync)
            $base_dir = settings::get_sync_output_dir();
        else
            $base_dir = settings::get_output_dir();
        return $base_dir . "/" . $id;
    }

    public function set_pbs_number($pbs_number) {
        $sql = "UPDATE gnn SET gnn_pbs_number='" . $pbs_number . "' ";
        $sql .= "WHERE gnn_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->pbs_number = $pbs_number;
    }

    private function update_est_job_file_field($full_ssn_path) {
        $file_name = pathinfo($full_ssn_path, PATHINFO_BASENAME);
        $data = array("filename" => $file_name);
        $result = $this->update_params_object($data);
        $this->filename = $file_name;
    }

    public function mark_job_as_archived() {
        // This marks the job as archived-failed. If the job is archived but the
        // time completed is non-zero, then the job successfully completed.
        if ($this->status == __FAILED__)
            $this->set_time_completed("0000-00-00 00:00:00");
        $this->set_status(__ARCHIVED__);
    }

    public function get_job_info($eol = "\r\n") {
        $filename = pathinfo($this->get_filename(), PATHINFO_BASENAME);
        $message = "EFI-GNT Job ID: " . $this->get_id() . $eol;
        $message .= "Uploaded Filename: " . $filename . $this->eol;
        $message .= "Neighborhood Size: " . $this->get_size() . $this->eol;
        $message .= "% Co-Occurrence Lower Limit (Default: " . settings::get_default_cooccurrence() . "%): " . $this->get_cooccurrence() . "%" . $this->eol;
        $message .= "Time Submitted: " . $this->get_time_created() . $this->eol;
        //$message .= "Time Completed: " . $this->get_time_completed() . $this->eol . $this->eol;
        return $message;
    }

    public function get_metadata() {
        $metadata = array();

        $source_info = $this->get_source_info();

        if ($this->show_summary_details()) {
            array_push($metadata, array("Job Number", $this->id));
            if ($source_info !== false) {
                if ($this->gnn_parent_id) {
                    array_push($metadata, array("Original GNT Job Number", "<a href='stepc.php?id=$this->gnn_parent_id&key=$source_info'>$this->gnn_parent_id</a>"));
                } else {
                    $gid = $source_info["generate_id"];
                    $aid = $source_info["analysis_id"];
                    $key = $source_info["key"];
                    array_push($metadata, array("Original EST Job Number", "$gid/$aid (<a href='../efi-est/stepc.php?id=$gid&key=$key' class='hl-est'>Original EST Dataset</a> | <a href='../efi-est/stepe.php?id=$gid&key=$key&analysis_id=$aid' class='hl-est'>Original SSN Download</a>)"));
                }
            }
            array_push($metadata, array("Time Started -- Finished", global_functions::format_short_date($this->time_started) . " -- " .
                global_functions::format_short_date($this->time_completed)));
        }
        array_push($metadata, array("Uploaded Filename", $this->get_filename()));
        array_push($metadata, array("Neighborhood Size", $this->get_size()));
        array_push($metadata, array("Input % Co-Occurrence", $this->get_cooccurrence()));

        $stats_file = $this->get_stats_file();

        if (!file_exists($stats_file))
            return $metadata;

        $fh = fopen($stats_file, "r");

        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            $parts = explode("\t", $line);
            if (!$line || count($parts) < 2)
                continue;
            $val = is_numeric($parts[1]) ? number_format($parts[1]) : $parts[1];
            array_push($metadata, array($parts[0], $val));
        }

        fclose($fh);

        return $metadata;
    }

    public function get_child_jobs() {
        $jobs = array();
        $sql = "SELECT gnn_id, gnn_key, gnn_params FROM gnn WHERE gnn_parent_id = " . $this->get_id();
        $results = $this->db->query($sql);
        foreach ($results as $result) {
            $params = global_functions::decode_object($result["gnn_params"]);
            $jobs[$result["gnn_id"]] = array(
                "key" => $result["gnn_key"],
                "filename" => $params["filename"],
                "size" => $params["neighborhood_size"],
                "cooccurrence" => $params["cooccurrence"],
            );
        }
        return $jobs;
    }
}
?>
