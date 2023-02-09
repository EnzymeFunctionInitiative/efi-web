<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\est\est_ui;
use \efi\gnt\gnt_ui;
use \efi\cgfp\cgfp_ui;
use \efi\training\example_config;



if (!isset($_GET["id"])) {
    error500("Unable to find the requested info.");
}
$id = $_GET["id"];
if ($id !== "biochem" && $id !== "2022") {
    error500("Unable to find the requested info (invalid).");
}



$NoAdmin = true;

$has_advanced_options = global_settings::advanced_options_enabled();

$main_html = "";
if ($id === "biochem") {
    $ExtraTitle = "From The Bench";
} else if ($id === "2022") {
    $ExtraTitle = "JMB Resources";
}

$config = new example_config($db, $id);
$sections = $config->get_section_order();

require_once(__DIR__."/inc/header.inc.php");

$active_tab = $id;
include("inc/tab_header.inc.php");

?>

<div class="tab-content"> <!-- from the top-most tabs -->
    <div>
        <?php echo $main_html; ?>
    </div>
    <div id="jobs">
<?php

if ($id === "biochem") {
    include(__DIR__."/examples/biochem/main.inc.php");
} else if ($id === "2022") {
    include(__DIR__."/examples/2022/main.inc.php");
}

?>
    </div> <!-- nested -->
</div> <!-- nested -->

<?php include("inc/tab_footer.inc.php"); ?>



<script>
$(document).ready(function() {
}).tooltip();
</script>

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>



