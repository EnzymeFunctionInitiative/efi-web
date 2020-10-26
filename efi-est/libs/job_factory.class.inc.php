<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");


class job_factory {
    public static function get_job_type($db, $id) {
        $sql = "SELECT generate_type, generate_params FROM generate WHERE generate_id = $id";
        $result = $db->query($sql);
        $result = isset($result[0]) ? $result[0] : null;
        if (isset($result))
            return $result["generate_type"];
        else
            return "";
    }
    public static function create($db, $id, $arg1 = null, $arg2 = null) {
        $type = $id;
        if (is_numeric($id))
            $type = self::get_job_type($db, $id);
        switch ($type) {
        case "FAMILIES":
            return new FAMILIES($db, $id, $arg1, $arg2);
        case "FASTA":
            return new fasta($db, $id, $arg1, $arg2);
        case "FASTA_ID":
            return new FASTA_ID($db, $id, $arg1, $arg2);
        case "ACCESSION":
            return new ACCESSION($db, $id, $arg1, $arg2);
        case "COLORSSN":
            return new colorssn($db, $id, $arg1, $arg2);
        case "CLUSTER":
            return new cluster_analysis($db, $id, $arg1, $arg2);
        case "NBCONN":
            return new nb_conn($db, $id, $arg1, $arg2);
        case "CONVRATIO":
            return new conv_ratio($db, $id, $arg1, $arg2);
        case "BLAST":
            return new blast($db, $id, $arg1, $arg2);
        default:
            return new stepa($db, $id, $arg1, $arg2);
        }
    }
}


