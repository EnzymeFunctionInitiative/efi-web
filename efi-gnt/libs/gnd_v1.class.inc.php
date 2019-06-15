<?php

require_once(__DIR__ . "/gnd.class.inc.php");


class gnd_v1 extends gnd {


    private $page_size;
    private $page;
    private $query = array(); // list of queried items


    function __construct($db, $params, $job_factory) {
        parent::__construct($db, $params, $job_factory);
    }


    protected function parse_params($params) {
        $this->page_size = 20;
        if (isset($params["pagesize"]) && is_numeric($params["pagesize"]))
            $this->page_size = $params["pagesize"];
        
        if (isset($params["page"]) && is_numeric($params["page"]))
            $this->page = $params["page"];
        
        if (isset($params["query"]))
            $this->query = $this->parse_query($params["query"]);
        else
            $this->append_error("No query is selected.");
    }


    // Returns the column to use for retrieving the IDs from the attributes database table.
    protected function get_select_id_col_name() {
        return "accession";
    }


    private function parse_ids($items) {
        $ids = array();

        foreach ($items as $item) {
            if (is_numeric($item)) {
                $cluster_ids = $this->get_ids_from_database($item);
                $ids = array_merge($ids, $cluster_ids);
            } elseif ($item && $this->id_exists($item, $this->db)) {
                array_push($ids, $item);
            }
        }
        return $ids;
    }


    // Implementation of an abstract method.  Returns a list of IDs that are to be retrieved
    // from the database and returned to the user.
    protected function get_retrieved_ids() {
        $ids = $this->parse_ids($items);
    
        $sidx = $eidx = 0;
        if (is_array($this->page_size)) {
            $sidx = $this->page_size[0];
            $eidx = $this->page_size[1];
            if ($sidx < 0)
                $sidx = 0;
            if ($eidx < $sidx)
                $eidx = $sidx;
            if ($eidx >= count($ids))
                $eidx = count($ids) - 1;
    
            $len = $eidx - $sidx + 1;
            if ($sidx >= count($ids))
                $ids = array();
            else
                $ids = array_slice($ids, $sidx, $len);
        }

        list($start_count, $max_count) = $this->get_page_limit($this->page_size);
        $num_to_display = $max_count - $start_count + 1;
        $ids = array_slice($ids, $start_count, $num_to_display);

        // This is supposed to come at the end of the retrieval, but I don't know where to put it yet.
        // This is legacy code anyway and is going byebye soon.
        //$output["eod"] = $start_count < $max_count;
        //$output["counts"]["displayed"] = $start_count;
        //if (!$output["eod"])
        //    $output["counts"]["displayed"]--;
        return $ids;
    }

    
    private function get_page_limit($params) {
        $start_count = 0;
        $max_count = 100000000;

        $dash_pos = strpos($this->page, "-");
        if ($dash_pos !== FALSE) {
            $start_page = substr($this->page, 0, $dash_pos);
            $end_page = substr($this->page, $dash_pos + 1);
            $start_count = $start_page * $this->page_size;
            $max_count = $end_page * $this->page_size + $this->page_size;
        } else {
            $page = intval($this->page);
            if ($page >= 0 && $page <= 10000) { // error check to limit to 10000 pages 
                $start_count = $page * $this->page_size;
                $max_count = $start_count + $this->page_size;
            }
        }

        return array($start_count, $max_count);
    }
    
    private function id_exists($id) {
        $sql = "SELECT accession FROM attributes WHERE accession = '$id' AND accession IS NOT NULL";
        $query_result = $this->db->query($sql);
        return $query_result ? true : false;
    }

    private function get_ids_from_database($cluster_id) {
        if (!is_numeric($cluster_id))
            return array();
    
        $sql = "SELECT accession FROM attributes WHERE cluster_num = $cluster_id AND accession IS NOT NULL ORDER BY sort_order";
        $query_result = $this->db->query($sql);
    
        $ids = array();
        if (!$query_result)
            return $ids;
    
        $ids = array();
        while ($row = $query_result->fetchArray()) {
            array_push($ids, $row['accession']);
        }
        
        return $ids;
    }
}

?>
