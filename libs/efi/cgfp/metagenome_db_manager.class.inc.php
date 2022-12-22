<?php
namespace efi\cgfp;

require_once(__DIR__."/../../../init.php");

use \efi\cgfp\settings;

// A metagenome db is a collection of metagenome reads for a specifc dataset (e.g. HMP, HMP2).

class metagenome_db_manager {

    private $mg_info = array();
    private $mg_dbs = array();

    function __construct() {
        $dbs = self::get_valid_dbs();

        foreach ($dbs as $db_id => $db_file) {
            $mg_meta = self::get_metagenome_db_metadata($db_file);
            $mg_info = self::load_db($db_file);

            //$db_info = array("file" => $db_file, "sites" => $mg_meta["sites"], "mgs" => $mg_info, "id" => $i, "name" => $mg_meta["db_name"]);
            $db_info = new metagenome_db();
            $db_info->file = $db_file;
            $db_info->site_info = $mg_meta["sites"];
            $db_info->metagenomes = $mg_info;
            $db_info->id = $db_id;
            $db_info->name = $mg_meta["db_name"];
            $db_info->description = $mg_meta["db_desc"];

            array_push($this->mg_dbs, $db_info);
        }
    }

    // Call this with care.
    public static function get_valid_dbs() {
        $db_list = settings::get_metagenome_db_list();
        $db_files = explode(",", $db_list);

        $dbs = array();
        for ($i = 0; $i < count($db_files); $i++) {
            $dbs[$i] = $db_files[$i];
        }

        return $dbs;
    }

    public function get_metagenome_db_ids() {
        $mg_db_list = array();
        foreach ($this->mg_dbs as $db_id => $db_info) {
            array_push($mg_db_list, $db_id);
        }
        return $mg_db_list;
    }

    public function get_metagenome_db_name($db_id) {
        if (!self::is_valid_id($db_id) || isset($this->mg_dbs[$db_id]))
            return $this->mg_dbs[$db_id]->name;
        else
            return "";
    }

    public function get_metagenome_db_description($db_id) {
        if (!self::is_valid_id($db_id) || isset($this->mg_dbs[$db_id]))
            return $this->mg_dbs[$db_id]->description;
        else
            return "";
    }

    public function get_metagenome_list_for_db($db_id) {
        if (!self::is_valid_id($db_id) || !isset($this->mg_dbs[$db_id]))
            return array();

        return $this->get_name_list($db_id);
    }

    private static function is_valid_id($id) {
        return is_numeric($id);
    }

    # Returns an associative array mapping metagenome ID to metagenome name for a specific metagenome database.
    private function get_name_list($db_id) {
        if (!self::is_valid_id($db_id) || !isset($this->mg_dbs[$db_id]))
            return array();

        $result = array();

        foreach ($this->mg_dbs[$db_id]->metagenomes as $id => $mg_info) {
            # Only include ones that have files
            if (!$mg_info['file_name'])
                continue;

            $result[$id] = $mg_info['name'];
            if ($mg_info['desc']) {
                $result[$id] = $result[$id] . " (" . $mg_info['desc'] . ")";
            }
        }

        asort($result);

        return $result;
    }

    private static function get_metagenome_db_metadata($mg_db) {
    
        $info = array("sites" => array(), "db_name" => ""); # map site to color
    
        $scheme_file = "$mg_db.metadata";
        if (file_exists($scheme_file)) {
            $fh = fopen($scheme_file, "r");
            if ($fh === false)
                return false;
        } else {
            return false;
        }

        $version = 1;
        $cats = array();

        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                continue; // skip comments

            if ($data[0] == "DB_NAME") {
                $info["db_name"] = $data[1];
            } elseif ($data[0] == "VERSION") {
                $version = $data[1];
            } elseif ($data[0] == "CATEGORIES") {
                $cats = explode(",", $data[1]);
            } else {
                $site = str_replace("_", " ", $data[0]);
                $color = $data[1];
                $order = isset($data[2]) ? $data[2] : 0;
                if (strpos($order, ",") !== false)
                    $order = explode(",", $order);
                $info["sites"][$site] = array('color' => $color, 'order' => $order);
            }
        }
    
        fclose($fh);
    
        $desc = "";
        $desc_file = "$mg_db.description";
        if (file_exists($desc_file)) {
            $dfh = fopen($desc_file, "r");
            while (!feof($dfh)) {
                $line = fgets($dfh, 1000);
                $desc .= $line;
            }
            fclose($dfh);
        }
        $info["db_desc"] = $desc;
        $info["version"] = $version;
        $info["categories"] = $cats !== false ? $cats : array();

        return $info;
    }

    // Load specific metagenome database
    public static function get_metagenome_db_site_info($db_id, $bodysites = array(), $group_cat = 0) { // Array to filter for specific body sites
        $mg_dbs = self::get_valid_dbs();
        if (!self::is_valid_id($db_id) || !isset($mg_dbs[$db_id]))
            return false;

        $mg_db = $mg_dbs[$db_id];
        $meta = self::get_metagenome_db_metadata($mg_db);
        $db_info = self::load_db($mg_db);

        $info = array("site" => array(), "secondary" => array(), "categories" => $meta["categories"]);

        foreach ($db_info as $mg_id => $data) {
            $site = $data["name"];
            if ($meta["version"] > 1)
                $site .= "; " . $data["desc"];
            if (count($bodysites) == 0 || in_array($site, $bodysites)) {
                $info["site"][$mg_id] = $site;
                $info["secondary"][$mg_id] = $data["desc"];
                if (isset($meta["sites"][$site])) {
                    $info["color"][$mg_id] = $meta["sites"][$site]["color"];
                    if (is_array($meta["sites"][$site]["order"]) && isset($meta["sites"][$site]["order"][$group_cat]))
                        $info["order"][$mg_id] = $meta["sites"][$site]["order"][$group_cat];
                    else
                        $info["order"][$mg_id] = is_array($meta["sites"][$site]["order"]) ?
                                                        $meta["sites"][$site]["order"][0] :
                                                        $meta["sites"][$site]["order"];
                } else {
                    $info["color"][$mg_id] = "";
                    $info["order"][$mg_id] = "";
                }
            }
        }

        return $info;
    }

    private static function load_db($mg_db) {
        $fh = fopen($mg_db, "r");
        if ($fh === false)
            return array();

        $info = array();

        while (($data = fgetcsv($fh, 1000, "\t")) !== false) {
            if (isset($data[0]) && $data[0] && $data[0][0] == "#")
                continue; // skip comments

            $mg_id = "";
            $name = "";
            $desc = "";
            $file_name = "";

            if (count($data) > 0)
                $mg_id = $data[0];
            if (count($data) > 1)
                $name = $data[1];
            if (count($data) > 2)
                $desc = $data[2];
            if (count($data) > 3)
                $file_name = $data[3];

            $pos = strpos($name, " - ");
            if ($pos !== false)
                $name = substr($name, $pos+3);
            $name = str_replace("_", " ", trim($name));

            if ($mg_id)
                $info[$mg_id] = array('name' => $name, 'desc' => $desc, 'file_name' => $file_name);
        }

        fclose($fh);

        return $info;
    }
    
}

