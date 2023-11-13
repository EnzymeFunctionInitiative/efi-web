<?php
require_once(__DIR__ . "/../../init.php");

?>

<html>
<head>
<title>Utilities</title>
<style>
li { margin-top: 15px; font-size: 1.1em; }
</style>
</head>

<body>

<ul>
<?php if (defined("__ENABLE_ADVANCED_OPTIONS__") && __ENABLE_ADVANCED_OPTIONS__) { ?>
<li><a href="make_msa.php">Create MSA</a></li>
<?php } ?>
<li><a href="id_list.php">Sample ID list</a></li>
<li><a href="list_exclude.php">List exclude</a></li>
<?php if (defined("__ENABLE_ADVANCED_OPTIONS__") && __ENABLE_ADVANCED_OPTIONS__) { ?>
<li><a href="run_cdhit.php">Run CD-HIT</a></li>
<?php } ?>
</ul>

</body>
</html>

