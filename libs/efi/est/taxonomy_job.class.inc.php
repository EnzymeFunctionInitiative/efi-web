<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\est\file_helper;


class taxonomy_job extends family_shared {

    public $subject = "EFI-EST Pfam/InterPro Taxonomy";

    private $file_helper;

    //private $min_seq_len;
    //private $max_seq_len;
    
    public function __construct($db, $id = 0, $is_example = false) {
        $this->file_helper = new file_helper(".txt", $id);
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    public function get_uploaded_filename() { return $this->file_helper->get_uploaded_filename(); }
    public function get_no_matches_download_path() {
        return functions::get_web_root() . "/" .
            $this->get_no_matches_download_file();
    }
    private function get_no_matches_download_file() {
        $dir = $this->is_example ? functions::get_results_example_dirname($this->is_example) : functions::get_results_dirname();
        $dir .= "/" . $this->get_output_dir();
        $dir .= "/" . $this->get_no_matches_filename(); 
        return $dir;
    }
    private function get_no_matches_job_file() {
        return
            "output/" .
            $this->get_no_matches_filename(); 
    }
    private function get_no_matches_filename() {
        return
            $this->get_id() . "_" .
            functions::get_no_matches_filename(); 
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    protected function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        // Hack to allow us to treat the jobs like family jobs
        //return "TAXONOMY";
        return "FAMILIES";
    }

    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        //if ($data->min_seq_len)
        //    $insert_array["min_seq_len"] = $data->min_seq_len;
        //if ($data->max_seq_len)
        //    $insert_array["max_seq_len"] = $data->max_seq_len;
        return $insert_array;
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        $parms["--tax-search-only"] = "";
        //if ($this->min_seq_len)
        //    $parms["--min-seq-len"] = $this->min_seq_len;
        //if ($this->max_seq_len)
        //    $parms["--max-seq-len"] = $this->max_seq_len;
        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if (isset($result['generate_fasta_file']) && $result['generate_fasta_file']) {
            $this->file_helper->on_load_generate($this->id, $result);
        }

        //if (isset($result["min_seq_len"]))
        //    $this->min_seq_len = $result["min_seq_len"];
        //if (isset($result["max_seq_len"]))
        //    $this->max_seq_len = $result["max_seq_len"];

        return $result;
    }

    protected function validate($data) {
        $result = parent::validate($data);

        if ($data->families == "") {
            $result->errors = true;
            $result->message .= "Please enter valid InterPro and Pfam numbers";
        }

        return $result;
    }

    protected function get_email_job_info() {
        $message = parent::get_email_job_info();
        $message .= "Pfam/InterPro Families: " . $this->get_families_comma() . PHP_EOL;
        if ($this->uniref_version)
            $message .= "Using UniRef" . $this->uniref_version . PHP_EOL;
        
        return $message;
    }

    public function get_length_histogram_info() {
        $uniprot_info = $this->get_graph_info("histogram_uniprot");
        $uniref90_info = $this->get_graph_info("histogram_uniref90");
        $uniref50_info = $this->get_graph_info("histogram_uniref50");

        $info = array();

        if ($uniprot_info !== false)
            array_push($info, array("UniProt", "histogram_uniprot"));
        if ($uniref50_info !== false)
            array_push($info, array("UniRef50", "histogram_uniref50"));
        if ($uniref90_info !== false)
            array_push($info, array("UniRef90", "histogram_uniref90"));

        return $info;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
}

