<?php

require_once('arrow_api.class.inc.php');
require_once(__DIR__.'/../../libs/global_functions.class.inc.php');

class gnn_shared extends arrow_api {

    public static function create2($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'tmp_filename' => $tmp_filename,
            'filename' => $filename,
            'est_id' => 0,
            'job_name' => $job_name,
            'is_sync' => false,
            'db_mod' => "",
        );
        return self::create_shared($db, $parms);
    }

    public static function create3($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name, $is_sync) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'tmp_filename' => $tmp_filename,
            'filename' => $filename,
            'est_id' => 0,
            'job_name' => $job_name,
            'is_sync' => $is_sync,
            'db_mod' => "",
        );
        return self::create_shared($db, $parms);
    }

    public static function create_from_est_job($db, $email, $size, $cooccurrence, $ssn_file_path, $est_id) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'tmp_filename' => "",
            'filename' => $ssn_file_path,
            'est_id' => $est_id,
            'job_name' => "",
            'is_sync' => false,
            'db_mod' => "",
        );
        return self::create_shared($db, $parms);
    }

    public static function create4($db, $parms) {
        if (!isset($parms['email']))
            return false;
        if (!isset($parms['size']))
            return false;
        if (!isset($parms['cooccurrence']))
            return false;
        if (!isset($parms['filename']))
            return false;

        if (!isset($parms['tmp_filename']))
            $parms['tmp_filename'] = "";
        if (!isset($parms['job_name']))
            $parms['job_name'] = "";
        if (!isset($parms['est_id']))
            $parms['est_id'] = 0;
        if (!isset($parms['is_sync']))
            $parms['is_sync'] = false;
        if (!isset($parms['db_mod']))
            $parms['db_mod'] = "";

        return self::create_shared($db, $parms);
    }

    // For jobs originating from EST, we save the full path to the SSN into the filename field.
    // When the job processing workflow starts, we set the filename field in the db to be just the filename.
    private static function create_shared($db, $parms) {
        //$email, $size, $cooccurrence, $tmp_filename, $filename, $est_job_id, $job_name, $is_sync) {

        // Sanitize the filename
        $filename = $parms['filename'];
        $est_job_id = $parms['est_id'];
        if (!$est_job_id)
            $filename = preg_replace("([\._]{2,})", '', preg_replace("([^a-zA-Z0-9\-_\.])", '', $filename));

        $job_name = preg_replace('/[^A-Za-z0-9 \-_?!#\$%&*()\[\],\.<>:;{}]/', "_", $parms['job_name']);
        $gnn_status = $parms['is_sync'] ? __FINISH__ : __NEW__;

        $result = false;
        $key = global_functions::generate_key();
        $insert_array = array(
            'gnn_email' => $parms['email'],
            'gnn_size' => $parms['size'],
            'gnn_key' => $key,
            'gnn_filename' => $filename,
            'gnn_cooccurrence' => $parms['cooccurrence'],
            'gnn_status' => $gnn_status);
        if ($est_job_id)
            $insert_array['gnn_source_id'] = $est_job_id;
        if ($parms['job_name'])
            $insert_array['gnn_job_name'] = $parms['job_name'];
        if ($parms['db_mod'] && preg_match('/^[A-Z0-9]{4}$/', $parms['db_mod']))
            $insert_array['gnn_db_mod'] = $parms['db_mod'];

        $result = $db->build_insert('gnn',$insert_array);
        if (!$est_job_id) {
            if ($result) {	
                functions::copy_to_uploads_dir($parms['tmp_filename'], $filename, $result);
            } else {
                return false;
            }
        }
        $info = array('id' => $result, 'key' => $key);

        return $info;
    }

    public static function create($db, $email, $size, $tmp_filename, $filename, $cooccurrence) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'coocurrence' => $cooccurrence,
            'tmp_filename' => $tmp_filename,
            'filename' => $filename,
            'est_id' => 0,
            'job_name' => "",
            'is_sync' => false,
            'db_mod' => "",
        );
        $info = self::create_shared($db, $parms);
        if ($info === false)
            return 0;
        else
            return $info['id'];
    }
}

?>
