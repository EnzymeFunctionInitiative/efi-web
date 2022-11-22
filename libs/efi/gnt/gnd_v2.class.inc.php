<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\gnt\gnd;


class gnd_v2 extends gnd {

    
    private $has_stats = false;
    private $query_set_idtype = false;
    private $uniref_query_id = false;
    private $use_cluster_id_map = false;
    private $query = array(); // list of queried items
    private $range = array(); // range of cluster_index to retrieve


    function __construct($db, $params, $job_factory, $is_example = false) {
        parent::__construct($db, $params, $job_factory, $is_example);

        $this->parse_params($params);
    }


    protected function parse_params($params) {
        $this->page_size = 20;
        if (isset($params["pagesize"]) && is_numeric($params["pagesize"]))
            $this->page_size = $params["pagesize"];
        if (isset($params["sidx"]) && isset($params["eidx"]) && is_numeric($params["sidx"]) && is_numeric($params["eidx"]))
            $this->page_size = array($params["sidx"], $params["eidx"]);
        if (isset($params["stats"]) && (isset($params["query"]) || isset($params["rs-id"])))
            $this->has_stats = true;
        if (isset($params["rs-id"])) {
            $this->query = $this->parse_query($params["rs-id"]);
            $this->use_cluster_id_map = true;
        } else if (isset($params["query"]) && !isset($params["range"])) {
            $this->query = $this->parse_query($params["query"]);
        }
        if (isset($params["mode"]) && $params["mode"] == "rt")
            $this->query = $this->parse_query($params["query"]);
        if (isset($params["range"]))
            $this->range = $this->parse_range($params["range"]);
        if (isset($params["id-type"])) {
            $this->query_set_idtype = true;
            if ($params["id-type"] == 50 || $params["id-type"] == 90) {
                $this->open_db_file();
                $ur_ver = $this->check_uniref_exists();
                if (($ur_ver == 50 && ($params["id-type"] == 50 || $params["id-type"] == 90)) || ($ur_ver == 90 && $params["id-type"] == 90)) {
                    $this->set_uniref_version($params["id-type"]);
                    if (isset($params["uniref-id"]))
                        $this->uniref_query_id = $params["uniref-id"];
                }
                $this->db->close();
            }
        }
    }

    private function parse_range($range) {
        $ranges = array();
        $subranges = preg_split("/,+/", $range);
        foreach ($subranges as $subrange) {
            $parts = explode("-", $subrange);
            if (count($parts) > 1 && is_numeric($parts[0]) && is_numeric($parts[1]))
                array_push($ranges, array(intval($parts[0]), intval($parts[1])));
            elseif (count($parts) > 0 && is_numeric($parts[0]))
                array_push($ranges, array(intval($parts[0]), intval($parts[0])));
        }
        return $ranges;
    }


    public function check_for_stats() { return $this->has_stats; }
    public function get_stats() {
        $output = $this->create_output();
        $db_file = $this->open_db_file();
        if (isset($this->rt_id) && $this->rt_id)
            $output["rt"] = array("rt_id" => $this->rt_id); //, "file" => $db_file);
        $S = microtime(true); //TIME
        $stats = $this->compute_stats();
        $T = microtime(true) - $S; //TIME
        $output["stats"] = $stats;
        $output["totaltime"] = $T; //TIME
        return $output;
    }

    
    // Returns the column to use for retrieving the IDs from the attributes database table.
    protected function get_select_id_col_name() {
        return "cluster_index";
    }

