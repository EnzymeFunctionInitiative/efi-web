<?php

class input_data {

    public $email;
    public $job_group;
    public $job_name;
    public $evalue;
    public $fraction;
    public $max_seqs;
    public $random_fraction;

    // For option A, C, D
    public $field_input;

    // For option A
    public $blast_evalue; // For Option A, this is the sequence blast evalue.  the $evalue var above is for the optional families added in by the user.

    // For option D
    public $expand_homologs;

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

    // This flag is set to true if the script is called from the command line.
    public $is_debug;

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

