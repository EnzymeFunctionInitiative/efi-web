<?php
require_once(__DIR__."/../../../init.php");
require_once(__DIR__."/inc/stats_admin_header.inc.php");

#$tmp_dir = defined("__UPLOADS_DIR__") ? __UPLOADS_DIR__ : "";
$tmp_file = "/tmp/efi.queue";

#$output = "N/A";
#if (is_dir($tmp_dir)) {
#    #$cmd = "/usr/bin/squeue -o \"%.18i %9P %25j %.8u %.2t %.10M %.6D %R\""; # > $tmp_dir/efi.queue`;
#    $cmd = "echo \"A\nB\nC\"";
#    $output = system($cmd);
#    ob_start();
#    passthru($cmd);
#    $output = ob_get_contents();
#    ob_end_clean();
#    $output = file_get_contents("$tmp_dir/efi.queue");
#}

$output = file_get_contents("$tmp_file");


?>
<h3>EFI Queue</h3>

<pre><?php echo $output; ?></pre>

</div>

<?php require_once(__DIR__."/inc/stats_footer.inc.php");
