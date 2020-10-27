<?php
require_once(__DIR__."/../../../conf/settings_paths.inc.php");
require_once(__EST_DIR__."/libs/fasta.class.inc.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$fasta = new fasta($db,$_GET['id']);
	echo $fasta->view_fasta_file();
}
