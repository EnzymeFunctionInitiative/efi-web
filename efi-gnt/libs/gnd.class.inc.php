<?php

require_once(__DIR__ . "/settings.class.inc.php");
require_once(__DIR__ . "/functions.class.inc.php");


abstract class job_factory {
    public abstract function new_gnn($db, $id);
    public abstract function new_gnn_bigscape_job($db, $id);
    public abstract function new_uploaded_bigscape_job($db, $id);
    public abstract function new_diagram_data_file($id);
    public abstract function new_direct_gnd_file($file);
}


abstract class gnd {

    // Protected properties are commonly-used in derived classes.  Otherwise we encapsulate most other things.

    protected $db; // this refers to the currently-loaded database file (usually SQLite)
    protected $window = null;
    
    private $db_file = "";
    protected $use_uniref = false;
    private $is_direct_job = false;
    private $factory;
    private $message = "";
    private $scale_factor = null;
    private $job_name = "";
    private $filter_uniref_ver = 0;
    private $filter_uniref_id = "";
    private $use_cluster_id = false;

    public function get_error_message() { return $this->message; }
    public function parse_error() { return $this->message; }
    protected function append_error($msg) { $this->message .= ($this->message ? "; " : "") . $msg; }
    public function get_job_name() { return $this->job_name; }


