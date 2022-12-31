<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\gnt\settings;
use \efi\gnt\functions;
use \efi\gnt\DiagramJob;
use \efi\global_functions;


class diagram_jobs {

    private $db;
    private $beta;

    public function __construct($db) {
        $this->db = $db;
        $this->beta = settings::get_release_status();
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////
    // STATIC FUNCTIONS FOR CREATING NEW JOBS

    public static function create_file($db, $email, $tmp_filename, $filename) {

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $title = self::get_diagram_title_from_file($filename);

        $job_type = $ext == "zip" ? DiagramJob::UploadedZip : DiagramJob::Uploaded;
        
        $info = self::do_database_create($db, $email, $title, $job_type, array());

        $upload_dir = settings::get_gnd_uploads_dir();

        if ($info['id']) {
            global_functions::copy_to_uploads_dir($tmp_filename, $filename, $info['id'], $upload_dir);
        } else {
            return false;
        }

        return $info;
    }

    public static function create_blast_job($db, $email, $title, $evalue, $max_num_seqs, $nb_size, $blast_seq, $db_mod, $seq_db_type) {
        
        $job_type = DiagramJob::BLAST;
        $params = array('blast_seq' => $blast_seq, 'evalue' => $evalue, 'max_num_sequence' => $max_num_seqs,
            'neighborhood_size' => $nb_size, 'db_mod' => $db_mod);
        if ($seq_db_type)
            $params['seq_db_type'] = $seq_db_type;

        $info = self::do_database_create($db, $email, $title, $job_type, $params);

        return $info;
    }

    public static function create_lookup_job($db, $email, $title, $nb_size, $content, $job_type, $db_mod, $seq_db_type, $tax_parms) {

        if ($job_type == DiagramJob::IdLookup) {
            $content = preg_replace("/\s+/", ",", $content);
            $content = preg_replace("/,,+/", ",", $content);
            $content = preg_replace("/,+$/", "", $content);
        }

        return self::do_create_diagram_job($db, $email, $title, $nb_size, $job_type, "txt", $content, $db_mod, $seq_db_type, $tax_parms);
    }

    public static function create_file_lookup_job($db, $email, $title, $nb_size, $temp_name, $file_name, $job_type, $db_mod, $seq_db_type) {
        return self::do_create_file_diagram_job($db, $email, $title, $nb_size, $temp_name, $file_name, $job_type, "txt", $db_mod, $seq_db_type);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Private Helpers

    private static function do_create_diagram_job($db, $email, $title, $nb_size, $job_type, $ext, $contents, $db_mod, $seq_db_type, $tax_parms) {
        $params = array('neighborhood_size' => $nb_size, 'db_mod' => $db_mod);

        if (isset($seq_db_type))
            $params['seq_db_type'] = $seq_db_type;

        if (is_array($tax_parms)) {
            $params['tax_job_id'] = $tax_parms['tax_job_id'];
            $params['tax_id_type'] = $tax_parms['tax_id_type'];
            $params['tax_tree_id'] = $tax_parms['tax_tree_id'];
        }

        $info = self::do_database_create($db, $email, $title, $job_type, $params);

        if ($info === false || !$info["id"]) {
            return false;
        }

        $uploadsDir = settings::get_gnd_uploads_dir();
        if ($uploadsDir === false)
            return false;
        
        $file_name = $info["id"] . ".$ext";
        $filePath = "$uploadsDir/$file_name";

        $retCode = file_put_contents($filePath, $contents);
        if ($retCode === false)
            return false;

        return $info;
    }

    private static function do_create_file_diagram_job($db, $email, $title, $nb_size, $temp_name, $file_name, $job_type, $ext, $db_mod, $seq_db_type) {

        $params = array('neighborhood_size' => $nb_size, 'db_mod' => $db_mod);

        if (isset($seq_db_type))
            $params['seq_db_type'] = $seq_db_type;

        if (!$title)
            $title = self::get_diagram_title_from_file($file_name);

        $info = self::do_database_create($db, $email, $title, $job_type, $params);

        $upload_dir = settings::get_gnd_uploads_dir();

        if ($info !== false && $info['id']) {
            global_functions::copy_to_uploads_dir($temp_name, $file_name, $info['id'], $upload_dir, $ext);
        } else {
            return false;
        }

        if ($info === false || !$info["id"]) {
            return false;
        }

        return $info;
    }

    private static function do_database_create($db, $email, $title, $job_type, $parms_array) {
        $key = functions::generate_key();

        $paramsJson = functions::encode_object($parms_array);

        $insertArray = array(
            'diagram_key' => $key,
            'diagram_email' => $email,
            'diagram_title' => $title,
            'diagram_type' => $job_type,
            'diagram_status' => __NEW__,
            'diagram_params' => $paramsJson,
        );

        $result = $db->build_insert('diagram', $insertArray);
        $info = array('id' => $result, 'key' => $key);
        \efi\job_shared::insert_new($db, "diagram", $result);

        return $info;
    }

    private static function get_diagram_title_from_file($file) {
        $file = preg_replace("/\.zip$/", "", $file);
        $file = preg_replace("/\.sqlite$/", "", $file);
        $file = preg_replace("/_arrow_data/", "", $file);
        return $file;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////
    // ACCESSORS

    public function get_new_jobs() {
        $jobs = array();

        $sql = "SELECT * FROM diagram WHERE diagram_status = '" . __NEW__ . "'";
        $rows = $this->db->query($sql);

        foreach ($rows as $row) {
            array_push($jobs, $row['diagram_id']);
        }

        return $jobs;
    }

    public function get_running_jobs() {
        $jobs = array();

        $sql = "SELECT * FROM diagram WHERE diagram_status = '" . __RUNNING__ . "'";
        $rows = $this->db->query($sql);

        foreach ($rows as $row) {
            array_push($jobs, $row['diagram_id']);
        }

        return $jobs;
    }

    public static function get_key($db, $id, $is_example = false) {
        $sql = "SELECT diagram_key FROM diagram WHERE diagram_id = $id";
        $result = $db->query($sql);
        if (!$result)
            return "";
        else
            return $result[0]['diagram_key'];
    }

    public static function get_time_completed($db, $id, $is_example = false) {
        $sql = "SELECT diagram_time_completed FROM diagram WHERE diagram_id = $id";
        $result = $db->query($sql);
        if (!$result)
            return false;
        else
            return $result[0]["diagram_time_completed"];
    }
}



