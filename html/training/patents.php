<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;

$extraStyle = '<link rel="stylesheet" type="text/css" href="css/refs.css">';
if (isset($StyleAdditional))
    array_push($StyleAdditional, $extraStyle);
else
    $StyleAdditional = array($extraStyle);
$NoAdmin = true;
$ExtraTitle = "Patents";
require_once(__DIR__."/inc/header.inc.php");

$active_tab = "patents";

$has_advanced_options = global_settings::advanced_options_enabled();

?>

<?php
include("inc/tab_header.inc.php");
?>
    <div class="tab-content">

<?php
include(__DIR__."/patents.html");
?>

    </div>
</div>

<?php include("inc/tab_footer.inc.php"); ?>


<script>
    $(document).ready(function() {
    }).tooltip();
</script>

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


