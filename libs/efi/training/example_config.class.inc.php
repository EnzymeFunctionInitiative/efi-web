<?php
namespace efi\training;

require_once(__DIR__."/../../../init.php");

require_once(__TRAINING_CONF_DIR__ . "/settings_examples.inc.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\est\est_user_jobs_shared;
use \efi\gnt\gnt_ui;
use \efi\cgfp\cgfp_ui;


const JOB_TYPE_EST = 1;
const JOB_TYPE_GNT = 2;
const JOB_TYPE_CGFP = 4;
const JOB_TYPE_ALL = 7;
const JOB_NONE = 0;

class example_config {

    const DEFAULT_EST_GENERATE_TABLE = "generate_example";
    const DEFAULT_EST_ANALYSIS_TABLE = "analysis_example";
    const DEFAULT_GNT_TABLE = "gnn_example";
    const DEFAULT_CGFP_IDENTIFY_TABLE = "identify_example";
    const DEFAULT_CGFP_QUANTIFY_TABLE = "quantify_example";


    private $db = false;
    private $config_file = "";

    private $est_job_ids = false;
    private $est_jobs = array();
    private $est_data_dir = "";
    private $est_generate_table = self::DEFAULT_EST_GENERATE_TABLE;
    private $est_analysis_table = self::DEFAULT_EST_ANALYSIS_TABLE;

    private $gnt_job_ids = false;
    private $gnt_jobs = array();
    private $gnt_data_dir = "";
    private $gnt_table = self::DEFAULT_GNT_TABLE;

    private $cgfp_job_ids = false;
    private $cgfp_jobs = array();
    private $cgfp_data_dir = "";
    private $cgfp_identify_table = self::DEFAULT_CGFP_IDENTIFY_TABLE;
    private $cgfp_quantify_table = self::DEFAULT_CGFP_QUANTIFY_TABLE;


    public static function get_config_file() {
        return defined("__EXAMPLE_CONFIG__") ? __EXAMPLE_CONFIG__ : "";
    }
    public static function get_configs() {
        return self::get_config();
    }
    //public static function get_configs() {
    //    if (!defined("__EXAMPLE_CONFIG__"))
    //        return array();

    //    $configs = array();
    //    $parts = explode(",", __EXAMPLE_CONFIG__);
    //    foreach ($parts as $part) {
    //        list($key, $path) = explode("=", $part);
    //        if (isset($key) && isset($path))
    //            $configs[$key] = $path;
    //    }
    //    return $configs;
    //}
    public static function get_est_generate_table($config) {
        return isset($config["config"]["est_generate_table"]) ? $config["config"]["est_generate_table"] : self::DEFAULT_EST_GENERATE_TABLE;
    }
    public static function get_est_analysis_table($config) {
        return isset($config["config"]["est_analysis_table"]) ? $config["config"]["est_analysis_table"] : self::DEFAULT_EST_ANALYSIS_TABLE;
    }
    public static function get_gnt_table($config) {
        return isset($config["config"]["gnt_table"]) ? $config["config"]["gnt_table"] : self::DEFAULT_GNT_TABLE;
    }
    public static function get_est_data_dir($config) {
        return isset($config["est.jobs"]["data_dir"]) ? $config["est.jobs"]["data_dir"] : "";
    }
    public static function get_gnt_data_dir($config) {
        return isset($config["gnt.jobs"]["data_dir"]) ? $config["gnt.jobs"]["data_dir"] : "";
    }
    public static function get_cgfp_identify_table($config) {
        return isset($config["config"]["cgfp_identify_table"]) ? $config["config"]["cgfp_identify_table"] : self::DEFAULT_CGFP_IDENTIFY_TABLE;
    }
    public static function get_cgfp_quantify_table($config) {
        return isset($config["config"]["cgfp_quantify_table"]) ? $config["config"]["cgfp_quantify_table"] : self::DEFAULT_CGFP_QUANTIFY_TABLE;
    }
    public static function get_cgfp_data_dir($config) {
        return isset($config["cgfp.jobs"]["data_dir"]) ? $config["cgfp.jobs"]["data_dir"] : "";
    }

    function __construct($db, $config_file, $job_type = JOB_TYPE_ALL) {
        $this->db = $db;
        $this->config_file = $config_file;

        if ($this->parse_config()) {
            if ($job_type & JOB_TYPE_EST)
                $this->load_est_jobs();
            if ($job_type & JOB_TYPE_GNT)
                $this->load_gnt_jobs();
            if ($job_type & JOB_TYPE_CGFP)
                $this->load_cgfp_jobs();
        }
    }

    public function get_est_jobs() {
        return $this->est_jobs;
    }
    public function get_gnt_jobs() {
        return $this->gnt_jobs;
    }
    public function get_cgfp_jobs() {
        return $this->cgfp_jobs;
    }

    public static function get_config($config_file) {
        $config = parse_ini_file($config_file, true);
        if ($config === false)
            return false;
        else
            return $config;
    }

    private function parse_config() {
        $config = self::get_config($this->config_file);
        if ($config === false)
            return false;

        $est_job_ids = $config["est.jobs"]["generate"];
        if (isset($est_job_ids) && is_array($est_job_ids))
            $this->est_job_ids = $est_job_ids;
        else
            $this->est_job_ids = array();
        $this->est_data_dir = self::get_est_data_dir($config);
        $this->est_generate_table = self::get_est_generate_table($config);
        $this->est_analysis_table = self::get_est_analysis_table($config);

        $gnt_job_ids = $config["gnt.jobs"]["gnn"];
        if (isset($gnt_job_ids) && is_array($gnt_job_ids))
            $this->gnt_job_ids = $gnt_job_ids;
        else
            $this->gnt_job_ids = array();
        $this->gnt_data_dir = self::get_gnt_data_dir($config);
        $this->gnt_table = self::get_gnt_table($config);

        $cgfp_job_ids = $config["cgfp.jobs"]["identify"];
        if (isset($cgfp_job_ids) && is_array($cgfp_job_ids))
            $this->cgfp_job_ids = $cgfp_job_ids;
        else
            $this->cgfp_job_ids = array();
        $this->cgfp_data_dir = self::get_cgfp_data_dir($config);
        $this->cgfp_identify_table = self::get_cgfp_identify_table($config);
        $this->cgfp_quantify_table = self::get_cgfp_quantify_table($config);

        return true;
    }

    private function load_est_jobs() {
        $est_db = global_settings::get_est_database();
        $generate_table = $this->est_generate_table;
        $sql = "SELECT generate_id, generate_key, generate_time_completed, generate_status, generate_type, generate_params FROM ${est_db}.${generate_table} ";

        $func = function($val) use($generate_table) { return "generate_id = $val"; };
        $where = implode(" OR ", array_map($func, $this->est_job_ids));

        if (!$where) {
            $this->est_jobs = array();
            return false;
        }

        $sql .= " WHERE ($where) ORDER BY generate_status, generate_time_completed DESC";

        $rows = $this->db->query($sql);

        $family_lookup_fn = function($family_id) {};
        $include_failed_analysis_jobs = false;
        $include_analysis_jobs = true;
        $analysis_table = "analysis_example";
        $this->est_jobs = est_user_jobs_shared::process_load_generate_rows($this->db, $rows, $include_analysis_jobs, $include_failed_analysis_jobs, $family_lookup_fn, "", $analysis_table, $est_db);

        return true;
    }

    private function load_gnt_jobs() {
        $gnt_db = global_settings::get_gnt_database();
        $gnn_table = $this->gnt_table;
        $sql = "SELECT gnn_id, gnn_key, gnn_time_completed, gnn_status, gnn_params, gnn_parent_id, gnn_child_type FROM ${gnt_db}.${gnn_table} ";
        
        $func = function($val) use($gnn_table) { return "gnn_id = $val"; };
        $where = implode(" OR ", array_map($func, $this->gnt_job_ids));

        if (!$where) {
            $this->gnt_jobs = array();
            return false;
        }

        $sql .= " WHERE ($where) ORDER BY gnn_status, gnn_time_completed DESC";
        $rows = $this->db->query($sql);

        $include_failed_jobs = false;
        $this->gnt_jobs = gnt_ui::process_load_rows($this->db, $rows, $include_failed_jobs, "");

        return true;
    }

    private function load_cgfp_jobs() {
        $cgfp_db = global_settings::get_cgfp_database();
        $id_table = $this->cgfp_identify_table;
        $qu_table = $this->cgfp_quantify_table;

        $sql = "SELECT identify_id, identify_key, identify_time_completed, identify_params, identify_status, identify_parent_id FROM ${cgfp_db}.${id_table} ";
        
        $func = function($val) { return "identify_id = $val"; };
        $where = implode(" OR ", array_map($func, $this->cgfp_job_ids));

        if (!$where) {
            $this->cgfp_jobs = array();
            return false;
        }

        $sql .= " WHERE ($where) ORDER BY identify_status, identify_time_completed DESC";
        $rows = $this->db->query($sql);

        $this->cgfp_jobs = cgfp_ui::process_load_rows($this->db, $rows, $id_table, $qu_table, $cgfp_db);

        return true;
    }
}

?>
