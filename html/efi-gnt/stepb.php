<?php 
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__GNT_DIR__."/libs/diagram_jobs.class.inc.php");
require_once(__GNT_DIR__."/libs/gnn.class.inc.php");    


if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['key'])) {
    error404();
} else if (!isset($_GET['diagram'])) {
    $gnn = new gnn($db,$_GET['id']);
    if ($gnn->get_key() != $_GET['key']) {
        error404();
    }
} else {
    $key = diagram_jobs::get_key($db, $_GET['id']);
    if ($key != $_GET['key']) {
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


