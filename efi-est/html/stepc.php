
<?php 
require_once '../includes/main.inc.php';
require_once '../libs/table_builder.class.inc.php';
require_once("../../includes/login_check.inc.php");


if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}

$generate = new stepa($db,$_GET['id']);
$gen_id = $generate->get_id();
$key = $_GET['key'];

if ($generate->get_key() != $_GET['key']) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}

if ($generate->is_expired()) {
    require_once 'inc/header.inc.php'; 
    echo "<p class='center'><br>Your job results are only retained for a period of " . functions::get_retention_days(). " days";
    echo "<br>Your job was completed on " . $generate->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    exit;
    require_once 'inc/footer.inc.php';
}




///////////////////////////////////////////////////////////////////////////////////////////////////
// This code handles submission of the form.  It exits at the end of the code block if the
// analysis job was successfully created.
if (isset($_POST['analyze_data'])) {
    foreach ($_POST as $var) {
        $var = trim(rtrim($var));
    }
    $min = $_POST['minimum'];
    if ($_POST['minimum'] == "") {
        $min = __MINIMUM__;
    }
    $max = $_POST['maximum'];
    if ($_POST['maximum'] == "") {
        $max = __MAXIMUM__;
    }

    $job_id = $_POST['id'];

    $analysis = new analysis($db);

    $customFile = "";
    if (isset($_FILES['cluster_file']) && (!isset($_FILES['cluster_file']['error']) || $_FILES['cluster_file']['error'] == 0)) {
        $customFile = $_FILES['cluster_file']['tmp_name'];
    }

    if (user_auth::has_token_cookie()) {
        $email = user_auth::get_email_from_token($db, user_auth::get_user_token());
        if (functions::is_job_sticky($db, $job_id, $email)) {
            $parent_id = $job_id;
            $parent_check = functions::is_parented($db, $job_id, $email);

            if ($parent_check === false) {
                $job_id = stepa::duplicate_job($db, $job_id, $email);
                if ($job_id === false) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                    die("Unable to create duplicate of $parent_id for $email");
                }
            } else {
                $job_id = $parent_check;
            }
        }
    }

    $result = $analysis->create(
        $job_id,
        $_POST['evalue'],
        $_POST['network_name'],
        $min,
        $max,
        $customFile);

    if ($result['RESULT']) {
        header('Location: stepd.php');
        exit(0);
    }
}

// If an analysis was not submitted, then display the Step C page.



$updateMessage = functions::get_update_message();

$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);


$web_address = dirname($_SERVER['PHP_SELF']);
$date_completed = $generate->get_time_completed_formatted();
$db_version = $generate->get_db_version();


$gen_type = $generate->get_type();
$formatted_gen_type = functions::format_job_type($gen_type);

$table->add_row("Date Completed", $date_completed);
if (!empty($db_version)) {
    $table->add_row("Database Version", $db_version);
}
$table->add_row("Input Option", $formatted_gen_type);
$table->add_row("Job Number", $gen_id);

$job_name = $gen_id . "_" . $gen_type;

$uploaded_file = "";
$included_family = "";
$num_family_nodes = $generate->get_num_family_sequences();
$num_full_family_nodes = $generate->get_num_full_family_sequences();
if (empty($num_full_family_nodes))
    $num_full_family_nodes = $num_family_nodes;
$total_num_nodes = $generate->get_num_sequences();
$extra_nodes_string = "";
$extra_nodes_ast = "";