    function __construct($db, $params, $factory) {
        $this->factory = $factory;

        $this->parse_job_id($db, $params);
        $this->parse_scale($params);
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Methods for parsing input parameters.
    //

    // Find the job ID and job key.
    private function parse_job_id($db, $params) {
        $message = "";
        $has_bigscape = false;
        if ((isset($params["gnn-id"])) && (is_numeric($params["gnn-id"]))) {
            $gnn_id = $params["gnn-id"];
            $gnn = $this->factory->new_gnn($db, $gnn_id);
            if ($gnn->get_key() != $params["key"]) {
                $message = "No GNN selected.";
            } elseif ($gnn->is_expired()) {
                $message = "GNN results are expired.";
            }

            if (!$message) {
                if (settings::get_bigscape_enabled() && isset($params['bigscape']) && $params['bigscape']=="1") {
                    $bss = $this->factory->new_gnn_bigscape_job($db, $gnn_id);
                    $has_bigscape = $bss->is_finished();
                }
                $this->db_file = $gnn->get_diagram_data_file($has_bigscape);
                if (!file_exists($this->db_file))
                    $this->db_file = $gnn->get_diagram_data_file_legacy();
                $this->get_uniref_db_files($gnn);
                $this->job_name = $gnn->get_gnn_name();
            }
        } else if (isset($params['upload-id']) && functions::is_diagram_upload_id_valid($params['upload-id'])) {
            $gnn_id = $params['upload-id'];
            $arrows = $this->factory->new_diagram_data_file($gnn_id);
            if (settings::get_bigscape_enabled() && isset($params['bigscape']) && $params['bigscape']=="1") {
                $bss = $this->factory->new_uploaded_bigscape_job($db, $gnn_id);
                $has_bigscape = $bss->is_finished();
            }
            $this->db_file = $arrows->get_diagram_data_file($has_bigscape);
            $this->get_uniref_db_files($arrows);
            $this->job_name = $arrows->get_gnn_name();
        } else if ((isset($params['direct-id']) && functions::is_diagram_upload_id_valid($params['direct-id'])) || (isset($_GET['rs-id']) && isset($_GET['rs-ver']))) {
            if (isset($_GET['rs-id'])) {
                $rs_ver = $_GET['rs-ver'];
                $gnd_file = functions::validate_direct_gnd_file($_GET['rs-id'], $rs_ver, $params["key"]);
                if ($gnd_file === false)
                    $validated = true;
                else
                    $arrows = $this->factory->new_direct_gnd_file($gnd_file);
                //TODO:
                if (isset($_GET['uniref50-id']))
                    $this->set_uniref_filter(50, $_GET['uniref50-id']);
                if (isset($_GET['uniref90-id']))
                    $this->set_uniref_filter(90, $_GET['uniref90-id']);
            } else {
                $gnn_id = $params['direct-id'];
                $arrows = $this->factory->new_diagram_data_file($gnn_id);
                if (settings::get_bigscape_enabled() && isset($params['bigscape']) && $params['bigscape']=="1") {
                    $bss = $this->factory->new_uploaded_bigscape_job($db, $gnn_id);
                    $has_bigscape = $bss->is_finished();
                }
            }
            $this->db_file = $arrows->get_diagram_data_file($has_bigscape);
            $this->get_uniref_db_files($arrows);
            $this->job_name = $arrows->get_gnn_name();
            $this->is_direct_job = true;
        } else if (isset($params['console-run-file']) && !isset($_SERVER["HTTP_HOST"])) {
            $this->db_file = $params['console-run-file'];
            $this->job_name = "console";
        } else {
            $message = "No GNN selected.";
        }

        $this->message = $message;
        return $message;
    }
    private function get_uniref_db_files($gnn) {
        $this->uniref50_file = $gnn->get_diagram_data_file(false, "uniref50");
        if (!file_exists($this->uniref50_file))
            $this->uniref50_file = "";
        $this->uniref90_file = $gnn->get_diagram_data_file(false, "uniref90");
        if (!file_exists($this->uniref90_file))
            $this->uniref90_file = "";
    }


    private function parse_scale($params) {
        $this->window = null;
        if (isset($params["window"]) && is_numeric($params["window"])) {
            $this->window = intval($params["window"]);
        }
        $this->scale_factor = null;
        if (isset($params["scale-factor"]) && is_numeric($params["scale-factor"])) {
            $this->scale_factor = floatval($params["scale-factor"]);
            if ($this->scale_factor < 0.00000001 || $this->scale_factor > 1000000.0)
                $this->scale_factor = null;
        }
    }


    protected function parse_query($query) {
        $query = strtoupper($query);
        $items = preg_split("/[\n\r ,]+/", $query);
        return $items;
    }

    
    protected abstract function parse_params($params);


    public function check_for_stats() { return false; }
    public function get_stats() { return array(); }


    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Methods for creating output/return data structure.
    //

    protected function create_output() {
        $output = array();
        $output["message"] = "";
        $output["error"] = false;
        $output["eod"] = false;
        return $output;
    }

    
    public function create_error_output($message) {
        $output = $this->create_output();
        $output["error"] = true;
        $output["message"] = $message;
        return $output;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Methods for accessing/computing neighbor data.
    //

    public function get_arrow_data() {
        $this->open_db_file();

        $output = $this->create_output();
        
        $S = microtime(true); //TIME
        $data = $this->retrieve_and_process();
        $T = microtime(true) - $S; //TIME
        
        $this->db->close();

        $output["scale_factor"] = $data["scale_factor"];
        $output["time"] = $data["time"] . " Total=$T"; //TIME
        $output["totaltime"] = $T; //TIME
        $output["eod"] = $data["eod"];
        $output["counts"] = $data["counts"];
        $output["data"] = $data["data"];
        $output["min_bp"] = $data["min_bp"];
        $output["max_bp"] = $data["max_bp"];
        $output["min_pct"] = $data["min_pct"];
        $output["max_pct"] = $data["max_pct"];
        $output["legend_scale"] = $data["legend_scale"]; // the base unit for the legend. the client can draw however many units they want for the legend.

        return $output;
    }

    // Returns the column to use for retrieving the IDs from the attributes database table.
    protected abstract function get_select_id_col_name();
    protected abstract function get_retrieved_ids();

    private function retrieve_and_process() {
        // datbase file is alread open

        $output["data"] = array();

        $id_col = $this->get_select_id_col_name();

        $S = microtime(true); //TIME
        $ids = $this->get_retrieved_ids();
        $parse_time = microtime(true) - $S; //TIME
        
        $output["counts"] = array("max" => count($ids), "invalid" => array(), "displayed" => 0); //TODO
    
        $min_bp = 999999999999;
        $max_bp = -999999999999;
        $max_query_width = -1;
        $max_nb_width = -1;
    
        $query_time = 0; //TIME
        $query_count = 0; //TIME
        $nb_time = 0; //TIME
        $nb_count = 0; //TIME
        $proc_time = 0; //TIME
        
        $idCount = 0;
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
    
            $attr_sql = "SELECT * FROM attributes WHERE $id_col = '$id'";
            $S = microtime(true); //TIME
            $query_result = $this->db->query($attr_sql);
            $query_time += microtime(true) - $S; //TIME
            $query_count++; //TIME
            $row = $query_result->fetchArray(SQLITE3_ASSOC);
            if (!$row)
                continue;
    
            $S = microtime(true); //TIME
            $attr = $this->get_query_attributes($row);
            self::update_rel_coords($attr, $min_bp, $max_bp, $max_query_width);
            $proc_time += microtime(true) - $S; //TIME
    
            $nb_sql = $this->get_neighbor_select_sql($attr, $row);
            $query_result = $this->db->query($nb_sql);
    
            $S = microtime(true); //TIME
            $neighbors = array();
            while ($row = $query_result->fetchArray()) {
                $S = microtime(true); //TIME
                $nb = $this->get_neighbor_attributes($row);
                $proc_time += microtime(true) - $S; //TIME
                self::update_rel_coords($nb, $min_bp, $max_bp, $max_nb_width);
                array_push($neighbors, $nb);
            }
            $nb_time += microtime(true) - $S; //TIME
            $nb_count++; //TIME
    
            array_push($output["data"], array( 'attributes' => $attr, 'neighbors' => $neighbors));
        }
    
        $this->db->close();
    
        $output["time"] = "#Q=$query_count TQ=$query_time #N=$nb_count TN=$nb_time PROC=$proc_time PARSE=$parse_time";
        $output["eod"] = count($ids) == 0;
    
        $output = $this->compute_rel_coords($output, $min_bp, $max_bp, $max_query_width);
        
        return $output;
    }


    private function set_uniref_filter($uniref_version, $id) {
        $this->filter_uniref_ver = $uniref_version;
        $this->filter_uniref_id = $id;
    }


    private function get_neighbor_select_sql($attr, $row) {
        $nb_sql = "SELECT * FROM neighbors WHERE gene_key = '" . $row['sort_key'] . "'";
        if ($this->window !== NULL) {
            //TODO: handle circular case
            $num_clause = "num >= " . ($attr['num'] - $this->window) . " AND num <= " . ($attr['num'] + $this->window);
            $nb_sql .= " AND " . $num_clause;
        }
        $nb_sql .= " ORDER BY num";
        return $nb_sql;
    }


    private function get_neighbor_attributes($row) {
        $nb = array();
        $nb['accession'] = $row['accession'];
        $nb['id'] = $row['id'];
        $nb['num'] = $row['num'];
        $nb['family'] = explode("-", $row['family']);
        if (isset($row['ipro_family']))
            $nb['ipro_family'] = explode("-", $row['ipro_family']);
        else
            $nb['ipro_family'] = array();
        $nb['start'] = $row['start'];
        $nb['stop'] = $row['stop'];
        $nb['rel_start_coord'] = $row['rel_start'];
        $nb['rel_stop_coord'] = $row['rel_stop'];
        $nb['direction'] = $row['direction'];
        $nb['type'] = $row['type'];
        $nb['seq_len'] = $row['seq_len'];
        $nb['anno_status'] = $row['anno_status'];
        $nb['desc'] = $row['desc'];
    
        $familyCount = count($nb['family']);
    
        $familyDesc = explode(";", $row['family_desc']);
        if (count($familyDesc) == 1)
            $familyDesc = explode("-", $row['family_desc']);
        $nb['family_desc'] = $familyDesc;
        if (count($nb['family_desc']) < $familyCount) {
            if (count($nb['family_desc']) > 0)
                $nb['family_desc'] = array_fill(0, $familyCount, $nb['family_desc'][0]);
            else
                $nb['family_desc'] = array_fill(0, $familyCount, "none");
        }
        
        $iproFamilyCount = count($nb['ipro_family']);
        $iproFamilyDesc = isset($row['ipro_family_desc']) ? explode(";", $row['ipro_family_desc']) : array();
        if (count($iproFamilyDesc) == 1)
            $iproFamilyDesc = explode("-", $row['ipro_family_desc']);
        $nb['ipro_family_desc'] = $iproFamilyDesc;
        if (count($nb['ipro_family_desc']) < $iproFamilyCount) {
            if (count($nb['ipro_family_desc']) > 0)
                $nb['ipro_family_desc'] = array_fill(0, $iproFamilyCount, $nb['ipro_family_desc'][0]);
            else
                $nb['ipro_family_desc'] = array_fill(0, $iproFamilyCount, "none");
        }
        
        if (array_key_exists("color", $row))
            $nb['color'] = explode(",", $row['color']);
        if (count($nb['color']) < $familyCount) {
            if (count($nb['color']) > 0)
                $nb['color'] = array_fill(0, count($nb['family']), $nb['color'][0]);
            else
                $nb['color'] = array_fill(0, count($nb['family']), "grey");
        }
        
        $nb['pfam'] = $nb['family']; // will migrate to this eventually
        $nb['interpro'] = $nb['ipro_family'];
        $nb['pfam_desc'] = $nb['family_desc'];
        $nb['interpro_desc'] = $nb['ipro_family_desc'];
        
        return $nb;
    }


    private function get_query_attributes($row) {
        $attr = array();
        $attr['accession'] = $row['accession'];
        $attr['id'] = $row['id'];
        $attr['num'] = $row['num'];
        $attr['family'] = explode("-", $row['family']);
        if (isset($row['ipro_family']))
            $attr['ipro_family'] = explode("-", $row['ipro_family']);
        else
            $attr['ipro_family'] = array();
        $attr['start'] = $row['start'];
        $attr['stop'] = $row['stop'];
        $attr['rel_start_coord'] = $row['rel_start'];
        $attr['rel_stop_coord'] = $row['rel_stop'];
        $attr['strain'] = $row['strain'];
        $attr['direction'] = $row['direction'];
        $attr['type'] = $row['type'];
        $attr['seq_len'] = $row['seq_len'];
        $attr['organism'] = rtrim($row['organism']);
        $attr['taxon_id'] = $row['taxon_id'];
        $attr['anno_status'] = $row['anno_status'];
        $attr['desc'] = $row['desc'];
        if (array_key_exists('evalue', $row) && $row['evalue'] !== NULL)
            $attr['evalue'] = $row['evalue'];
        elseif (! $this->is_direct_job && array_key_exists('cluster_num', $row))
            $attr['cluster_num'] = $row['cluster_num'];
    
        if (isset($row['uniref50_size']))
            $attr['uniref50_size'] = $row['uniref50_size'];
        if (isset($row['uniref90_size']))
            $attr['uniref90_size'] = $row['uniref90_size'];
    
        if (count($attr['family']) > 0 && $attr['family'][0] == "")
            $attr['family'][0] = "none-query";
        if (count($attr['ipro_family']) > 0 && $attr['ipro_family'][0] == "")
            $attr['ipro_family'][0] = "none-query";
        $familyCount = count($attr['family']);
    
        $familyDesc = explode(";", $row['family_desc']);
        if (count($familyDesc) == 1) {
            $familyDesc = explode("-", $row['family_desc']);
            if ($familyDesc[0] == "")
                $familyDesc[0] = "Query without family";
        }
        $attr['family_desc'] = $familyDesc;
        if (count($attr['family_desc']) < $familyCount) {
            if (count($attr['family_desc']) > 0)
                $attr['family_desc'] = array_fill(0, $familyCount, $attr['family_desc'][0]);
            else
                $attr['family_desc'] = array_fill(0, $familyCount, "none");
        }
    
        $iproFamilyCount = isset($attr['ipro_family']) ? count($attr['ipro_family']) : 0;
        $iproFamilyDesc = isset($row['ipro_family_desc']) ? explode(";", $row['ipro_family_desc']) : array();
        if (count($iproFamilyDesc) == 1) {
            $iproFamilyDesc = explode("-", $row['ipro_family_desc']);
            if ($iproFamilyDesc[0] == "")
                $iproFamilyDesc[0] = "Query without family";
        }
        $attr['ipro_family_desc'] = $iproFamilyDesc;
        if (count($attr['ipro_family_desc']) < $iproFamilyCount) {
            if (count($attr['ipro_family_desc']) > 0)
                $attr['ipro_family_desc'] = array_fill(0, $iproFamilyCount, $attr['ipro_family_desc'][0]);
            else
                $attr['ipro_family_desc'] = array_fill(0, $iproFamilyCount, "none");
        }
        
        $attr['pfam'] = $attr['family']; // will migrate to this eventually
        $attr['interpro'] = $attr['ipro_family'];
        $attr['pfam_desc'] = $attr['family_desc'];
        $attr['interpro_desc'] = $attr['ipro_family_desc'];
        
        if (array_key_exists("color", $row))
            $attr['color'] = explode(",", $row['color']);
        if (count($attr['color']) < $familyCount) {
            if (count($attr['color']) > 0)
                $attr['color'] = array_fill(0, $familyCount, $attr['color'][0]);
            else
                $attr['color'] = array_fill(0, $familyCount, "grey");
        }
    
        if (array_key_exists("sort_order", $row))
            $attr['sort_order'] = $row['sort_order'];
        else
            $attr['sort_order'] = -1;
        
        if (array_key_exists("is_bound", $row))
            $attr['is_bound'] = $row['is_bound'];
        else
            $attr['is_bound'] = 0;
    
        $attr['pid'] = -1;
    
        if (strlen($attr['organism']) > 0 && substr_compare($attr['organism'], ".", -1) === 0)
            $attr['organism'] = substr($attr['organism'], 0, strlen($attr['organism'])-1);

        return $attr;
    }




    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Helper methods.
    //
    protected function open_db_file() {
        $db_file = $this->db_file;
        $this->db = new SQLite3($db_file);
    }
    protected function set_uniref_version($ver) {
        if ($ver == "50" || $ver == "90")
            $this->use_uniref = $ver;
        else
            $this->use_uniref = false;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////
    // Coordinate methods.
    //

    protected static function coord_compare($row, &$min_bp, &$max_bp) {
        if ($row["start"] < $min_bp)
            $min_bp = $row["start"];
        if ($row["stop"] > $max_bp)
            $max_bp = $row["stop"];
    }

    private static function update_rel_coords($attr, &$min_bp, &$max_bp, &$max_query_width) {
        if ($attr['rel_start_coord'] < $min_bp)
            $min_bp = $attr['rel_start_coord'];
        if ($attr['rel_stop_coord'] > $max_bp)
            $max_bp = $attr['rel_stop_coord'];
        $query_width = $attr['rel_stop_coord'] - $attr['rel_start_coord'];
        if ($query_width > $max_query_width)
            $max_query_width = $query_width;
        return $query_width;
    }
    
    protected function compute_scale_factor($min_bp, $max_bp, $max_query_width, $width_cap = 0) {
    
        $max_side = (abs($max_bp) > abs($min_bp)) ? abs($max_bp) : abs($min_bp);
        $max_width = $max_side * 2 + $max_query_width;
        $actual_max_width = $max_width;
        if ($width_cap > 0 && $max_width > $width_cap)
            $max_width = $width_cap;
        if ($max_width < 0.000001)
            $max_width = 1;
        $scale_factor = 300000 / $max_width;
    
        $legend_scale = $max_bp - $min_bp;
    
        return array($scale_factor, $legend_scale, $max_side, $max_width, $actual_max_width);
    }

    // Computes coordinates relative to the query (middle) sequence using the entire set of retrieved diagrams.
    private function compute_rel_coords($output, $min_bp, $max_bp, $max_query_width) {

        if ($this->scale_factor !== NULL) {
            $max_width = 300000 / $this->scale_factor; // scale factor is between 1 and 100 (specifying the scale factor as a percentage of the screen width = 1000AA) the data points in the file are given in bp so we x3 to get the factor in bp
            $max_query_width = 0;
            $max_side = $max_width / 2;
            $legend_scale = $max_width;
        } else {
            list($this->scale_factor, $legend_scale, $max_side, $max_width) = $this->compute_scale_factor($min_bp, $max_bp, $max_query_width);
        }

        $min_bp = -$max_side;
        $max_bp = $max_side + $max_query_width;
    
        $min_pct = 2;
        $max_pct = -2;
        for ($i = 0; $i < count($output["data"]); $i++) {
            $start = $output["data"][$i]["attributes"]["rel_start_coord"];
            $stop = $output["data"][$i]["attributes"]["rel_stop_coord"];
            $ac_start = 0.5;
            $ac_width = ($stop - $start) / $max_width;
            $offset = 0.5 - ($start - $min_bp) / $max_width;
            $output["data"][$i]["attributes"]["rel_start"] = $ac_start;
            $output["data"][$i]["attributes"]["rel_width"] = $ac_width;
            $acEnd = $ac_start + $ac_width;
            if ($acEnd > $max_pct)
                $max_pct = $acEnd;
            if ($ac_start < $min_pct)
                $min_pct = $ac_start;
    
            foreach ($output["data"][$i]["neighbors"] as $idx => $data2) {
                $nb_start_bp = $output["data"][$i]["neighbors"][$idx]["rel_start_coord"];
                $nb_width_bp = $output["data"][$i]["neighbors"][$idx]["rel_stop_coord"] - $output["data"][$i]["neighbors"][$idx]["rel_start_coord"];
                $nb_start = ($nb_start_bp - $min_bp) / $max_width;
                $nb_width = $nb_width_bp / $max_width;
                $nb_start += $offset;
                $nb_end = $nb_start + $nb_width;
                $output["data"][$i]["neighbors"][$idx]["rel_start"] = $nb_start;
                $output["data"][$i]["neighbors"][$idx]["rel_width"] = $nb_width;
                if ($nb_end > $max_pct)
                    $max_pct = $nb_end;
                if ($nb_start < $min_pct)
                    $min_pct = $nb_start;
            }
        }
    
        $output["legend_scale"] = $legend_scale;
        $output["min_pct"] = $min_pct;
        $output["max_pct"] = $max_pct;
        $output["min_bp"] = $min_bp;
        $output["max_bp"] = $max_bp;
        $output["scale_factor"] = $this->scale_factor;
    
        return $output;
    }


}

?>