    private function get_uniref_table_basename() {
        return "uniref" . $this->use_uniref;
    }
    private function get_cluster_index_table($id_lookup = false) {
        $prefix = "";
        if (!$id_lookup && $this->use_uniref !== false) {
            $prefix = $this->get_uniref_table_basename() . "_";
            if ($this->uniref_query_id || $id_lookup == true)
                return $prefix . "range";
            else
                return $prefix . "cluster_index";
        } else {
            return $id_lookup ? "attributes" : "cluster_index";
        }
    }
    protected function get_retrieved_ids() {

        $db = $this->db;

        $query_fn = function ($ids, $table, $table_col) use ($db) {
            $new_ids = array();
            for ($i = 0; $i < count($ids); $i++) {
                $sql = "SELECT cluster_index FROM $table WHERE $table_col = '" . $ids[$i] . "'";
                $result = $this->db->query($sql);
                if ($result) {
                    $row = $result->fetchArray(SQLITE3_ASSOC);
                    if (isset($row["cluster_index"]))
                        array_push($new_ids, $row["cluster_index"]);
                }
            }
            return $new_ids;
        };

        if (count($this->range) == 0 && $this->query) {
            return $query_fn($this->query, "attributes", "accession");
        }

        $ids = $this->expand_range($this->range);
        if ($this->use_cluster_id_map === false && $this->use_uniref === false)
            return $ids;

        if ($this->use_cluster_id_map === true && $this->uniref_query_id === false) {
            $table_col = "member_index";
            if ($this->use_uniref !== false) {
                $uniref_table = $this->get_uniref_table_basename();
                $uniref_table_suffix = "range";
                $uniref_table_col = "uniref_index";
                $uniref_table = "${uniref_table}_${uniref_table_suffix}";
                
                $cluster_id_table = "cluster_id_uniref".$this->use_uniref."_attr_index";
                $uniref_ids = $query_fn($ids, $cluster_id_table, $table_col);

                $new_ids = $query_fn($uniref_ids, $uniref_table, "uniref_index");
                return $new_ids;
            } else {
                $table = "cluster_id_uniprot_attr_index";
                return $query_fn($ids, $table, $table_col);
            }
        } else if ($this->use_uniref !== false) {
            $base = $this->get_uniref_table_basename();
            $table_suffix = "range";
            $table_col = "uniref_index";
            if ($this->uniref_query_id) {
                $table_suffix = "index";
                $table_col = "member_index";
            }
            return $query_fn($ids, "${base}_${table_suffix}", $table_col);
        }

        return $ids;
        //$sql = "SELECT attributes.cluster_index AS clsuter_index FROM ${table}_
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Methods for computing statistics on the query.
    //

    private function compute_stats() {
        $S = microtime(true); //TIME
        // Check if the database has UniRef support
        $has_uniref = false;
        $has_uniref = $this->check_uniref_exists();
//        if ($this->check_uniref_exists(50))
//            $has_uniref = 50;
//        else if ($this->check_uniref_exists(90))
//            $has_uniref = 90;
        if ($has_uniref !== false && !$this->query_set_idtype && !$this->uniref_query_id)
            $this->set_uniref_version($has_uniref);

        $index_range = $this->get_cluster_indices();
        $parse_time = microtime(true) - $S; //TIME
        $all_idx = $this->expand_range($index_range);
        $count = count($all_idx);

        $idx = array_slice($all_idx, 0, 100);
        $check_num = max(1, round(intval($count / 200) / 10) * 10);
        if ($count > 100) {
            for ($i = 100; $i < $count; $i++) {
                if (!(rand(1, $count) % $check_num))
                    array_push($idx, $all_idx[$i]);
            }
        }

        $scale_cap = 40000; // Limit the scale cap by default to 40000 bp. The user can zoom out.
        $S = microtime(true); //TIME
        list($scale_factor, $legend_scale, $min, $max, $q_width, $actual_max_width, $time_data) =
             $this->compute_set_scale_factor($idx, $scale_cap);
        $proc_time = microtime(true) - $S; //TIME

        $stats = array("max_index" => $count - 1, "scale_factor" => $scale_factor, "legend_scale" => $legend_scale,
            "min_bp" => $min, "max_bp" => $max, "query_width" => $q_width, "actual_max_width" => $actual_max_width,
            "time_data" => $time_data . " PROC=$proc_time PARSE=$parse_time",
            "num_checked" => count($idx), "index_range" => $index_range, "has_uniref" => $has_uniref);

        return $stats;
    }

    private function check_uniref_exists() {
        if ($this->use_cluster_id_map) {
            $status = $this->check_table_exists("cluster_id_uniref_support");
            if ($status === false || count($this->query) < 1)
                return false;
            $cluster_id = strtolower($this->db->escapeString($this->query[0]));
            $parts = explode(":", $cluster_id);
            $ascore = "";
            if (isset($parts[1])) {
                $cluster_id = $parts[0];
                $ascore = " AND ascore = '$parts[1]'";
            }
            $sql = "SELECT uniref_version FROM cluster_id_uniref_support WHERE cluster_id = '$cluster_id' $ascore";
            $result = $this->db->query($sql);
            if ($result) {
                $result = $result->fetchArray(SQLITE3_ASSOC);
                if ($result and isset($result["uniref_version"]))
                    return $result["uniref_version"] == 50 ? 50 : ($result["uniref_version"] == 90 ? 90 : false);
                else
                    return false;
            } else {
                return false;
            }
        } else {
            if ($this->check_table_exists("uniref50_index"))
                return 50;
            else if ($this->check_table_exists("uniref90_index"))
                return 90;
            else
                return false;
        }
    }
    private function check_cluster_map_exists() {
        $this->check_table_exists("cluster_id_uniprot_range");
    }
    private function check_table_exists($table) {
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = '$table'";
        $result = $this->db->query($sql);
        if ($result && $result->fetchArray()) {
            $sql = "SELECT COUNT(*) AS check_col FROM $table";
            $result = $this->db->query($sql);
            if ($result && $result->fetchArray())
                return true;
        }
        return false;
    }

    // Get the start and ending ID indexes for the given cluster inputs
    private function get_cluster_indices() {
        $ranges = array();

        $db = $this->db;
        $default_start_col = "start_index";
        $default_end_col = "end_index";
        $index_table = $this->get_cluster_index_table(false);
        $id_lookup_table = $this->get_cluster_index_table(true);
        $use_uniref = $this->use_uniref;

        $query_fn = function($table, $id_col, $item, $special_where = "", $start_col = "", $end_col = "") use ($db, $default_start_col, $default_end_col) {
            if (!$start_col)
                $start_col = $default_start_col;
            if (!$end_col)
                $end_col = $default_end_col;
            $cols = $start_col == $end_col ? $start_col : "$start_col, $end_col";
            $where = $special_where ? $special_where : "$id_col = '$item'";
            $sql = "SELECT $cols FROM $table WHERE $where";
            $query_result = $db->query($sql);
            if ($query_result) {
                $result = $query_result->fetchArray(SQLITE3_ASSOC);
                if ($result && isset($result[$start_col]))
                    return array($result[$start_col], $result[$end_col]);
            }
        };

        #, $start_col, $end_col
        $cluster_num_fn = function($item) use ($db, $index_table, $query_fn) {
            $item = $db->escapeString($item);
            return $query_fn($index_table, "cluster_num", $item);
        };

        $accession_fn = function($item) use ($db, $id_lookup_table, $query_fn) {
            $item = $db->escapeString($item);
            return $query_fn($id_lookup_table, "accession", $item, "", "cluster_index", "cluster_index");
        };

        $cluster_id_fn = function($cluster_id) use ($db, $query_fn, $use_uniref) {
            $cluster_id = strtolower($cluster_id);
            $parts = explode(":", $cluster_id);

            $cid = $parts[0];
            $where = "cluster_id = '" . $db->escapeString($cid) . "'";

            if (count($parts) > 1)
                $where .= " AND ascore = '" . $db->escapeString($parts[1]) . "'";

            if ($use_uniref)
                $table = "cluster_id_uniref${use_uniref}_range";
            else
                $table = "cluster_id_uniprot_range";
            $result = $query_fn($table, "", $cluster_id, $where);
            return $result;
        };

        $uniref_id_fn = function($item) use ($db, $index_table, $query_fn) {
            $item = $db->escapeString($item);
            return $query_fn($index_table, "uniref_id", $item);
        };

        if ($this->use_uniref !== false && $this->uniref_query_id !== false) {
            $ranges = array($uniref_id_fn($this->uniref_query_id));
        } else {
            $acc_count = 0;
            foreach ($this->query as $item) {
                $result = false;
                if (is_numeric($item))
                    $result = $cluster_num_fn($item);
                else if (strtolower(substr($item, 0, 7)) == "cluster")
                    $result = $cluster_id_fn($item);
                else
                    $result = $accession_fn($item);
                $acc_count++;
                if (is_array($result))
                    array_push($ranges, $result);
            }
        }
        return $ranges;
    }

    /*{
        $ranges = array();
        $query_fn = function($start_col, $end_col, $id_col, $item, $index_table, $db) {
            $cluster_id = strtolower($item);
            if (substr($cluster_id, 0, 7) == "cluster") {
                #TODO: find cluster_num
                $sql = "SELECT cluster_num FROM cluster_num_map WHERE cluster_id = '$cluster_id'";
                $query_result = $db->query($sql);
                if ($query_result) {
                    $result = $query_result->fetchArray(SQLITE3_ASSOC);
                    if ($result && isset($result["cluster_num"]))
                        $item = $result["cluster_num"];
                }
            }
            $cols = $start_col == $end_col ? $start_col : "$start_col, $end_col";
            $sql = "SELECT $cols FROM $index_table WHERE $id_col = '$item'";
            $query_result = $db->query($sql);
            if ($query_result) {
                $result = $query_result->fetchArray(SQLITE3_ASSOC);
                if ($result && isset($result[$start_col]))
                    return array($result[$start_col], $result[$end_col]);
            }
        };
        $index_table = $this->get_cluster_index_table(false);
        $id_lookup_table = $this->get_cluster_index_table(true);
        if ($this->use_uniref !== false && $this->uniref_query_id !== false) {
            $id = $this->db->escapeString($this->uniref_query_id);
            $result = $query_fn("start_index", "end_index", "uniref_id", $id, $index_table, $this->db);
            if (is_array($result))
                array_push($ranges, $result);
        } else {
            $acc_col = "accession"; //$this->use_uniref !== false ? "uniref_id" : "accession";
            $id_index_col = "cluster_index"; //$this->use_uniref !== false ? "uniref_index" : "cluster_index";
            foreach ($this->query as $item) {
                $result = false;
                if (is_numeric($item) || strtolower(substr($item, 0, 7)) == "cluster")
                    $result = $query_fn("start_index", "end_index", "cluster_num", $item, $index_table, $this->db);
                else
                    $result = $query_fn($id_index_col, $id_index_col, $acc_col, $item, $id_lookup_table, $this->db);
                if (is_array($result))
                    array_push($ranges, $result);
            }
        }
        return $ranges;
    }
     */


    private function expand_range($range) {
        $idx = array();
        for ($i = 0; $i < count($range); $i++) {
            $idx = array_merge($idx, range($range[$i][0], $range[$i][1]));
        }
        return $idx;
    }

    private function compute_set_scale_factor($idx, $scale_cap) {

        $min_bp = 999999999999;
        $max_bp = -999999999999;
        $max_query_width = -1;
    
        $TS = microtime(true); //TIME
        $db_query_time = 0; //TIME
        $db_fetch_time = 0; //TIME
        $db_num_queries = 0; //TIME
        $db_fetch = 0; //TIME
    
        for ($i = 0; $i < count($idx); $i++) {
            $cl_idx = $idx[$i];
            $attr_sql = "SELECT A.rel_start AS start, A.rel_stop AS stop, A.sort_key AS key, A.num AS num FROM attributes AS A WHERE A.cluster_index = $cl_idx";
            $DS = microtime(true); //TIME
            $query_result = $this->db->query($attr_sql);
            $db_query_time += microtime(true) - $DS; //TIME
            $db_num_queries++; //TIME
    
            $DS = microtime(true); //TIME
            $row = $query_result->fetchArray(SQLITE3_ASSOC);
            $db_fetch_time += microtime(true) - $DS; //TIME
            $db_fetch++; //TIME
            $key = "";
            if ($row) {
                gnd::coord_compare($row, $min_bp, $max_bp);
                $key = $row["key"];
                $query_width = $row["stop"] - $row["start"];
                if ($query_width > $max_query_width)
                    $max_query_width = $query_width;
            }
    
            if (!$key)
                continue;
    
            $nb_sql = "SELECT N.rel_start AS start, N.rel_stop AS stop, N.accession AS id FROM neighbors AS N WHERE N.gene_key = '$key'";
            if ($this->window !== NULL) {
                $num_clause = "num >= " . ($row["num"] - $this->window) . " AND num <= " . ($row["num"] + $this->window);
                $nb_sql .= " AND " . $num_clause;
            }
            $DS = microtime(true); //TIME
            $query_result = $this->db->query($nb_sql);
            $db_query_time += microtime(true) - $DS; //TIME
            $db_num_queries++; //TIME
            $DS = microtime(true); //TIME
            while ($row = $query_result->fetchArray(SQLITE3_ASSOC)) {
                $db_fetch_time += microtime(true) - $DS; //TIME
                $db_fetch++; //TIME
                gnd::coord_compare($row, $min_bp, $max_bp);
                $DS = microtime(true); //TIME
            }
        }
    
        $TT = microtime(true) - $TS; //TIME
        $time_data = "#Ids: " . count($idx) . ", #Queries: $db_num_queries, QueryTime: $db_query_time, #Fetch: $db_fetch, FetchTime: $db_fetch_time, Total: $TT"; //TIME

        //$min_bp = -12000;
        //$max_bp = 9200;
        list ($scale_factor, $legend_scale, $max_side, $max_width, $actual_max_width) = $this->compute_scale_factor($min_bp, $max_bp, $max_query_width, $scale_cap);
        return array($scale_factor, $legend_scale, $min_bp, $max_bp, $max_query_width, $actual_max_width, $time_data);
    }

}


?>