if ($gen_type == "BLAST") {
    $generate = new blast($db,$_GET['id']);
    $code = $generate->get_blast_input();
    if ($table_format == "html") {
        $code = "<a href='blast.php?blast=$code' target='_blank'>View Sequence</a>";
    }
    $table->add_row("Blast Sequence", $code);
    $table->add_row("E-Value", $generate->get_evalue());
    $included_family = $generate->get_families_comma();
    if ($included_family != "") {
        $uniref = $generate->get_uniref_version();
        $fraction = $generate->get_fraction();
        $table->add_row("PFam/Interpro Families", $included_family);
        if ($uniref)
            $table->add_row("UniRef Version", $uniref);
        if ($fraction)
            $table->add_row("Fraction", $fraction);
    }
    $table->add_row("Maximum Blast Sequences", number_format($generate->get_submitted_max_sequences()));
}
elseif ($gen_type == "FAMILIES") {
    $generate = new generate($db,$_GET['id']);
    $included_family = $generate->get_families_comma();
    $seqid = $generate->get_sequence_identity();
    $overlap = $generate->get_length_overlap();
    $uniref = $generate->get_uniref_version();

    $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $table->add_row("Domain", $generate->get_domain());
    if ($uniref)
        $table->add_row("UniRef Version", $uniref);
    if ($seqid)
        $table->add_row("Sequence Identity", $seqid);
    if ($overlap)
        $table->add_row("Sequence Overlap", $overlap);
}
elseif ($gen_type == "ACCESSION") {
    $generate = new accession($db,$_GET['id']);
    $uploaded_file = $generate->get_uploaded_filename();
    if ($uploaded_file)
        $table->add_row("Uploaded Accession ID File", $uploaded_file);
    $table->add_row_html_only("No matches file", "<a href=\"" . $generate->get_no_matches_download_path() . "\"><button class=\"mini\">Download</button></a>");
    $included_family = $generate->get_families_comma();
    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $uniref = $generate->get_uniref_version();
    if ($uniref)
        $table->add_row("UniRef Version", $uniref);

    $term = "IDs";
    $table->add_row("Number of $term in Uploaded File", number_format($generate->get_total_num_file_sequences()));
    $table->add_row("Number of $term in Uploaded File with UniProt Match", number_format($generate->get_num_matched_file_sequences()));
    $table->add_row("Number of $term in Uploaded File without UniProt Match", number_format($generate->get_num_unmatched_file_sequences()));
}
elseif ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
    $generate = new fasta($db,$_GET['id']);
    $uploaded_file = $generate->get_uploaded_filename();
    if ($uploaded_file)
        $table->add_row("Uploaded Fasta File", $uploaded_file);
    $included_family = $generate->get_families_comma();
    if ($included_family != "")
        $table->add_row("PFam/Interpro Families", $included_family);
    $table->add_row("E-Value", $generate->get_evalue());
    $table->add_row("Fraction", $generate->get_fraction());
    $uniref = $generate->get_uniref_version();
    if ($uniref)
        $table->add_row("UniRef Version", $uniref);

    $num_file_seq = $generate->get_total_num_file_sequences();
    $num_matched = $generate->get_num_matched_file_sequences();
    $num_unmatched = $generate->get_num_unmatched_file_sequences();
    
    if (!empty($num_file_seq))
        $table->add_row("Number of Sequences in Uploaded File", number_format($num_file_seq));
    if (!empty($num_matched) && !empty($num_unmatched))
        $table->add_row("Number of FASTA Headers in Uploaded File", number_format($num_matched + $num_unmatched));
    if (!empty($num_matched))
        $table->add_row("Number of SSN Nodes with UniProt IDs from Uploaded File", number_format($num_matched));
    if (!empty($num_unmatched))
        $table->add_row("Number of SSN Nodes without UniProt IDs from Uploaded File", number_format($num_unmatched));

    if (!empty($num_family_nodes) && !empty($num_file_seq)) {
        $extra_num_nodes = $total_num_nodes - $num_family_nodes - $num_file_seq;
        if ($extra_num_nodes > 0) {
            $extra_nodes_string = "<div>* $extra_num_nodes additional nodes have been added since multiple UniProt IDs were found for a single sequence with more than one header in one or more cases.</div>";
            $extra_nodes_ast = "*";
        }
    }
}
elseif ($gen_type == "COLORSSN") {
    $generate = new colorssn($db, $_GET['id']);
    $table->add_row("Uploaded XGMML File", $generate->get_uploaded_filename());
    $table->add_row("Neighborhood Size", $generate->get_neighborhood_size());
    $table->add_row("Cooccurrence", $generate->get_cooccurrence());
}

