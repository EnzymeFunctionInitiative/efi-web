<?php
namespace efi\est;

require_once(__DIR__."/../../../init.php");

use \efi\est\functions;


class generate_helper {

    public static function get_run_script_args($out, $parms, $obj) {
        if ($obj->get_num_cpu())
            $parms["-np"] = $obj->get_num_cpu();
        else
            $parms["-np"] = functions::get_cluster_procs();
        if ($obj->is_large_mem())
            $parms["--large-mem"] = "";
        $parms["-evalue"] = $obj->get_evalue();
        $parms["-tmp"] = $out->relative_output_dir;
        $parms["-maxsequence"] = est_settings::get_max_seq();
        $parms["-queue"] = functions::get_generate_queue();
        $parms["-memqueue"] = functions::get_memory_queue();
        return $parms;
    }
    
}

