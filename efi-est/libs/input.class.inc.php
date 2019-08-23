<?php

class input_data {

    public $email;
    public $job_group;
    public $job_name;
    public $evalue;
    public $fraction;
    public $max_seqs;
    public $random_fraction;
    public $db_mod;
    public $cpu_x2;

    // For option A, C, D
    public $field_input;

    // For option A
    public $blast_evalue; // For Option A, this is the sequence blast evalue.  the $evalue var above is for the optional families added in by the user.
    public $blast_input_id; // For Option A, if the user provides a header, we store it.

    // For option D
    public $domain_family;
    public $domain_region;

    // For option B, and E
    public $domain;
    public $program;
    public $length_overlap;
    public $uniref_version;  # If this is set to valid value (50 or 90) UniRef is used to generate the dataset.
    public $no_demux;  # In the case of Option E (Option B+) setting this forces a demux step to be executed.

    // For option B+C
    public $families;

    // For option C+D+E
    public $tmp_file;
    public $uploaded_filename;

    public $minimum;
    public $maximum;

    // For Option E
    public $min_seq_len;
    public $max_seq_len;

    // This flag is set to true if the script is called from the command line.
    public $is_debug;

    // For Color SSN option
    public $color_ssn_source_id;  // analysis ID
    public $color_ssn_source_idx; // SSN index
    public $extra_ram; // use extra RAM (dev site)
    public $make_hmm; // make HMMs
    public $fast_hmm; // make HMMs and fast MUSCLE HMMs

    //public $cooccurrence;
    //public $neighborhood_size;
}

class validation_result {
    public $message;
    public $errors;

    public function __construct() {
        $this->message = "";
        $this->errors = false;
    }
}

?>

