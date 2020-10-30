<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__GNT_DIR__."/libs/gnn.class.inc.php");
require_once(__GNT_DIR__."/libs/bigscape_job.class.inc.php");
require_once(__GNT_DIR__."/libs/gnd_v2.class.inc.php");


// This is necessary so that the gnd class environment doesn't get clusttered
// with the dependencies that gnn, etc. need.
class gnd_job_factory extends job_factory {
    function __construct($is_example) { $this->is_example = $is_example; }
    public function new_gnn($db, $id) { return $this->is_example ? new gnn_example($db, $id) : new gnn($db, $id); }
    public function new_gnn_bigscape_job($db, $id) { return new bigscape_job($db, $id, DiagramJob::GNN); }
    public function new_uploaded_bigscape_job($db, $id) { return new bigscape_job($db, $id, DiagramJob::Uploaded); }
    public function new_diagram_data_file($id) { return new diagram_data_file($id); }
    public function new_direct_gnd_file($file) { return new direct_gnd_file($file); }
}


// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
if (!isset($_SERVER["HTTP_HOST"]))
    parse_str($argv[1], $_GET);

$PARAMS = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$is_example = isset($PARAMS["x"]) ? true : false;


$start_time = microtime(true);

$gnd = new gnd_v2($db, $PARAMS, new gnd_job_factory($is_example));



if ($gnd->parse_error()) {
    $output = $gnd->create_error_output($gnd->get_error_message());
    echo json_encode($output);
    exit;
}

if ($gnd->check_for_stats())
    $output = $gnd->get_stats();
else
    $output = $gnd->get_arrow_data();

$total_time = microtime(true) - $start_time;
$output["totaltime"] = $total_time;


if (!isset($_SERVER["HTTP_HOST"])) {
    print $output["totaltime"];
    print "\n";
} else {
    echo json_encode($output);
}

?>
