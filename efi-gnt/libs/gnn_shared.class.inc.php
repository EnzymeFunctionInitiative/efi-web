<?php

require_once('arrow_api.class.inc.php');
require_once(__DIR__.'/../../libs/global_functions.class.inc.php');

class gnn_shared extends arrow_api {

    public static function create2($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name) {
        return self::create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, 0, $job_name, false);
    }

    public static function create3($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name, $is_sync) {
        return self::create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, 0, $job_name, $is_sync);
    }

    public static function create_from_est_job($db, $email, $size, $cooccurrence, $ssn_file_path, $est_id) {
        return self::create_shared($db, $email, $size, $cooccurrence, "", $ssn_file_path, $est_id, "", false);
    }

    // For jobs originating from EST, we save the full path to the SSN into the filename field.
    // When the job processing workflow starts, we set the filename field in the db to be just the filename.
    private static function create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $est_job_id, $job_name, $is_sync) {

        // Sanitize the filename
        if (!$est_job_id)
            $filename = preg_replace("([\._]{2,})", '', preg_replace("([^a-zA-Z0-9\-_\.])", '', $filename));

        $job_name = preg_replace('/[^A-Za-z0-9 \-_?!#\$%&*()\[\],\.<>:;{}]/', "_", $job_name);
        $gnn_status = $is_sync ? __FINISH__ : __NEW__;

        $result = false;
        $key = global_functions::generate_key();
        $insert_array = array(
            'gnn_email' => $email,
            'gnn_size' => $size,
            'gnn_key' => $key,
            'gnn_filename' => $filename,
            'gnn_cooccurrence' => $cooccurrence,
            'gnn_status' => $gnn_status);
        if ($est_job_id)
            $insert_array["gnn_source_id"] = $est_job_id;

        $result = $db->build_insert('gnn',$insert_array);
        if (!$est_job_id) {
            if ($result) {	
                functions::copy_to_uploads_dir($tmp_filename, $filename, $result);
            } else {
                return false;
            }
        }
        $info = array('id' => $result, 'key' => $key);

        return $info;
    }

    public static function create($db, $email, $size, $tmp_filename, $filename, $cooccurrence) {
        $info = create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, 0, "", false);
        if ($info === false)
            return 0;
        else
            return $info['id'];
    }
}

?>
