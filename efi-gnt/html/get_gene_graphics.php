<?php
require_once("../includes/main.inc.php");
require_once("../libs/gnn.class.inc.php");
require_once("../libs/bigscape_job.class.inc.php");
require_once("../libs/gnd_v2.class.inc.php");


// This is necessary so that the gnd class environment doesn't get clusttered
// with the dependencies that gnn, etc. need.
class gnd_job_factory extends job_factory {
    function __construct($is_example) { $this->is_example = $is_example; }
    public function new_gnn($db, $id) { return $this->is_example ? new gnn_example($db, $id) : new gnn($db, $id); }
    public function new_gnn_bigscape_job($db, $id) { return new bigscape_job($db, $id, DiagramJob::GNN); }
    public function new_uploaded_bigscape_job($db, $id) { return new bigscape_job($db, $id, DiagramJob::Uploaded); }
    public function new_diagram_data_file($id) { return new diagram_data_file($id); }
}


// If this is being run from the command line then we parse the command line parameters and put them into _POST so we can use
// that below.
if (!isset($_SERVER["HTTP_HOST"]))
    parse_str($argv[1], $_GET);

$is_example = isset($_GET["x"]) ? true : false;


$gnd = new gnd_v2($db, $_GET, new gnd_job_factory($is_example));



if ($gnd->parse_error()) {
    $output = $gnd->create_error_output($gnd->get_error_message());
    die("Invalid  input");
}

$data = $gnd->get_arrow_data();

$output = "Genome\tID\tStart\tStop\tSize (nt)\tStrand\tFunction\tFC\tSS\tSet\n";

foreach ($data["data"] as $row) {
    $A = $row["attributes"];
    $org = $A["organism"];
    $num = $A["num"];
    $query_processed = false;
    foreach ($row["neighbors"] as $N) {
        if ($N["num"] > $num && !$query_processed) {
            $query_processed = true;
            $output .= get_line($org, $A);
        }
        $output .= get_line($org, $N);
    }
}


$gnn_name = $gnd->get_job_name();
send_headers("${gnn_name}_gene_graphics.tsv", strlen($output));
print $output;



function get_line($organism, $data) {
    if (!isset($data["accession"])) {
        return "";
    }
    
    $family = implode("; ", $data["family_desc"]);
    if (!$family)
        $family = "none";

    $line = $organism;
    $line .= "\t" . $data["accession"];
    $line .= "\t" . round($data["start"] / 3);
    $line .= "\t" . round($data["stop"] / 3);
    $line .= "\t" . $data["seq_len"];
    $line .= "\t" . ($data["direction"] == "complement" ? "-" : "+");
    $line .= "\t" . $family;
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
