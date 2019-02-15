<?php

require_once('functions.class.inc.php');

class generate_helper {

    public static function get_run_script_args($out, $parms, $obj) {
        if ($obj->get_num_cpu())
            $parms["-np"] = $obj->get_num_cpu();
        else
            $parms["-np"] = functions::get_cluster_procs();
        $parms["-evalue"] = $obj->get_evalue();
        $parms["-tmp"] = $out->relative_output_dir;
        $parms["-maxsequence"] = functions::get_max_seq();
        $parms["-queue"] = functions::get_generate_queue();
        $parms["-memqueue"] = functions::get_memory_queue();
        return $parms;
    }
    
}

?>
