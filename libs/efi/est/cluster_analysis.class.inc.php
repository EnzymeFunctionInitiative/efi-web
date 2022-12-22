<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\file_types;


class cluster_analysis extends colorssn_shared {

    private $make_hmm;

    public function __construct($db, $id = 0, $is_example = false) {
        parent::__construct($db, $id, $is_example);
    }

    public function __destruct() {
    }

    public function get_hmm_options() {
        $opt = $this->make_hmm;
        $opt = str_replace("CR", "Consensus Residue", $opt);
        $opt = str_replace("HMM", "HMM", $opt);
        $opt = str_replace("WEBLOGO", "Weblogo", $opt);
        $opt = str_replace("HIST", "Length Histogram", $opt);
        $parts = explode(",", $opt);
        $opt = implode(", ", $parts);
        $opt = preg_replace("/^\s*,\s*/", "", $opt);
        $count_seq_opt = $this->min_seq_msa != self::DEFAULT_MIN_SEQ_MSA ? "MinNumSeq=" . $this->min_seq_msa : "";
        $count_seq_opt .= $this->max_seq_msa ? ($count_seq_opt ? "; " : "") . "MaxNumSeq=" . $this->max_seq_msa : "";
        if (preg_match("/CR/", $this->make_hmm))
            $opt .= " (AAs=" . $this->hmm_aa . "; Thresholds=" . $this->aa_threshold . "; " . $count_seq_opt . ")";
        elseif ($count_seq_opt)
            $opt .= " ($count_seq_opt)";
        return $opt;
    }

    public function is_hmm_and_stuff_job() { return true; }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS

    public function get_create_type() {
        return self::create_type();
    }

    public static function create_type() {
        return "CLUSTER";
    }

    protected function get_run_script_args($out) {
        $parms = parent::get_run_script_args($out);
        
        if ($this->make_hmm) {
            $parms["--opt-msa-option"] = $this->make_hmm;
            if (preg_match("/CR/", $this->make_hmm)) {
                if ($this->hmm_aa)
                    $parms["--opt-aa-list"] = $this->hmm_aa;
                if ($this->aa_threshold)
                    $parms["--opt-aa-threshold"] = $this->aa_threshold;
            }
            if (preg_match("/CR|HMM|WEBLOGO/", $this->make_hmm)) {
                if ($this->min_seq_msa)
                    $parms["--opt-min-seq-msa"] = $this->min_seq_msa;
                if ($this->max_seq_msa)
                    $parms["--opt-max-seq-msa"] = $this->max_seq_msa;
            }
        }

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        $this->make_hmm = (isset($result["make_hmm"]) && $result["make_hmm"]) ? $result["make_hmm"] : "";
        $this->aa_threshold = (isset($result["aa_threshold"]) && $result["aa_threshold"]) ? $result["aa_threshold"] : 0;
        $this->min_seq_msa = (isset($result["min_seq_msa"]) && $result["min_seq_msa"]) ? $result["min_seq_msa"] : self::DEFAULT_MIN_SEQ_MSA;
        $this->max_seq_msa = (isset($result["max_seq_msa"]) && $result["max_seq_msa"]) ? $result["max_seq_msa"] : 0;
        $this->hmm_aa = (isset($result["hmm_aa"]) && $result["hmm_aa"]) ? $result["hmm_aa"] : "";

        return $result;
    }

    public function get_insert_array($data) {
        if ($data->hmm_aa)
            $data->hmm_aa = strtoupper($data->hmm_aa);
        $insert_array = parent::get_insert_array($data);
        $insert_array["make_hmm"] = (isset($data->make_hmm) && preg_match("/^[CRHMWEBLOGIST,]+$/", $data->make_hmm)) ? $data->make_hmm : "";
        $insert_array["aa_threshold"] = (isset($data->aa_threshold) && preg_match("/^[0-9\., ]+$/", $data->aa_threshold)) ? $data->aa_threshold : 0;
        $insert_array["hmm_aa"] = (isset($data->hmm_aa) && preg_match("/^[A-Z, ]+$/", $data->hmm_aa)) ? str_replace(" ", "", $data->hmm_aa) : "";
        $insert_array["min_seq_msa"] = (isset($data->min_seq_msa) && preg_match("/^[0-9\., ]+$/", $data->min_seq_msa)) ? $data->min_seq_msa : self::DEFAULT_MIN_SEQ_MSA;
        $insert_array["max_seq_msa"] = (isset($data->max_seq_msa) && preg_match("/^[0-9\., ]+$/", $data->max_seq_msa)) ? $data->max_seq_msa : 700;
        return $insert_array;
    }

    protected function get_email_completion_subject() { return "EFI-EST - SSN colored and analyzed"; }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    public function get_hmm_graphics() {
        return $this->get_logo_graphics_data("hmm_logos");
    }
    public function get_weblogo_graphics() {
        return $this->get_logo_graphics_data("weblogos");
    }
    public function get_lenhist_graphics() {
        return $this->get_logo_graphics_data("histograms");
    }
    public function get_alignment_list() {
        $data = $this->get_logo_graphics_data("alignments");
        if (!empty($data))
            return $data;

        $logo_data = $this->get_weblogo_graphics();
        foreach ($logo_data as $cluster_num => $seq_type_list) {
            foreach ($seq_type_list as $seq_type => $quality_list) {
                foreach ($quality_list as $quality => $ds) {
                    $parts = explode("/", $ds["path"]);
                    $max = count($parts) - 1;
                    if (isset($parts[$max - 1])) {
                        $parts[$max - 1] = "align";
                        $parts[$max] = $parts[$max];
                    }
                    $path = implode("/", $parts);
                    $data[$cluster_num][$seq_type][$quality]["path"] = $path;
                }
            }
        }

        return $data;
    }
    public function get_cr_results_list() {
        $data = $this->get_logo_graphics_data("consensus_residue");
        return $data;
    }



