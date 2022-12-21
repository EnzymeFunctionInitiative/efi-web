<?php 
require_once(__DIR__."/../../init.php");

use \efi\gnt\diagram_jobs;
use \efi\gnt\gnn;
use \efi\sanitize;


$id = sanitize::get_sanitize_string("id");
$key = sanitize::get_sanitize_key("key");

if (!isset($id) || !isset($key)) {
    error404();
} else if (!isset($_GET['diagram'])) {
    $gnn = new gnn($db, $id);
    if ($gnn->get_key() != $key) {
        error404();
    }
} else {
    $d_key = diagram_jobs::get_key($db, $id);
    if ($d_key != $key) {
        error404();
    }
}

$isDiagram = isset($_GET['diagram']) && $_GET['diagram'];

require_once(__DIR__."/inc/header.inc.php");

?>



<?php if (!$isDiagram) { ?>
<h2>Completing Generation of GNN </h2>
<p>&nbsp;</p>
<p>An e-mail will be sent when your GNN generation is complete.</p>
<?php } else { ?>
<h2>Diagram is Being Processed</h2>
<p>&nbsp;</p>
<p>An e-mail will be sent when your diagram is ready to view.</p>
<?php } ?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


