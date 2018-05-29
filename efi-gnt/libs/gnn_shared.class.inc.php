<?php

require_once('arrow_api.class.inc.php');
require_once(__DIR__.'/../../libs/global_functions.class.inc.php');

class gnn_shared extends arrow_api {

    public static function create3($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $migrate_id) {
        return self::create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, "", $migrate_id);
    }

    public static function create2($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $jobGroup) {
        return self::create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $jobGroup, "");
    }

    private static function create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, $jobGroup, $migrate_id) {
        $result = false;
        $key = global_functions::generate_key();
        $insert_array = array(
            'gnn_email' => $email,
            'gnn_size' => $size,
            'gnn_key' => $key,
            'gnn_filename' => $filename,
            'gnn_cooccurrence' => $cooccurrence,
            'gnn_status' => __NEW__);

        if ($migrate_id)
            $insert_array['gnn_source_id'] = $migrate_id;

        $result = $db->build_insert('gnn',$insert_array);
        if ($result) {	
            functions::copy_to_uploads_dir($tmp_filename, $filename, $result);
        } else {
            return false;
        }
        $info = array('id' => $result, 'key' => $key);

        if ($jobGroup && $jobGroup != settings::get_default_group_name()) {
            $jobGroup = preg_replace("/[^A-Za-z0-9]/", "", $jobGroup);
            $insertArray = array(
                'gnn_id' => $result,
                'user_group' => $jobGroup,
            );
            $jobGroupResult = $db->build_insert('job_group', $insertArray);
            //TODO: check result and do something if it fails? It's not critical.
        }

        return $info;
    }

    public static function create($db, $email, $size, $tmp_filename, $filename, $cooccurrence) {
        $info = create_shared($db, $email, $size, $cooccurrence, $tmp_filename, $filename, "", "");
        if ($info === false)
            return 0;
        else
            return $info['id'];
    }
}

?>
