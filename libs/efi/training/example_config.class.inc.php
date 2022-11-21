<?php
namespace efi\training;

require_once(__DIR__."/../../../init.php");

require_once(__TRAINING_CONF_DIR__ . "/settings_examples.inc.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\est\est_user_jobs_shared;
use \efi\est\est_ui;
use \efi\gnt\gnt_ui;
use \efi\cgfp\cgfp_ui;
use \efi\est\user_jobs;


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
    private $all_est_jobs = array();
    private $est_data_dir = "";
    private $est_generate_table = self::DEFAULT_EST_GENERATE_TABLE;
    private $est_analysis_table = self::DEFAULT_EST_ANALYSIS_TABLE;

    private $gnt_job_ids = false;
    private $gnt_jobs = array();
    private $all_gnt_jobs = array();
    private $gnt_data_dir = "";
    private $gnt_table = self::DEFAULT_GNT_TABLE;

    private $cgfp_job_ids = false;
    private $cgfp_jobs = array();
    private $all_cgfp_jobs = array();
    private $cgfp_data_dir = "";
    private $cgfp_identify_table = self::DEFAULT_CGFP_IDENTIFY_TABLE;
    private $cgfp_quantify_table = self::DEFAULT_CGFP_QUANTIFY_TABLE;

    private $section_order = false;
    private $example_id = "";
    private $use_tabs = false;


    public static function get_config_file($id = "") {
        $file = defined("__EXAMPLE_CONFIG__") ? __EXAMPLE_CONFIG__ : "";
        if (!$file) {
            return $file;
        } else {
            $files = explode(";", $file);
            for ($i = 0; $i < count($files); $i++) {
                $p = explode("=", $files[$i]);
                if (count($p) > 1 && (!$id || $p[0] == $id)) {
                    return $p[1];
                }
            }
            return false;
        }
    }
    public static function get_est_generate_table($config) {
        return isset($config["est_generate_table"]) ? $config["est_generate_table"] : self::DEFAULT_EST_GENERATE_TABLE;
    }
    public static function get_est_analysis_table($config) {
        return isset($config["est_analysis_table"]) ? $config["est_analysis_table"] : self::DEFAULT_EST_ANALYSIS_TABLE;
    }
    public static function get_gnt_table($config) {
        return isset($config["gnt_table"]) ? $config["gnt_table"] : self::DEFAULT_GNT_TABLE;
    }
    public static function get_est_data_dir($config) {
        return isset($config["est_data_dir"]) ? $config["est_data_dir"] : "";
    }
    public static function get_gnt_data_dir($config) {
        return isset($config["gnt_data_dir"]) ? $config["gnt_data_dir"] : "";
    }
    public static function get_cgfp_identify_table($config) {
        return isset($config["cgfp_identify_table"]) ? $config["cgfp_identify_table"] : self::DEFAULT_CGFP_IDENTIFY_TABLE;
    }
    public static function get_cgfp_quantify_table($config) {
        return isset($config["cgfp_quantify_table"]) ? $config["cgfp_quantify_table"] : self::DEFAULT_CGFP_QUANTIFY_TABLE;
    }
    public static function get_cgfp_data_dir($config) {
        return isset($config["cgfp_data_dir"]) ? $config["cgfp_data_dir"] : "";
    }

    function __construct($db, $id, $job_type = JOB_TYPE_ALL) {
        $this->db = $db;
        $this->example_id = $id;

        $config_file = self::get_config_file($id);
        $this->config_file = $config_file;

        list($sort_by_id, $use_tabs, $sections, $config_data) = self::parse_config($this->config_file);

        $this->config = $config_data;
        $this->sort_by_id = $sort_by_id;
        $this->use_tabs = $use_tabs;
        $this->section_order = $sections;

        if ($config_data) {
            if ($job_type & JOB_TYPE_EST)
                $this->load_est_jobs();
            if ($job_type & JOB_TYPE_GNT)
                $this->load_gnt_jobs();
            if ($job_type & JOB_TYPE_CGFP)
                $this->load_cgfp_jobs();
        }
    }

    public function get_est_jobs($section = "est.jobs") {
        return isset($this->est_jobs[$section]) ? $this->est_jobs[$section] : array();
    }
    public function get_gnt_jobs($section = "gnt.jobs") {
        return isset($this->gnt_jobs[$section]) ? $this->gnt_jobs[$section] : array();
    }
    public function get_cgfp_jobs($section = "cgfp.jobs") {
        return isset($this->cgfp_jobs[$section]) ? $this->cgfp_jobs[$section] : array();
    }
    public function get_section_order() {
        return $this->section_order;
    }
    public function get_use_tabs() {
        return $this->use_tabs;
    }

    // Get the default example data (if there are multiple sections, get the first one).
    public static function get_example_data($id = "") {
        $parse_data = self::parse_config(self::get_config_file($id));
        if (!$parse_data)
            return false;
        list($sort_by_id, $use_tabs, $sections, $data) = $parse_data;

        $info = array();
        foreach ($data as $key => $conf) {
            if (self::is_est_job($key) && !isset($info["est_generate_table"])) {
                $info["est_generate_table"] = $conf["table_name"][0];
                $info["est_analysis_table"] = $conf["table_name"][1];
                $info["est_data_dir"] = $conf["data_dir"];
            } else if (preg_match("/^gn/", $key) && !isset($info["gnt_table"])) {
                $info["gnt_table"] = $conf["table_name"];
                $info["gnt_data_dir"] = $conf["data_dir"];
            } else if (preg_match("/^cgfp/", $key) && !isset($info["cgfp_identify_table"])) {
                $info["cgfp_identify_table"] = $conf["table_name"][0];
                $info["cgfp_quantify_table"] = $conf["table_name"][1];
                $info["cgfp_data_dir"] = $conf["data_dir"];
            }
        }
        return $info;
    }

    public static function is_example($params = null) {
        if (!$params || !is_array($params))
            $params = $_GET;
        $is_example = isset($params["x"]) ? $params["x"] : false;
        if ($is_example !== false && preg_match("/^[a-z0-9]+$/i", $is_example)) {
            if (!self::get_config_file($is_example))
                $is_example = false;
        }
        return $is_example;
    }

    private static function parse_config($config_file) {
        $config = parse_ini_file($config_file, true);
        if ($config === false)
            return false;

        $sections = array("est.jobs", "gnt.jobs", "cgfp.jobs"); // also order
        $titles = array("est.jobs" => "EST Jobs", "gnt.jobs" => "GNT Jobs", "cgfp.jobs" => "CGFP Jobs");
        $sort_by_id = false;
        $use_tabs = false;
        if (isset($config["sections"])) {
            $conf = $config["sections"];
            if (isset($conf["order"])) {
                $sections = explode(",", $conf["order"]);
                for ($i = 0; $i < count($sections); $i++) {
                    $S = $sections[$i];
                    if (isset($conf[$S]))
                        $titles[$S] = $conf[$S];
                }
            }
            if (isset($conf["sort_by"]) && $conf["sort_by"] == "id")
                $sort_by_id = true;
            if (isset($conf["tabs"]) && $conf["tabs"] == "section")
                $use_tabs = true;
        }

        $data = array();

        for ($i = 0; $i < count($sections); $i++) {
            $key = $sections[$i];
            $conf = $config[$key];

            $job_ids = (isset($conf["job_id"]) && is_array($conf["job_id"])) ? $conf["job_id"] : array();
            $title = isset($titles[$key]) ? $titles[$key] : strtoupper($key);
            $data_dir = isset($conf["data_dir"]) ? $conf["data_dir"] : "";
            $table_name = self::parse_table_name($key, $conf); // if cgfp or est, returns array

            $data[$key] = array("id" => $key, "title" => $title, "data_dir" => $data_dir, "job_ids" => $job_ids, "table_name" => $table_name);
        }

        return array($sort_by_id, $use_tabs, $sections, $data);
    }

    public function output_section($id) {
        if (!isset($this->config[$id]))
            return "";

        $data = $this->config[$id];

        $output = "";
        if (isset($data["title"]))
            $output = "<h4 style=\"margin-top:50px\">" . $data["title"] . "</h4>\n";

        if (self::is_est_job($id)) {
            $output .= est_ui::output_job_list($this->est_jobs[$id], false, false, $this->example_id);
        } else if (preg_match("/^gn/", $id)) {
            $output .= gnt_ui::output_job_list($this->gnt_jobs[$id], $this->example_id);
        } else if (preg_match("/^cgfp/", $id)) {
            $output .= cgfp_ui::output_job_list($this->cgfp_jobs[$id], false, $this->example_id);
        }

        return $output;
    }

    private static function parse_table_name($id, $config) {
        $retval = "";
        if (self::is_est_job($id)) {
            $retval = array(self::DEFAULT_EST_GENERATE_TABLE, self::DEFAULT_EST_ANALYSIS_TABLE);
            if (isset($config["generate_table_name"]))
                $retval[0] = $config["generate_table_name"];
            if (isset($config["analysis_table_name"]))
                $retval[1] = $config["analysis_table_name"];
        } else if (preg_match("/^gn/", $id)) {
            if (isset($config["gnt_table_name"]))
                $retval = $config["gnt_table_name"];
            else
                $retval = self::DEFAULT_GNT_TABLE;
        } else if (preg_match("/^cgfp/", $id)) {
            $retval = array(self::DEFAULT_CGFP_IDENTIFY_TABLE, self::DEFAULT_CGFP_QUANTIFY_TABLE);
            if (isset($config["identify_table_name"]))
                $retval[0] = $config["identify_table_name"];
            if (isset($config["quantify_table_name"]))
                $retval[1] = $config["quantify_table_name"];
        }
        return $retval;        
    }

    private function load_est_jobs() {
        $est_db = global_settings::get_est_database();
        $generate_table = $this->est_generate_table;

        foreach ($this->config as $id => $data) {
            if (!self::is_est_job($id))
                continue;

            $sql = "SELECT generate_id, generate_key, generate_time_completed, generate_status, generate_type, generate_params FROM ${est_db}.${generate_table} ";
            $func = function($val) use($generate_table) { return "generate_id = $val"; };
            $where = implode(" OR ", array_map($func, $data["job_ids"]));
    
            if (!$where)
                continue;
    
            $order_by = $this->sort_by_id ? "generate_id ASC" : "generate_status, generate_time_completed DESC";
            $sql .= " WHERE ($where) ORDER BY $order_by";
            $rows = $this->db->query($sql);
    
            $family_lookup_fn = function($family_id) {};
            $include_failed_analysis_jobs = false;
            $include_analysis_jobs = true;
            $analysis_table = "analysis_example";
            $est_jobs = est_user_jobs_shared::process_load_generate_rows($this->db, $rows, $include_analysis_jobs, $include_failed_analysis_jobs, $family_lookup_fn, user_jobs::SORT_TIME_COMPLETED, "", $analysis_table, $est_db);

            $this->est_jobs[$id] = $est_jobs;
            array_merge($this->all_est_jobs, $est_jobs);
        }

        return true;
    }

    private function load_gnt_jobs() {
        $gnt_db = global_settings::get_gnt_database();
        $gnn_table = $this->gnt_table;

        foreach ($this->config as $id => $data) {
            if (!preg_match("/^gn/", $id))
                continue;

            $sql = "SELECT gnn_id, gnn_key, gnn_time_completed, gnn_status, gnn_params, gnn_parent_id, gnn_child_type FROM ${gnt_db}.${gnn_table} ";
            $func = function($val) use($gnn_table) { return "gnn_id = $val"; };
            $where = implode(" OR ", array_map($func, $data["job_ids"]));
    
            if (!$where)
                continue;
    
            $order_by = $this->sort_by_id ? "gnn_id ASC" : "gnn_status, gnn_time_completed DESC";
            $sql .= " WHERE ($where) ORDER BY $order_by";
            $rows = $this->db->query($sql);
    
            $include_failed_jobs = false;
            $gnt_jobs = gnt_ui::process_load_rows($this->db, $rows, $include_failed_jobs, "");

            $this->gnt_jobs[$id] = $gnt_jobs;
            array_merge($this->all_gnt_jobs, $gnt_jobs);
        }

        return true;
    }

    private function load_cgfp_jobs() {
        $cgfp_db = global_settings::get_cgfp_database();
        $id_table = $this->cgfp_identify_table;
        $qu_table = $this->cgfp_quantify_table;

        foreach ($this->config as $id => $data) {
            if (!preg_match("/^cgfp/", $id))
                continue;

            $sql = "SELECT identify_id, identify_key, identify_time_completed, identify_params, identify_status, identify_parent_id FROM ${cgfp_db}.${id_table} ";
            $func = function($val) { return "identify_id = $val"; };
            $where = implode(" OR ", array_map($func, $data["job_ids"]));
    
            if (!$where)
                continue;
    
            $order_by = $this->sort_by_id ? "identify_id ASC" : "identify_status, identify_time_completed DESC";
            $sql .= " WHERE ($where) ORDER BY $order_by";
            $rows = $this->db->query($sql);

            $cgfp_jobs = cgfp_ui::process_load_rows($this->db, $rows, $id_table, $qu_table, $cgfp_db);
    
            $this->cgfp_jobs[$id] = $cgfp_jobs;
            array_merge($this->all_cgfp_jobs, $cgfp_jobs);
        }

        return true;
    }

    private static function is_est_job($id) {
        return preg_match("/^(est|tax)/", $id);
    }
}