if ($gen_type != "COLORSSN") {
    if (functions::get_program_selection_enabled())
        $table->add_row("Program Used", $generate->get_program());
}

if ($included_family && !empty($num_family_nodes))
    $table->add_row("Number of IDs in PFAM/InterPro Family", number_format($num_full_family_nodes));
$table->add_row("Total Number of Nodes $extra_nodes_ast", number_format($total_num_nodes));
$conv_ratio = $generate->get_convergence_ratio();
$convergence_ratio_string = "";
if ($conv_ratio > -0.5) {
    $convergence_ratio_string = <<<STR
The convergence ratio is a measure of the similarity of the sequences used in the BLAST.  It is the
ratio of the total number of edges retained from the BLAST (e-values less than the specified threshold;
default 5) to the total number of sequence pairs.  The value decreases from 1.0 for sequences that are
very similar (identical) to 0.0 for sequences that are very different (unrelated).
STR;
    $table->add_row("Convergence Ratio<a class=\"question\" title=\"$convergence_ratio_string\">?</a>", number_format($conv_ratio, 3));
    $convergence_ratio_string = "";
//    $table->add_row("Convergence Ratio<sup>+</sup>", number_format($conv_ratio, 3));
//    $convergence_ratio_string = <<<STR
//<div><sup>+</sup>
//The convergence ratio is a measure of the similarity of the sequences used in the BLAST.  It is the
//ratio of the total number of edges retained from the BLAST (e-values less than the specified threshold;
//default 5) to the total number of sequence pairs.  The value decreases from 1.0 for sequences that are
//very similar (identical) to 0.0 for sequences that are very different (unrelated).</div>
//STR;
}

