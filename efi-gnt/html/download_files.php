<?php
require_once "../includes/main.inc.php";
require_once "../libs/gnn.class.inc.php";
require_once "../libs/diagram_data_file.class.inc.php";
require_once "../libs/diagram_jobs.class.inc.php";


$is_error = false;
$db_file = "";
$arrows = NULL;
$id = "";
$is_gnn = false;
$gnd_output_dir = settings::get_rel_output_dir();

if (isset($_GET["gnn-id"]) && is_numeric($_GET["gnn-id"])) {
    $id = $_GET["gnn-id"];
    $gnn = new gnn($db, $id);
    $is_gnn = true;

    if ($gnn->get_key() != $_GET["key"]) {
        $is_error = true;
    }
    elseif (time() < $gnn->get_time_completed() + settings::get_retention_days()) {
        $is_error = true;
    }

    $db_file = $gnn->get_diagram_data_file();
    if (!file_exists($db_file))
        $db_file = $gnn->get_diagram_data_file_legacy();
    $arrows = $gnn; // gnn class has shared API to diagram_data_file class for the purposes of this functionality
}
elseif (isset($_GET["direct-id"]) && is_numeric($_GET["direct-id"])) {
    $id = $_GET["direct-id"];
    $arrows = get_arrow_db($db, $id);
    $db_file = $arrows->get_diagram_data_file();
    $gnd_output_dir = settings::get_rel_diagram_output_dir();
}
elseif (isset($_GET["upload-id"]) && is_numeric($_GET["upload-id"])) {
    $id = $_GET["upload-id"];
    $arrows = get_arrow_db($db, $id);
    $db_file = $arrows->get_diagram_data_file();
    $gnd_output_dir = settings::get_rel_diagram_output_dir();
}
else {
    $is_error = true;
}

if ($is_error) {
    error404();
}

if (isset($_GET["type"])) {
    $type = $_GET["type"];

    if ($type == "data-file") {
        $dl_filename = pathinfo($db_file, PATHINFO_FILENAME) . ".sqlite";
        header("Location: $gnd_output_dir/$id/$dl_filename");
        exit(0);
    } elseif ($arrows === NULL) {
        $is_error = true;
    } elseif ($is_gnn == false && $type == "uniprot") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "${id}_${gnn_name}_UniProt_IDs.txt";
        $ids = $arrows->get_uniprot_ids();
        $content = "UniProt ID\tQuery ID\n";
        foreach ($ids as $upId => $otherId) {
            $content .= "$upId\t$otherId\n";
        }
        #$content = implode("\n", $ids);
        send_headers($dl_filename, strlen($content));
        print $content;
        exit(0);
    } elseif ($is_gnn == false && $type == "unmatched") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "${id}_${gnn_name}_Unmatched_IDs.txt";
        $ids = $arrows->get_unmatched_ids();
        $content = implode("\n", $ids);
        send_headers($dl_filename, strlen($content));
        print $content;
        exit(0);
    } elseif ($is_gnn == false && $type == "blast") {
        $gnn_name = $arrows->get_gnn_name();
        $dl_filename = "${id}_${gnn_name}_BLAST_Sequence.txt";
        $content = $arrows->get_blast_sequence();
        send_headers($dl_filename, strlen($content));
        print $content;
        exit(0);
    } elseif ($type == "bigscape") {
        $cluster_file = $arrows->get_bigscape_cluster_file();
        if ($cluster_file !== FALSE) {
            $gnn_name = $arrows->get_gnn_name();
            $dl_filename = "${id}_${gnn_name}_BiG-SCAPE_clusters.txt";
            $content_size = filesize($cluster_file);
            send_headers($dl_filename, $content_size);
            readfile($cluster_file);
            exit(0);
        }
    } else {
        $is_error = true;
    }
}

if ($is_error) {
    error404();
}





function send_headers($dl_filename, $content_size) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $dl_filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $content_size);
    ob_clean();
}







function get_arrow_db($db, $id) {
    $arrows = new diagram_data_file($id);
    $key = diagram_jobs::get_key($db, $id);
    $time_comp = diagram_jobs::get_time_completed($db, $id);

    if ($key != $_GET["key"]) {
        $is_error = true;
    }
    elseif (!$arrows->is_loaded()) {
        $is_error = true;
    }
    elseif ($time_comp === false || time() < $time_comp + settings::get_retention_days()) {
        $is_error = true;
    }

    return $arrows;
}

?>

