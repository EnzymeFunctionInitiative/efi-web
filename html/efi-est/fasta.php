<?php
require_once(__DIR__."/../../init.php");

use \efi\est\fasta;
use \efi\sanitize;


$id = sanitize::validate_id("id", sanitize::GET);

if ($id !== false) {
	$fasta = new fasta($db, $id);
	echo $fasta->view_fasta_file();
} else {
    exit;
}


