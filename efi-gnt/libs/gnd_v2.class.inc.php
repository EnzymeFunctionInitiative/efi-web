<?php

require_once(__DIR__ . "/gnd.class.inc.php");


class gnd_v2 extends gnd {

    
    private $has_stats = false;
    private $query = array(); // list of queried items
    private $range = array(); // range of cluster_index to retrieve


    function __construct($db, $params, $job_factory) {
        parent::__construct($db, $params, $job_factory);

        $this->parse_params($params);
    }


    protected function parse_params($params) {
        $this->page_size = 20;
        if (isset($params["pagesize"]) && is_numeric($params["pagesize"]))
            $this->page_size = $params["pagesize"];
        if (isset($params["sidx"]) && isset($params["eidx"]) && is_numeric($params["sidx"]) && is_numeric($params["eidx"]))
            $this->page_size = array($params["sidx"], $params["eidx"]);
        if (isset($params["stats"]) && isset($params["query"]))
            $this->has_stats = true;
        if (isset($params["query"]))
            $this->query = $this->parse_query($params["query"]);
        if (isset($params["range"]))
            $this->range = $this->parse_range($params["range"]);
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
        $this->open_db_file();
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
    
    protected function get_retrieved_ids() {
        return $this->expand_range($this->range);
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Methods for computing statistics on the query.
    //

    private function compute_stats() {
        $S = microtime(true); //TIME
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
            "num_checked" => count($idx), "index_range" => $index_range);

        return $stats;
    }


    // Get the start and ending ID indexes for the given cluster inputs
    private function get_cluster_indices() {
        $ranges = array();
        foreach ($this->query as $item) {
            if (is_numeric($item)) {
                $sql = "SELECT start_index, end_index FROM cluster_index WHERE cluster_num = $item";
                $query_result = $this->db->query($sql);
                if ($query_result) {
                    $result = $query_result->fetchArray(SQLITE3_ASSOC);
                    if ($result && isset($result["start_index"]))
                        array_push($ranges, array($result["start_index"], $result["end_index"]));
                }
            } else {
                $sql = "SELECT cluster_index FROM attributes WHERE accession = '$item'";
                $query_result = $this->db->query($sql);
                if ($query_result) {
                    $result = $query_result->fetchArray(SQLITE3_ASSOC);
                    if (isset($result["cluster_index"]))
                        array_push($ranges, array($result["cluster_index"], $result["cluster_index"]));
                }
            }
        }
        return $ranges;
    }


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
                $queryWidth = $row["stop"] - $row["start"];
                if ($queryWidth > $max_query_width)
                    $max_query_width = $queryWidth;
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
    
        list ($scale_factor, $legend_scale, $max_side, $max_width, $actual_max_width) = $this->compute_scale_factor($min_bp, $max_bp, $max_query_width, $scale_cap);
        return array($scale_factor, $legend_scale, $min_bp, $max_bp, $max_query_width, $actual_max_width, $time_data);
    }

}


?>
