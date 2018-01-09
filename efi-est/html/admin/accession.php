<?php
include_once '../includes/stats_main.inc.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$api = new accession($db,$_GET['id']);
	echo $api->view_accession_file();
}
