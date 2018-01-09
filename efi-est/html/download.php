<?php

include_once '../includes/main.inc.php';
if (isset($_POST['download_network'])) {
	$analysis_id = $_POST['analysis_id'];
	$file = $_POST['file'];
	$stepa = new stepa($db,$_POST['id']);
        if ($stepa->get_key() != $_POST['key']) {
                echo "No EFI-EST Selected. Please go back";
                exit;
        }
	
        $analysis = new analysis($db,$analysis_id);
	$analysis->download_network($file);

}
else {
	echo "No EFI-EST Selected. Please go back";
	exit;

}



?>
