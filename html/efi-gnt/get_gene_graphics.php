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

function is_cli() {
    if (php_sapi_name() == "cli" &&
        defined("STDIN") &&
        (!isset($_SERVER["HTTP_HOST"]) || empty($_SERVER["HTTP_HOST"])) &&
        (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR'])) &&
        (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) &&
        count($_SERVER['argv']) > 0)
    {
        return true;
    } else {
        return false;
    }
}

// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
if (is_cli()) {
    parse_str($argv[1], $_GET);
    if (isset($argv[2]) && file_exists($argv[2])) {
        $_GET['console-run-file'] = $argv[2];
    }
}


$PARAMS = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$is_example = isset($PARAMS["x"]) ? true : false;


$gnd = new gnd_v2($db, $PARAMS, new gnd_job_factory($is_example));


if ($gnd->parse_error()) {
    $output = $gnd->create_error_output($gnd->get_error_message());
    die("Invalid  input");
}

$data = $gnd->get_arrow_data();

$output = "Genome\tID\tStart\tStop\tSize (nt)\tStrand\tFunction\tFC\tSS\tSet\n";
$add_ipro = true;
//$add_ipro = is_cli();
foreach ($data["data"] as $row) {
    $A = $row["attributes"];
    $org = $A["organism"];
    $num = $A["num"];
    $query_processed = false;
    foreach ($row["neighbors"] as $N) {
        if ($N["num"] > $num && !$query_processed) {
            $query_processed = true;
            $output .= get_line($org, $A, $add_ipro);
        }
        $output .= get_line($org, $N, $add_ipro);
    }
}


$gnn_name = $gnd->get_job_name();
if (!is_cli())
    send_headers("${gnn_name}_gene_graphics.tsv", strlen($output));
print $output;



function get_line($organism, $data, $add_ipro = false) {
    if (!isset($data["accession"])) {
        return "";
    }
    
    $family = implode("; ", $data["family_desc"]);
    if (!$family)
        $family = "none";
    $ipro = "";
    if ($add_ipro && is_array($data["ipro_family"])) {
        $ipro = implode("; ", preg_grep("/^(?!none)/", $data["ipro_family"]));
        if ($ipro)
            $ipro = "; InterPro=$ipro";
    }

    $line = $organism;
    $line .= "\t" . $data["accession"];
    $line .= "\t" . round($data["start"] / 3);
    $line .= "\t" . round($data["stop"] / 3);
    $line .= "\t" . $data["seq_len"];
    $line .= "\t" . ($data["direction"] == "complement" ? "-" : "+");
    $line .= "\t" . $family . $ipro;
    $line .= "\t" . "";
    $line .= "\t" . "";
    $line .= "\t" . "";
    $line .= "\n";
    return $line;
}



function send_headers($dl_filename, $content_size) {
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . $dl_filename . "\"");
    header("Content-Transfer-Encoding: binary");
    header("Connection: Keep-Alive");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");
    header("Content-Length: " . $content_size);
    ob_clean();
}

?>
