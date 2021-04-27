<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__DIR__."/functions.class.inc.php");
require_once(__DIR__."/const.class.inc.php");
require_once(__DIR__."/settings.class.inc.php");


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

        $uploadPrefix = settings::get_diagram_upload_prefix();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $title = self::get_diagram_title_from_file($filename);

        $jobType = $ext == "zip" ? DiagramJob::UploadedZip : DiagramJob::Uploaded;
        
        $info = self::do_database_create($db, $email, $title, $jobType, array());

        if ($info['id']) {
            functions::copy_to_uploads_dir($tmp_filename, $filename, $info['id'], $uploadPrefix, $ext);
        } else {
            return false;
        }

        return $info;
    }

    public static function create_blast_job($db, $email, $title, $evalue, $maxNumSeqs, $nbSize, $blastSeq, $dbMod, $seqDbType) {
        
        $jobType = DiagramJob::BLAST;
        $params = array('blast_seq' => $blastSeq, 'evalue' => $evalue, 'max_num_sequence' => $maxNumSeqs,
            'neighborhood_size' => $nbSize, 'db_mod' => $dbMod);
        if ($seqDbType)
            $params['seq_db_type'] = $seqDbType;

        $info = self::do_database_create($db, $email, $title, $jobType, $params);

        return $info;
    }

    public static function create_lookup_job($db, $email, $title, $nbSize, $content, $jobType, $dbMod, $seqDbType) {

        if ($jobType == DiagramJob::IdLookup) {
            $content = preg_replace("/\s+/", ",", $content);
            $content = preg_replace("/,,+/", ",", $content);
            $content = preg_replace("/,+$/", "", $content);
        }

        return self::do_create_diagram_job($db, $email, $title, $nbSize, $jobType, "txt", $content, $dbMod, $seqDbType);
    }

    public static function create_file_lookup_job($db, $email, $title, $nbSize, $tempName, $fileName, $jobType, $dbMod, $seqDbType) {
        return self::do_create_file_diagram_job($db, $email, $title, $nbSize, $tempName, $fileName, $jobType, "txt", $dbMod, $seqDbType);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Private Helpers

    private static function do_create_diagram_job($db, $email, $title, $nbSize, $jobType, $ext, $contents, $dbMod, $seqDbType) {
        $params = array('neighborhood_size' => $nbSize, 'db_mod' => $dbMod);

        if (isset($seqDbType))
            $params['seq_db_type'] = $seqDbType;

        $info = self::do_database_create($db, $email, $title, $jobType, $params);

        if ($info === false || !$info["id"]) {
            return false;
        }

        $prefix = settings::get_diagram_upload_prefix();
        $uploadsDir = settings::get_uploads_dir();
        if ($uploadsDir === false)
            return false;
        
        $fileName = $prefix . $info["id"] . ".$ext";
        $filePath = "$uploadsDir/$fileName";

        $retCode = file_put_contents($filePath, $contents);
        if ($retCode === false)
            return false;

        return $info;
    }

    private static function do_create_file_diagram_job($db, $email, $title, $nbSize, $tempName, $fileName, $jobType, $ext, $dbMod, $seqDbType) {

        $uploadPrefix = settings::get_diagram_upload_prefix();
        $params = array('neighborhood_size' => $nbSize, 'db_mod' => $dbMod);

        if (isset($seqDbType))
            $params['seq_db_type'] = $seqDbType;

        if (!$title)
            $title = self::get_diagram_title_from_file($fileName);

        $info = self::do_database_create($db, $email, $title, $jobType, $params);

        if ($info !== false && $info['id']) {
            functions::copy_to_uploads_dir($tempName, $fileName, $info['id'], $uploadPrefix, $ext);
        } else {
            return false;
        }

        if ($info === false || !$info["id"]) {
            return false;
        }

        return $info;
    }

    private static function do_database_create($db, $email, $title, $jobType, $paramsArray) {
        $key = functions::generate_key();

        $paramsJson = functions::encode_object($paramsArray);

        $insertArray = array(
            'diagram_key' => $key,
            'diagram_email' => $email,
            'diagram_title' => $title,
            'diagram_type' => $jobType,
            'diagram_status' => __NEW__,
            'diagram_params' => $paramsJson,
        );

        $result = $db->build_insert('diagram', $insertArray);
        $info = array('id' => $result, 'key' => $key);

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

    public static function get_key($db, $id) {
        $sql = "SELECT diagram_key FROM diagram WHERE diagram_id = $id";
        $result = $db->query($sql);
        if (!$result)
            return "";
        else
            return $result[0]['diagram_key'];
    }

    public static function get_time_completed($db, $id) {
        $sql = "SELECT diagram_time_completed FROM diagram WHERE diagram_id = $id";
        $result = $db->query($sql);
        if (!$result)
            return false;
        else
            return $result[0]["diagram_time_completed"];
    }
}


?>

