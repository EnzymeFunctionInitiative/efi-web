<?php
require_once(__DIR__."/inc/stats_main.inc.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$api = new accession($db,$_GET['id']);
	echo $api->view_accession_file();
}
