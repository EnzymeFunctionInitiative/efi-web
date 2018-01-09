<?php

include_once '../includes/main.inc.php';
if (isset($_GET['type'])) {
        $type = $_GET['type'];
        $stepa = new stepa($db,$_GET['id']);
        if ($stepa->get_key() != $_GET['key']) {
                echo "No EFI-EST Selected. Please go back2";
                exit;
        }
        
        $stepa->download_graph($type);

}
else {
        echo "No EFI-EST Selected. Please go back";
        exit;

}



?>
