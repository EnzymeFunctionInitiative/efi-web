<?php


abstract class arrow_api {

    private $db_file = "";
    private $gnn_name = "";

    public function __construct() {
    }

    protected function set_diagram_data_file($dbFile) {
        $this->db_file = $dbFile;
    }

    protected function set_gnn_name($gnnName) {
        $this->gnn_name = $gnnName;
    }

    public function get_bigscape_cluster_file() {
        $file = $this->db_file . ".bigscape-clusters";
        if (file_exists($file))
            return $file;
        else
            return FALSE;
    }

    public function get_diagram_data_file($useBigscape = false) {
        $file = $this->db_file . ($useBigscape ? ".bigscape" : "");
        return $file;
    }

    public function get_gnn_name() {
        return $this->gnn_name;
    }

    protected static function get_diagram_title_from_file($file) {
        $file = preg_replace("/\.sqlite$/", "", $file);
        $file = preg_replace("/_arrow_data/", "", $file);
        return $file;
        /*
        try {
            $db = new SQLite3($file);

            $sql = "SELECT * FROM metadata";
            $dbQuery = $db->query($sql);

            $row = $dbQuery->fetchArray();
            if (!$row)
            {
                $db->close();
                return "";
            }
            $gnn_name = $row['name'];

            $db->close();

            return $gnn_name;
        } catch (Exception $e) {
            return "";
        }
         */
    }
}


?>

