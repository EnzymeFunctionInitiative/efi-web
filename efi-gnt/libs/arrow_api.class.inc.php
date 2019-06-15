<?php
require("job_shared.class.inc.php");


abstract class arrow_api extends job_shared {

    const DEFAULT_DIAGRAM_VERSION = 2;
    const DIAGRAM_VERSION_FILE = "diagram.version";


    private $db_file = "";
    private $gnn_name = "";
    protected $diagram_version = self::DEFAULT_DIAGRAM_VERSION;

    public function __construct($db, $id, $db_field_prefix) {
        parent::__construct($db, $id, $db_field_prefix);
    }
    
    public function get_diagram_version() { return $this->diagram_version; }

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

    public function get_max_neighborhood_size() {
        return 20; //TODO: grab this from the db file
    }

    public abstract function get_output_dir($id);
    protected abstract function update_results_object($data);
    protected function set_diagram_version() {
        $out_dir = $this->get_output_dir();
        $ver_file = "$out_dir/" . self::DIAGRAM_VERSION_FILE;
        if (file_exists($ver_file)) {
            $ver = trim(file_get_contents($ver_file));
            if (is_numeric($ver) && $ver >= self::DEFAULT_DIAGRAM_VERSION) {
                $data = array("diagram_version" => $ver);
                $result = $this->update_results_object($data);
                $this->diagram_version = $ver;
            }
        }
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

