<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\gnt\DiagramJob;
use \efi\gnt\functions;
use \efi\gnt\settings;


class diagram_data_file extends arrow_api {

    private $loaded;
    private $nb_size;
    private $cooccurrence;
    private $is_direct; // true if generated from a list of IDs or sequences, not a GNN
    private $job_type;
    private $blast_sequence;
    private $message = "";

    public function __construct($id, $is_example = false) {
        parent::__construct(NULL, $id, "diagram");
        if ($id) {
            $this->loaded = $this->load_data();
            if ($is_example)
                ; //TODO
        } else {
            $this->loaded = false;
        }
    }

    public static function create($db, $email, $tmp_filename, $filename) {
        $result = false;

        $uploadPrefix = settings::get_diagram_upload_prefix();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $key = functions::generate_key();
        $title = self::get_diagram_title_from_file($filename);

        $jobType = $ext == "zip" ? DiagramJob::UploadedZip : DiagramJob::Uploaded;

        $insert_array = array(
            'diagram_key' => $key,
            'diagram_email' => $email,
            'diagram_title' => $title,
            'diagram_type' => $jobType,
            'diagram_status' => __NEW__,
        );

        $result = $db->build_insert('diagram', $insert_array);
        \efi\job_shared::insert_new($db, "diagram", $result);

        if ($result) {
            functions::copy_to_uploads_dir($tmp_filename, $filename, $result, $uploadPrefix, $ext);
        } else {
            return false;
        }

        $info = array('id' => $result, 'key' => $key);
        return $info;
    }

    protected function get_diagram_file_path() {
        $dbFile = functions::get_diagram_file_path($this->id);
        return $dbFile;
    }
    private function load_data() {

        $dbFile = $this->get_diagram_file_path();
        $this->set_diagram_data_file($dbFile);

        if (!file_exists($dbFile)) {
            $this->message = "File " . $dbFile . " does not exist.";
            return false;
        }

        $db = new \SQLite3($dbFile);

        $gnnName = "";
        if (functions::sqlite_table_exists($db, "metadata")) {
            $sql = "SELECT * FROM metadata";
            $dbQuery = $db->query($sql);

            $row = $dbQuery->fetchArray();
            if (!$row)
            {
                $db->close();
                $this->message = "Unable to query metadata table.";
                return false;
            }

            if (array_key_exists("cooccurrence", $row))
                $this->cooccurrence = $row["cooccurrence"];
            else
                $this->cooccurrence = $row["coocurrence"]; //TODO: remove this in production; there was a typo earlier.
            
            if (array_key_exists("type", $row))
                $this->job_type = strtoupper($row["type"]);
            else
                $this->job_type = "";

            if (array_key_exists("sequence", $row))
                $this->blast_sequence = $row["sequence"];
            else
                $this->blast_sequence = "";
            
            $this->is_direct = $this->job_type == DiagramJob::BLAST || $this->job_type == DiagramJob::IdLookup || $this->job_type == DiagramJob::FastaLookup;

            $this->nb_size = $row["neighborhood_size"];
            $gnnName = $row["name"];
        } else {
            $this->cooccurrence = "";
            $this->nb_size = "";
            $this->is_direct = false;
            $this->job_type = "";
            $this->blast_sequence = "";
        }

        $db->close();

        $this->set_gnn_name($gnnName);
        $this->message = "";

        return true;
    }

    public function get_neighborhood_size() {
        return $this->nb_size;
    }

    public function get_cooccurrence() {
        return $this->cooccurrence;
    }

    public function is_job_type_blast() {
        return $this->job_type == DiagramJob::BLAST;
    }

    public function get_verbose_job_type() {
        return functions::get_verbose_job_type($this->job_type);
    }

    public function is_direct_job() {
        return $this->is_direct;
    }

    public function is_loaded() {
        return $this->loaded;
    }

    public function get_uniprot_ids() {
        $ids = array();

        $dbFile = $this->get_diagram_data_file();
        if (!file_exists($dbFile))
            return false;

        $db = new \SQLite3($dbFile);
        if (!functions::sqlite_table_exists($db, "matched")) {
            $rawIds = $this->get_ids_from_accessions($db);
            for ($i = 0; $i < count($rawIds); $i++)
                $ids[$rawIds[$i]] = $rawIds[$i];
        } else {
            $ids = $this->get_ids_from_match_table($db);
        }

        $db->close();

        return $ids;
    }

    private function get_ids_from_accessions($db) {
        $ids = array();

        $sql = "SELECT accession FROM attributes ORDER BY accession";
        $dbQuery = $db->query($sql);

        while ($row = $dbQuery->fetchArray()) {
            array_push($ids, $row["accession"]);
        }

        return $ids;
    }

    private function get_ids_from_match_table($db) {
        $ids = array();

        $sql = "SELECT * FROM matched ORDER BY uniprot_id";
        $dbQuery = $db->query($sql);

        while ($row = $dbQuery->fetchArray()) {
            $ids[$row["uniprot_id"]] = $row["id_list"];
        }

        return $ids;
    }

    public function get_unmatched_ids() {
        $ids = array();

        $dbFile = $this->get_diagram_data_file();
        if (!file_exists($dbFile))
            return false;

        $db = new \SQLite3($dbFile);
        if (!functions::sqlite_table_exists($db, "unmatched"))
            return $ids;

        $sql = "SELECT id_list FROM unmatched";
        $dbQuery = $db->query($sql);

        while ($row = $dbQuery->fetchArray()) {
            array_push($ids, $row["id_list"]);
        }

        $db->close();

        return $ids;
    }

    public function get_blast_sequence() {
        return $this->blast_sequence;
    }

    public function get_message() {
        return $this->message;
    }

    public function get_output_dir($id = 0) {}
    protected function update_results_object($data) {}

    public function process_error() {}
    public function process_start() {}
    public function process_finish() {}
}

