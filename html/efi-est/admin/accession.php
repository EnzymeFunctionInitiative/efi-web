<?php
require_once(__DIR__."/../../../init.php");

use \efi\est\accession;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$api = new accession($db,$_GET['id']);
	echo $api->view_accession_file();
}

