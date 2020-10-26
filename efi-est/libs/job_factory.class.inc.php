<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/includes/main.inc.php");
require_once(__DIR__."/generate.class.inc.php");
require_once(__DIR__."/fasta.class.inc.php");
require_once(__DIR__."/accession.class.inc.php");
require_once(__DIR__."/colorssn.class.inc.php");
require_once(__DIR__."/cluster_analysis.class.inc.php");
require_once(__DIR__."/nb_conn.class.inc.php");
require_once(__DIR__."/conv_ratio.class.inc.php");
require_once(__DIR__."/blast.class.inc.php");
require_once(__DIR__."/stepa.class.inc.php");


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
            return new family($db, $id, $arg1, $arg2);
        case "FASTA":
            return new fasta($db, $id, $arg1, $arg2);
        case "FASTA_ID":
            return new fasta($db, $id, $arg1, $arg2);
        case "ACCESSION":
            return new accession($db, $id, $arg1, $arg2);
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