$table_string = $table->as_string();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($job_name) . "_settings.txt";

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
    header('Content-Length: ' . strlen($table_string));
    ob_clean();
    echo $table_string;
}
else {

    require_once 'inc/header.inc.php'; 


    $date_completed = $generate->get_time_completed_formatted();
    $db_version = $generate->get_db_version();

    $url = $_SERVER['PHP_SELF'] . "?" . http_build_query(array('id'=>$generate->get_id(),
        'key'=>$generate->get_key()));

    function make_plot_download($gen, $hdr, $type, $preview_img, $download_img) {
        $html = "<span class='plot_header'>$hdr</span> \n";
        $html .= "<a href='graphs.php?id=" . $gen->get_id() . "&type=" . $type . "&key=" . $_GET["key"] . "'><button class='file_download'>Download <img src='images/download.svg' /></button></a>\n";
        if ($preview_img) {
            $html .= "<button class='accordion'>Preview</button>\n";
            $html .= "<div class='acpanel'>\n";
            $html .= "<img src='$preview_img' />\n";
            $html .= "</div>\n";
        } else {
            $html .= "<a href='$download_img'><button class='file_download'>Preview</button></a>\n";
            $html .= "<div></div>\n";
        }

        return $html;
    }



    $has_edge_evalue_data = $generate->get_has_edge_evalue_data();


?>	


<div id="update-message" class="update_message initial-hidden">
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

<h2 class="darkbg">Data set Completed</h2>
<p>&nbsp;</p>

<h3>Network Information</h3>

<p>
Generation Summary Table
<a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=$key&as-table=1" ?>'><button class="normal">Download</button></a>
</p>

<table width="100%" border="1">
    <?php echo $table_string; ?>
</table>
<?php echo $extra_nodes_string; ?>
<?php echo $convergence_ratio_string; ?>
<p>&nbsp;</p>

<hr>

<?php
    if ($generate->is_cd_hit_job()) {
?>

<h4>CD-HIT Counts</h4>

<table border="0">
<thead><th>Sequence % ID</th><th>Sequence Length</th><th>Number of Nodes</th></thead>
<tbody>
<?php
        $cdhit_stats = $generate->get_cdhit_stats();
        for ($i = 0; $i < count($cdhit_stats); $i++) {
            echo "<tr><td>";
            echo join("</td><td>", array($cdhit_stats[$i]['SequenceId'], $cdhit_stats[$i]['SequenceLength'], $cdhit_stats[$i]['Nodes']));
            echo "</td></tr>\n";
        }
?>
</tbody>
</table>

<hr>
  </div>
<?php
    } else {
?>

<h3><b>Parameters for SSN Finalization</b></h3>

To finalize the generation of an SSN, a similarity threshold that defines which protein sequences
should or should not be connected in a network is needed. This will determine the segregation of proteins into clusters.

<h3>Analyze your data set<a href="tutorial_analysis.php" class="question" target="_blank">?</a></h3>
<p>View plots and histogram to determine the appropriate lengths and alignment score before continuing.</p>

<?php echo make_plot_download($generate, "Number of Edges Histogram", "EDGES", $generate->get_number_edges_plot_sm(), $generate->get_number_edges_plot(1)); ?>

<?php echo make_plot_download($generate, "Length Histogram", "HISTOGRAM", $generate->get_length_histogram_plot_sm(), $generate->get_length_histogram_plot(1)); ?>

<?php echo make_plot_download($generate, "Alignment Length Quartile Plot", "ALIGNMENT", $generate->get_alignment_plot_sm(), $generate->get_alignment_plot(1)); ?>

<?php echo make_plot_download($generate, "Percent Identity Quartile Plot", "IDENTITY", $generate->get_percent_identity_plot_sm(), $generate->get_percent_identity_plot(1)); ?>


<hr><p><br></p>
<h3><b>Finalization Parameters</b></h3>

<form name="define_length" method="post" action="<?php echo $url; ?>" class="align_left" enctype="multipart/form-data">

<?php if (functions::custom_clustering_enabled()) { ?>
<div class="tabs">
    <ul class="tab-headers">
        <li class="active"><a href="#threshold">Alignment Score Threshold</a></li>
        <li><a href="#custom">Custom Clustering</a></li>
    </ul>

    <div class="tab-content" style="min-height: 300px">
        <div id="threshold" class="tab active">
<?php } ?>
            <h3>1: Alignment score for output <a href="tutorial_analysis.php" class="question" target="_blank">?</a></h3>
            <p>Select a lower limit for the aligment score for the output files. You will input an integer which represents the exponent of 10<sup>-X</sup> where X is the integer.</p>

            <p><input type="text" name="evalue" <?php if (isset($_POST['evalue'])) { echo "value='" . $_POST['evalue'] ."'"; } ?>>
            alignment score</p>

            This score is the similarity threshold which determine the connection of proteins with each other. All pairs of proteins with a similarity score below this number will not be connected. Sets of connected proteins will form clusters.

<?php if (functions::file_size_graph_enabled()) { ?>
            <br><button id="file-size-button" class="mini" type="button" style="margin-top: 20px">View Node-Edge-File Size Chart</button>
            <div id="node-edge-chart" class="advanced-options" style="display: none;">
                <iframe id="file-size-iframe" src="<?php echo $SiteUrlPrefix; ?>/node_edge_filesize.php" width="900" height="500" style="border: none"></iframe>
            </div>
<?php } ?>

<?php if ($has_edge_evalue_data) { ?>
            <br><button id="edge-evalue-button" class="mini" type="button" style="margin-top: 20px">View Edge Count vs Alignment Score Chart</button>
            <div id="edge-evalue-chart" style="display: none;">
                <iframe id="edge-evalue-iframe" src="edge_evalue.php?<?php echo "id=$gen_id&key=$key"; ?>" width="900" height="500" style="border: none"></iframe>
            </div>
<?php } ?>

            <hr>
            <h3>2: Sequence length restriction  <a href="tutorial_analysis.php" class="question" target="_blank">?</a>
                <span style='color:red'>Optional</span></h3>
            <p> This option can be used to restrict sequences used based on their length.</p>

            <p>
                <input type="text" name="minimum" maxlength='20' <?php if (isset($_POST['minimum'])) { echo "value='" . $_POST['minimum'] . "'"; } ?>> Min (Defaults: <?php echo __MINIMUM__; ?>)<br>
                <input type="text" name="maximum" maxlength='20' <?php if (isset($_POST['maximum'])) { echo "value='" . $_POST['maximum'] . "'"; } ?>> Max (Defaults: <?php echo __MAXIMUM__; ?>)
            </p>
<?php if (functions::custom_clustering_enabled()) { ?>
        </div>
        <div id="custom" class="tab">
            <h3>Custom Clustering File <a href="tutorial_analysis.php" class="question" target="_blank">?</a></h3>
            
            A file specifying which proteins are in what cluster can be uploaded.  The file must be givein in the format below.
            Tabs or spaces can be used instead of the ',' comma separating character.
<pre>
Protein_ID_1,Cluster#
Protein_ID_2,Cluster#
Protein_ID_3,Cluster#
...
</pre>
            <div class="primary-input">
<?php echo ui::make_upload_box("Custom cluster file (text)", "cluster_file", "progress-bar-cluster", "progress-num-cluster"); ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>


<hr>
<h3><?php if (!functions::custom_clustering_enabled()) { echo "3: "; } ?>Provide Network Name</h3>

<p>
    <input type="text" name="network_name" <?php if (isset($_POST['network_name'])) { echo "value='" . $_POST['network_name'] . "'";} ?>>
    Name
</p>

This name will be displayed in Cytoscape.

<p>
    <input type='hidden' name='id' value='<?php echo $generate->get_id(); ?>'>
</p>

<hr>

<center>
    <button type="submit" name="analyze_data" class="dark">Create SSN</button>
    <p style="color:red"><?php if (isset($result['MESSAGE'])) { echo $result['MESSAGE']; } ?></p>

<?php if (functions::is_beta_release()) { ?>
<h4><b><span style="color: blue">BETA</span></b></h4>
<?php } ?>
</center>

</form>


<center>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</center>

<script src="<?php echo $SiteUrlPrefix; ?>/js/accordion.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    var fileSizeIframe = $("#file-size-iframe");
    var edgeIframe = $("#edge-evalue-iframe");
    
    $('#file-size-button').click(function() {
        $header = $(this);
        //getting the next element
        $content = $header.next();
        //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        $content.slideToggle(100, function () {
            if ($content.is(":visible")) {
                $header.find("i.fas").addClass("fa-minus-square");
                $header.find("i.fas").removeClass("fa-plus-square");
            } else {
                $header.find("i.fas").removeClass("fa-minus-square");
                $header.find("i.fas").addClass("fa-plus-square");
            }
        });
        fileSizeIframe.attr('src', function() {
            return $(this).data('src');
        });
    });
    
    $('#edge-evalue-button').click(function() {
        $header = $(this);
        //getting the next element
        $content = $header.next();
        //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        $content.toggle();
        edgeIframe.attr('src', function() {
            return $(this).data('src');
        });
    });

    fileSizeIframe.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });
    edgeIframe.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });
});
</script>
<?php if (functions::custom_clustering_enabled()) { ?>
<script>
    $(document).ready(function() {
        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            $(".tabs " + curAttrValue).fadeIn(300).show().siblings().hide();
            $(this).parent("li").addClass("active").siblings().removeClass("active");
            e.preventDefault();
        });
    }).tooltip();
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>
<?php } ?>
<?php
    }

    require_once 'inc/footer.inc.php';
}

?>