    public function get_cons_res_file_name($type, $aa) {
        $ext = file_types::ext($type);
        if ($ext === false)
            return false;
        $suffix = file_types::suffix($type);
        if ($suffix === false)
            return false;

        $suffix .= "_$aa";
        if ($type == file_types::FT_cons_res_pct)
            $suffix .= "_Percentage_Summary";
        else if ($type == file_types::FT_cons_res_pos)
            $suffix .= "_Position_Summary";
        $suffix .= "_Full";

        $name = $this->get_base_filename();
        $name .= "_$suffix.$ext";
        return $name; 
    }
    public function get_cons_res_file_path($type, $aa) {
        $ext = file_types::ext($type);
        if ($ext === false)
            return false;
        $name = file_types::suffix($type);
        if ($name === false)
            return false;
        $base_dir = $this->get_full_output_dir();

        // Try the simple naming convention first
        $file_path = "$base_dir/$name.$ext";

        // Then try legacy file naming convention if the new convention doesn't exist
        if (!file_exists($file_path)) {
            $file_name = $this->get_cons_res_file_name($type, $aa);
            $file_path = "$base_dir/${file_name}";
        }

        if (!file_exists($file_path))
            return false;
        return $file_path;
    }




    private function get_logo_graphics_data($graphics_type) {
        $base_dir = $this->get_full_output_dir();
        $full_path = "$base_dir/$graphics_type.txt";
        if (!file_exists($full_path))
            return array();

        $graphics = array();

        $sizes = $this->parse_cluster_sizes();
        $num_map = $this->get_cluster_num_map();

        $lines = file($full_path);
        foreach ($lines as $line) {
            $parts = explode("\t", rtrim($line));
            if (count($parts) < 4)
                continue;
            list($cluster_num, $seq_type, $sub_type, $path) = $parts;
            
            $data = array("path" => $path);
            if (isset($sizes[$cluster_num])) {
                $data["num_seq"] = 0;
                $data["num_uniprot"] = $sizes[$cluster_num]["uniprot"];
                $data["num_uniref90"] = $sizes[$cluster_num]["uniref90"];
                $data["num_uniref50"] = $sizes[$cluster_num]["uniref50"];
            }

            if ($graphics_type == "hmm_logos") {
                $hmm_path = "$base_dir/$path.hmm";
                $hmm_data = self::parse_hmm($hmm_path);
                $data = array_merge($data, $hmm_data);
            }
            $graphics[$cluster_num][$seq_type][$sub_type] = $data;
        }

        if (count($num_map) > 0) {
            uksort($graphics, function ($a, $b) use ($num_map) { return ($num_map[$a] < $num_map[$b] ? -1 : ($num_map[$a] > $num_map[$b] ? 1 : 0)); });
        }

        return $graphics;
    }
    public function get_graphics_dir() {
        return $this->get_web_output_dir();
    }


    private function parse_cluster_sizes() {
        $filename = $this->get_file_name(file_types::FT_sizes);
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        $lines = file($full_path);
        $nums = array();
        foreach ($lines as $line) {
            $data = explode("\t", rtrim($line));
            $cluster_num = $data[0];
            $uniprot_size = $data[1];
            $uniref90_size = count($data) > 2 ? $data[2] : 0;
            $uniref50_size = count($data) > 3 ? $data[3] : 0;
            $nums[$cluster_num] = array("uniprot" => $uniprot_size, "uniref90" => $uniref90_size, "uniref50" => $uniref50_size);
        }
        return $nums;
    }
    public function get_cluster_num_map() {
        $filename = $this->get_cluster_num_filename();
        $full_path = $this->get_full_output_dir() . "/" . $filename;
        if (!file_exists($full_path))
            return array();
        $lines = file($full_path);
        $num_map = array();
        foreach ($lines as $line) {
            $data = explode("\t", rtrim($line));
            $seq_num = $data[0];
            $node_num = $data[1];
            //$uniprot_size = $data[2];
            //$uniref_size = isset($data[3]) ? $data[3] : 0;
            $num_map[$seq_num] = $node_num;//array("uniprot" => $uniprot_size, "uniref90" => $uniref90_size, "uniref50" => $uniref50_size);
        }
        return $num_map;
    }
    private function get_cluster_num_filename() {
        return "cluster_num_map.txt";
    }
    // Read HMM length and number of sequences used to generate HMM.
    private static function parse_hmm($path) {
        $fh = fopen($path, "r");
        if (!$fh)
            return array();

        $stats = array();
        while (!feof($fh)) {
            $line = fgets($fh);
            $parts = preg_split('/\s+/', $line);
            if (isset($parts[0]) && $parts[0] === "HMM")
                break;
            if (count($parts) >= 2) {
                if ($parts[0] === "LENG")
                    $stats["length"] = $parts[1];
                elseif ($parts[0] === "NSEQ")
                    $stats["num_seq"] = $parts[1];
            }
        }

        return $stats;
    }

    public function get_metadata() {
        $extra = $this->get_hmm_options();
        $meta = array(array("Analysis Options", $extra));
        $metadata = parent::get_metadata_parent("Cluster Analysis", $meta);
        return $metadata;
    }
}

