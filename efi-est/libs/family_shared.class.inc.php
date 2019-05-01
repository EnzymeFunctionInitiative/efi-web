<?php

require_once("option_base.class.inc.php");
require_once("generate_helper.class.inc.php");
require_once("family_size.class.inc.php");

abstract class family_shared extends option_base {


    //////////////////Private Variables//////////

    protected $families = array();
    protected $length_overlap = 1.0;
    protected $seq_id = "1.0";
    protected $uniref_version = "";
    protected $no_demux = 0;
    protected $random_fraction = false;
    protected $min_seq_len = 0;
    protected $max_seq_len = 0;

    ///////////////Public Functions///////////

    public function __construct($db,$id = 0) {
        parent::__construct($db, $id);
    }

    public function __destruct() {
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FUNCTIONS SPECIFIC TO FAMILIES
    
    public function get_families() { return $this->families; }
    public function get_families_comma() { return implode(",", $this->get_families()); }
    public function get_sequence_identity() { return $this->seq_id; }
    public function get_length_overlap() { return $this->length_overlap; }
    public function get_uniref_version() { return $this->uniref_version; }
    public function get_no_demux() { return $this->no_demux; }
    public function is_cd_hit_job() { return strpos($this->seq_id, ",") !== FALSE; } //HACK: this is a temporary hack for research purposes
            


    //returns an array of the pfam families or empty array otherwise
    public function get_pfam_families() {
        $pfam_families = array();
        foreach ($this->families as $family) {
            if (substr($family,0,2) == "PF" || substr($family,0,2) == "CL") { // Also allow Pfam clans
                array_push($pfam_families,$family);
            }
        }
        return $pfam_families;

    }

    //returns an array of the interpro families or empty array otherwise
    public function get_interpro_families() {
        $interpro_families = array();
        foreach ($this->families as $family) {
            if (substr($family,0,3) == "IPR") {
                array_push($interpro_families,$family);
            }
        }
        return $interpro_families;

    }

    public function get_cdhit_stats() {
        $results_dir = functions::get_results_dir();
        $file = $results_dir . "/" . $this->get_output_dir();
        $file .= "/" . functions::get_cdhit_stats_filename();
        $file_handle = @fopen($file,"r") or die("Error opening " . $this->stats_file . "\n");
        $i = 0; 
        $stats_array = array();
        $keys = array('SequenceId','SequenceLength','Nodes');
        while (($data = fgetcsv($file_handle,0,"\t")) !== FALSE) {
            $data[0] = number_format(floatval($data[0]) * 100, 0) . "%";
            $data[1] = number_format(floatval($data[1]) * 100, 0) . "%";
            array_push($stats_array,array_combine($keys,$data));
        }
        fclose($file_handle);
        return $stats_array;
    }


    // END FUNCTIONS SPECIFIC TO FAMILIES
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // OVERLOADS
    
    protected function get_insert_array($data) {
        $insert_array = parent::get_insert_array($data);
        $families = family_size::parse_family_query($data->families);
        $formatted_families = implode(",", $families);
        //$formatted_families = $this->format_families($data->families);

        $insert_array['generate_families'] = $formatted_families;
        if (isset($data->seq_id))
            $insert_array['generate_sequence_identity'] = $data->seq_id;
        if (isset($data->length_overlap))
            $insert_array['generate_length_overlap'] = $data->length_overlap;
        if (isset($data->uniref_version) && $data->uniref_version && ($data->uniref_version == "50" || $data->uniref_version == "90"))
            $insert_array['generate_uniref'] = $data->uniref_version;
        if (isset($data->no_demux))
            $insert_array['generate_no_demux'] = $data->no_demux;
        if (isset($data->random_fraction))
            $insert_array['generate_random_fraction'] = $data->random_fraction;
        if (isset($data->min_seq_len))
            $insert_array['generate_min_seq_len'] = $data->min_seq_len;
        if (isset($data->max_seq_len))
            $insert_array['generate_max_seq_len'] = $data->max_seq_len;
        return $insert_array;
    }

    protected function validate($data) {
        $result = parent::validate($data);

        if (($data->families != "") && (!$this->verify_families($data->families))) {
            $result->errors = true;
            $result->message .= "<br><b>Please enter valid InterPro and Pfam numbers</b></br>";
        }

        return $result;
    }

    protected function get_run_script() {
        return "generatedata.pl";
    }

    protected function get_run_script_args($outDir) {

        $pfam_families = implode(",",$this->get_pfam_families());
        $interpro_families = implode(",",$this->get_interpro_families());

        $parms = array();
        $parms = generate_helper::get_run_script_args($outDir, $parms, $this);
        #$parms["-blast"] = strtolower($this->get_program());
        if (strlen($interpro_families))
            $parms["-ipro"] = $interpro_families;
        if (strlen($pfam_families))
            $parms["-pfam"] = $pfam_families;
        if ($this->seq_id) {
            $parms["-sim"] = $this->seq_id;
            if (strpos($this->seq_id, ",") !== FALSE)
                $parms["-cd-hit"] = functions::get_cdhit_stats_filename();
        }
        if ($this->length_overlap)
            $parms["-lengthdif"] = $this->length_overlap;
        if ($this->uniref_version)
            $parms["-uniref-version"] = $this->uniref_version;
        if (($this->length_overlap || $this->seq_id) && $this->no_demux)
            $parms["-no-demux"] = "";
        
        $fraction = $this->get_fraction();
        if ($fraction) {
            $parms["-fraction"] = $this->get_fraction();
            if ($fraction > 1 && $this->random_fraction)
                $parms["-random-fraction"] = "";
        }

        if ($this->min_seq_len)
            $parms["-min-seq-len"] = $this->min_seq_len;
        if ($this->max_seq_len)
            $parms["-max-seq-len"] = $this->max_seq_len;

        $parms["-seq-count-file"] = $this->get_accession_counts_file_full_path();
        $parms["-conv-ratio-file"] = functions::get_convergence_ratio_filename();

        return $parms;
    }

    protected function load_generate($id) {
        $result = parent::load_generate($id);
        if (! $result) {
            return;
        }

        if ($result['generate_families'] != "") {
            $families = explode(",", $result['generate_families']);
            $this->families = $families;
        }

        if (array_key_exists('generate_sequence_identity', $result) && $result['generate_sequence_identity'])
            $this->seq_id = $result['generate_sequence_identity'];
        if (array_key_exists('generate_length_overlap', $result) && $result['generate_length_overlap'])
            $this->length_overlap = $result['generate_length_overlap'];
        if (array_key_exists('generate_uniref', $result) && $result['generate_uniref'] != "--")
            $this->uniref_version = $result['generate_uniref'];
        else
            $this->uniref_version = "";
        if (array_key_exists('generate_no_demux', $result) && $result['generate_no_demux'])
            $this->no_demux = 1;
        else
            $this->no_demux = 0;
        if (array_key_exists('generate_random_fraction', $result) && $result['generate_random_fraction'])
            $this->random_fraction = 1;
        else
            $this->random_fraction = 0;
        if (array_key_exists('generate_min_seq_len', $result))
            $this->min_seq_len = $result['generate_min_seq_len'];
        if (array_key_exists('generate_max_seq_len', $result))
            $this->max_seq_len = $result['generate_max_seq_len'];

        return $result;
    }

    // END OVERLOADS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    private function verify_families($families) {
        $family_array = family_size::parse_family_query($families);

        $valid = 0;
        foreach ($family_array as $family) {
            $family = trim(rtrim($family));
            $family = strtolower($family);
            //Test if InterPro Number
            if ((substr($family,0,3) == "ipr") && (is_numeric(substr($family,3))) && (strlen(substr($family,3)) == 6)) {
                $valid = 1;

            }
            //Test if Pfam Number
            elseif ((substr($family,0,2) == "pf") && (is_numeric(substr($family,2))) && (strlen(substr($family,2)) == 5)) {
                $valid = 1;
            }
            //Test if Clan Number
            elseif ((substr($family,0,2) == "cl") && (is_numeric(substr($family,2))) && (strlen(substr($family,2)) == 4)) {
                $valid = 1;
            }
            else {
                $valid = 0;
                break;
            }
        }
        return $valid;

    }

    //private function format_families($families) {
    //    $search = array(" ");
    //    $replace = "";
    //    $formatted_families = str_ireplace($search,$replace,$families);
    //    $formatted_families = strtoupper($formatted_families);
    //    return $formatted_families;

    //}

}

?>
