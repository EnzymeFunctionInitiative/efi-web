<?php

require_once('arrow_api.class.inc.php');
require_once(__DIR__.'/../../libs/global_functions.class.inc.php');

class gnn_shared extends arrow_api {

    public function __construct($db, $id, $db_field_prefix) {
        parent::__construct($db, $id, $db_field_prefix);
    }

    public static function create2($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'tmp_filename' => $tmp_filename,
            'filename' => $filename,
            'job_name' => $job_name,
        );
        return self::create4($db, $parms);
    }

    public static function create3($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $job_name, $is_sync) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'tmp_filename' => $tmp_filename,
            'filename' => $filename,
            'job_name' => $job_name,
            'is_sync' => $is_sync,
        );
        return self::create4($db, $parms);
    }

    public static function create_from_est_job($db, $email, $size, $cooccurrence, $ssn_file_path, $est_id, $db_mod) {
        $parms = array(
            'email' => $email,
            'size' => $size,
            'cooccurrence' => $cooccurrence,
            'filename' => $ssn_file_path,
            'est_id' => $est_id,
            'db_mod' => $db_mod,
        );
        return self::create4($db, $parms); // create4 adds default parameters
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
        if (!isset($parms['parent_id']))
            $parms['parent_id'] = 0;
        if (!isset($parms['child_type']))
            $parms['child_type'] = "";

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
            'gnn_key' => $key,
            'gnn_status' => $gnn_status);
        if ($est_job_id)
            $insert_array['gnn_est_source_id'] = $est_job_id;
        if ($parms['parent_id'] && $parms['child_type']) {
            $insert_array['gnn_parent_id'] = $parms['parent_id'];
            $insert_array['gnn_child_type'] = $parms['child_type'];
        }

        $params_array = array(
            'neighborhood_size' => $parms['size'],
            'filename' => $filename,
            'cooccurrence' => $parms['cooccurrence'],
        );
        if ($parms['job_name'])
            $params_array['job_name'] = $parms['job_name'];
        if ($parms['db_mod'] && preg_match('/^[A-Z0-9]{4}$/', $parms['db_mod']))
            $params_array['db_mod'] = $parms['db_mod'];

        $insert_array['gnn_params'] = global_functions::encode_object($params_array);

        $result = $db->build_insert('gnn',$insert_array);
        if (!$est_job_id && (!$parms['child_type'] || $parms['child_type'] != 'filter')) {
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
        );
        $info = self::create4($db, $parms);
        if ($info === false)
            return 0;
        else
            return $info['id'];
    }
}

?>
