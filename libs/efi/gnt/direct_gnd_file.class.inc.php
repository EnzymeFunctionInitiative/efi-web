<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");


class direct_gnd_file extends diagram_data_file {

    private $gnd_file = "";

    public function __construct($db, $gnd_file = "") {
        $this->gnd_file = $gnd_file;
        parent::__construct($db, false, false);
    }

    protected function get_diagram_file_path() {
        return $this->gnd_file;
    }
}

