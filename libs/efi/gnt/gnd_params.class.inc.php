<?php
namespace efi\gnt;


class gnd_params {
    public $is_example = false;
    public $is_uploaded_diagram = false;
    public $is_direct_job = false;
    public $is_blast = false;
    public $is_superfamily_job = false;
    public $is_interpro_enabled = false;
    public $is_bigscape_enabled = false;
    public $is_realtime_job = false;

    public $supports_download = false;
    public $supports_export = false;

    public $has_unmatched_ids = false;
    public $unmatched_id_modal_text;
    public $uniprot_id_modal_text;
    public $job_type_text;

    public $id_key_query_string;
    public $gnn_key;
    public $gnn_id;

    public $blast_seq;

    public $bigscape_btn_icon;
    public $bigscape_status;
    public $bigscape_btn_text;
    public $bigscape_modal_close_text;

    public $max_nb_size = 20;
    public $nb_size;
    public $cooccurrence;
    public $gnn_name;
}

