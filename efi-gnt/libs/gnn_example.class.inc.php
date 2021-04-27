<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__ . "/includes/main.inc.php");
require_once(__DIR__ . "/gnn_shared.class.inc.php");
require_once(__BASE_DIR__ . "/training/libs/example_config.class.inc.php");
require_once(__BASE_DIR__."/vendor/autoload.php");

require_once("Mail.php");
require_once("Mail/mime.php");


class gnn_example extends gnn {

    public function __construct($db, $id) {
        parent::__construct($db, $id, false, false);

        $config_file = example_config::get_config_file();
        $config = example_config::get_config($config_file);
        $table = example_config::get_gnt_table($config);
        $this->data_dir = example_config::get_gnt_data_dir($config);

        $this->load_gnn($id, $table);
    }
    
    public function get_rel_output_dir() {
        return settings::get_rel_example_output_dir();
    }

    protected function show_summary_details() {
        return false;
    }
    
    public function get_source_info() {
        return false;
    }

    public function get_output_dir($id = 0) {
        if (!$id)
            $id = $this->get_id();

        return $this->data_dir . "/" . $id;
    }
}
?>
