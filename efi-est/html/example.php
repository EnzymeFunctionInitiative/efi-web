<?php 
include_once '../includes/main.inc.php';
include_once 'inc/header.inc.php'; 


if ((isset($_GET['id'])) && (is_numeric($_GET['id']))) {
        $generate = new stepa($db,$_GET['id']);
        if ($generate->get_key() != $_GET['key']) {
                echo "No EFI-EST Selected. Please go back";
                exit;
        }
	$analysis_id = $_GET['analysis_id'];
        $analysis = new analysis($db,$analysis_id);

	if (time() > $analysis->is_expired()) {

                echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days() . " days.";
		echo "<br>Your job was completed on " . $analysis->get_time_completed();
                echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
                exit;
        }

	$stats = $analysis->get_network_stats();
	$rep_network_html = "";
	$full_network_html = "";
	
	for ($i=0;$i<count($stats);$i++) {
		if ($i == 0) {
			$path = functions::get_web_root() . "/results/" . $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
	                $full_network_html = "<tr>";
			$full_network_html .= "<td style='text-align:center;'><a href='" . $path . "'><button>Download</button></a></td>";
                	$full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>";
	                $full_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>";
        	        $full_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>";
                	$full_network_html .= "</tr>";
		}
		else {
			$percent_identity = substr($stats[$i]['File'],strpos($stats[$i]['File'],'-')+1);
			$percent_identity = substr($percent_identity,0,strrpos($percent_identity,'.'));
			$percent_identity = str_replace(".","",$percent_identity);
			$path = functions::get_web_root() . "/results/" . $analysis->get_output_dir() . "/" . $analysis->get_network_dir() . "/" . $stats[$i]['File'];
			$rep_network_html .= "<tr>";
			$rep_network_html .= "<td style='text-align:center;'><a href='" . $path . "'><button>Download</button></a></td>";
			$rep_network_html .= "<td style='text-align:center;'>" . $percent_identity . "</td>";
			$rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Nodes'],0) . "</td>";
			$rep_network_html .= "<td style='text-align:center;'>" . number_format($stats[$i]['Edges'],0) . "</td>";
			$rep_network_html .= "<td style='text-align:center;'>" . functions::bytes_to_megabytes($stats[$i]['Size'],0) . " MB</td>";
			$rep_network_html .= "</tr>";
		}
	}

}

else {

        echo "No EFI-EST Select.  Please go back";
        exit;

}


?>	

<h2>Download Network Files</h2>
	<p>&nbsp;</p>
	<h3>Full Network <a href="tutorial_download.php" class="question" target="_blank">?</a></h3>
	<p>Each node in the network is a single protein from the dataset. Large files (&gt;500MB) may not open.</p>

    <table width="100%" border="1">
	<tr>
	    <th></th>
	    <th># Nodes</th>
	    <th># Edges</th>
	    <th>File Size (MB)</th>
	</tr>
	<?php echo $full_network_html; ?>
    </table>

	<p>&nbsp;</p>
    <div class="align_left">
    <h3>Representative Node Networks <a href="tutorial_download.php" class="question" target="_blank">?</a></h3>
	<p>Each node in the network represents a collection of proteins grouped according to percent identity.</p>
    </div>
	    <table width="100%" border="1">
	<tr>
    <th></th>
    <th>% ID</th>
    <th># Nodes</th>
    <th># Edges</th>
    <th>File Size (MB)</th>
	</tr>

	<?php echo $rep_network_html; ?>
    </table>
    
    <hr>
  
<?php include_once 'inc/footer.inc.php'; ?>

