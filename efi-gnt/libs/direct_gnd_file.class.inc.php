<?php

require_once('const.class.inc.php');
require_once('arrow_api.class.inc.php');

class direct_gnd_file extends diagram_data_file {

    private $gnd_file = "";

    public function __construct($gnd_file = "") {
        $this->gnd_file = $gnd_file;
        parent::__construct(-1);
    }

    protected function get_diagram_file_path() {
        return $this->gnd_file;
    }
}

