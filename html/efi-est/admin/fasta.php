<?php
require_once(__DIR__."/../../../init.php");

use \efi\est\fasta;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$fasta = new fasta($db,$_GET['id']);
	echo $fasta->view_fasta_file();
}
