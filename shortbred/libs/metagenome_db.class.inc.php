<?php

require_once("settings.class.inc.php");

class metagenome_db {

    private $mg_info = array();

    function __construct() {
    }

    public function load_db() {
        $file_list = settings::get_metagenome_db_list();
        $files = explode(",", $file_list);

        $this->mg_info = array();

        foreach ($files as $list_file) {
            $fh = fopen($list_file, "r") or next;

            while (!feof($fh)) {
                $line = fgets($fh);
                if (substr($line, 0, 1) == "#") {
                    continue;
                }

                $line = rtrim($line, "\r\n");
                $parts = preg_split("/[\t,]/", $line);

                $id = "";
                $name = "";
                $desc = "";
                $file_name = "";

                if (count($parts) > 0) {
                    $id = $parts[0];
                }
                if (count($parts) > 1) {
                    $name = $parts[1];
                }
                if (count($parts) > 2) {
                    $desc = $parts[2];
                }
                if (count($parts) > 3) {
                    $file_name = $parts[3];
                }

                if ($id) {
                    $this->mg_info[$id] = array('name' => $name, 'desc' => $desc, 'file_name' => $file_name);
                }
            }

            fclose($fh);
        }

        return true;
    }

    # Returns an associative array mapping metagenome ID to metagenome name.
    public function get_name_list() {
        $result = array();

        foreach ($this->mg_info as $id => $name) {
            # Only include ones that have files
            if (!$name['file_name'])
                continue;

            $result[$id] = $name['name'];
            if ($name['desc']) {
                $result[$id] = $result[$id] . " (" . $name['desc'] . ")";
            }
        }

        return $result;
    }

    public function get_metagenome_data($mg_name) {
        if (isset($this->mg_info[$mg_name])) {
            return $this->mg_info[$mg_name];
        } else {
            return array('name' => "", 'desc' => "", 'file_name' => "");
        }
    }
}

?>
