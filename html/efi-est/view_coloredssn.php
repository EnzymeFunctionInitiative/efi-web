<?php 
require_once(__DIR__."/../../init.php");

require_once(__BASE_DIR__."/includes/login_check.inc.php");

use \efi\global_settings;
use \efi\global_header;
use \efi\table_builder;
use \efi\est\job_factory;
use \efi\est\functions;
use \efi\est\colorssn;
use \efi\training\example_config;
use \efi\sanitize;
use \efi\file_types;



$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($id === false || $key === false) {
    error500("Unable to find the requested job.");
    exit;
}


$is_example = example_config::is_example();
$obj = job_factory::create($db, $id, $is_example); // Creates a color SSN or a cluster analysis job

if ($key !== $obj->get_key()) {
    error500("Unable to find the requested job.");
    exit;
}

if (!$is_example && $obj->is_expired()) {
    require_once(__DIR__."/inc/header.inc.php");
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days(). " days";
    echo "<br>Your job was completed on " . $obj->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    require_once(__DIR__."/inc/footer.inc.php");
    exit;
}


$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}
$table = new table_builder($table_format);

$metadata = $obj->get_metadata();
$job_name = $obj->get_uploaded_filename();
foreach ($metadata as $row) {
    if (strpos($row[0], '<a href') !== false)
        $table->add_row_html_only($row[0], $row[1]);
    else
        $table->add_row($row[0], $row[1]);
}

$table_string = $table->as_string();

$baseSsnName = $obj->get_colored_xgmml_filename_no_ext();

if (isset($_GET["as-table"])) {
    $table_filename = functions::safe_filename($baseSsnName) . "_summary.txt";

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $table_filename . '"');
    header('Content-Length: ' . strlen($table_string));
    ob_clean();
    echo $table_string;
    exit(0);
}


$est_id = $obj->get_id();
$uploadedFilename = $obj->get_uploaded_filename();

$url = $_SERVER['PHP_SELF'] . "?" . http_build_query(array('id'=>$obj->get_id(), 'key' => $key));

$ex_param = $is_example ? "&x=".$is_example : "";
$job_type = $obj->get_create_type();


$file_info = array();
$hmm_graphics = "";
$weblogo_graphics = "";
$lenhist_graphics = "";
$alignment_list = "";
$cr_list = "";
$num_map = "";

$hmm_zip_file_list = array();
$weblogo_zip_file_list = array();
$msa_zip_file_list = array();
$pim_zip_file_list = array();


$dl_base = "download.php?id=$est_id&key=$key&" . ($is_example ? "x=$is_example&" : "");

$ssn_file_zip = "";
$ssn_file_zip_size = 0;

if ($job_type === "NBCONN") {
    $dl_base .= "dl=nbconn&";
    array_push($file_info, array(""));
    array_push($file_info, array("Neighborhood Connectivity Color Scale (PNG)", file_types::FT_nc_legend, 0));
    array_push($file_info, array("Neighborhood Connectivity Table", file_types::FT_nc_table, 0));

    $ssn_file_zip_size = format_file_size($obj->get_file_size(file_types::FT_ssn));
    $ssn_file_zip = $dl_base . "ft=" . file_types::FT_ssn;
} else if ($job_type === "CONVRATIO") {
    $dl_base .= "dl=convratio&";
    array_push($file_info, array(""));
    array_push($file_info, array("Convergence Ratio Table", file_types::FT_cr_table, 0));
} else {
    $want_clusters_file = true;
    $want_singles_file = false;

    $ur90_info = $obj->get_results_file_info(file_types::FT_fasta_ur90);
    $ur50_info = $obj->get_results_file_info(file_types::FT_fasta_ur50);
    $uniprotText = ($ur90_info !== false || $ur50_info !== false) ? "UniProt" : "";

    if ($obj->is_hmm_and_stuff_job()) {
        add_file_type($hmm_zip_file_list, array("HMMs for FASTA $uniprotText cluster (full length sequences)", file_types::FT_hmm), $obj);
        add_file_type($hmm_zip_file_list, array("HMMs for FASTA $uniprotText cluster (domain sequences)", file_types::FT_hmm_dom), $obj);

        add_file_type($weblogo_zip_file_list, array("WebLogos for FASTA $uniprotText cluster (full length sequences)", file_types::FT_weblogo), $obj);
        add_file_type($weblogo_zip_file_list, array("WebLogos for FASTA $uniprotText cluster (domain sequences)", file_types::FT_weblogo_dom), $obj);
        
        add_file_type($msa_zip_file_list, array("MSAs for FASTA $uniprotText cluster (full length sequences)", file_types::FT_msa), $obj);
        add_file_type($msa_zip_file_list, array("MSAs for FASTA $uniprotText cluster (domain sequences)", file_types::FT_msa_dom), $obj);

        add_file_type($pim_zip_file_list, array("Percent Identity Matrix for FASTA $uniprotText cluster (full length sequences)", file_types::FT_pim), $obj);
        add_file_type($pim_zip_file_list, array("Percent Identity Matrix for FASTA $uniprotText cluster (domain sequences)", file_types::FT_pim_dom), $obj);
        
        $len_hist_zip_file_list = array();
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (full length sequences, UniProt)", file_types::FT_len_hist), $obj);
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (full length sequences, UniRef90)", file_types::FT_len_hist_ur90), $obj);
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (full length sequences, UniRef50)", file_types::FT_len_hist_ur50), $obj);
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (domain sequences, UniProt)", file_types::FT_len_hist_dom), $obj);
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (domain sequences, UniRef90)", file_types::FT_len_hist_dom_ur90), $obj);
        add_file_type($len_hist_zip_file_list, array("Length Histograms for FASTA $uniprotText cluster (domain sequences, UniRef50)", file_types::FT_len_hist_dom_ur50), $obj);

        $hmm_graphics = $obj->get_hmm_graphics();
        $weblogo_graphics = $obj->get_weblogo_graphics();
        $lenhist_graphics = $obj->get_lenhist_graphics();
        $alignment_list = $obj->get_alignment_list();
        $cr_list = $obj->get_cr_results_list();
        $num_map = $obj->get_cluster_num_map();
        $dl_base .= "dl=ca&";
    } else {
        $dl_base .= "dl=colorssn&";
    }
    
    array_push($file_info, array("Mapping Tables"));
    add_file_type($file_info, array("UniProt ID-Color-Cluster number mapping table", file_types::FT_mapping), $obj);
    add_file_type($file_info, array("UniProt ID-Color-Cluster number mapping table (with domain)", file_types::FT_mapping_dom), $obj);

    //array_push($file_info, array("UniRef90 ID lists per cluster", $uniref90NodeFilesZip, $uniref90NodeFilesZipSize));

    array_push($file_info, array("ID Lists and FASTA Files per Cluster"));
    add_file_type($file_info, array("UniProt ID lists per cluster", file_types::FT_ids), $obj);
    add_file_type($file_info, array("UniProt ID lists per cluster (with domain)", file_types::FT_dom_ids), $obj);
    add_file_type($file_info, array("UniRef90 ID lists per cluster", file_types::FT_ur90_ids), $obj);
    add_file_type($file_info, array("UniRef90 ID lists per cluster (with domain)", file_types::FT_ur90_dom_ids), $obj);
    add_file_type($file_info, array("UniRef50 ID lists per cluster", file_types::FT_ur50_ids), $obj);
    add_file_type($file_info, array("UniRef50 ID lists per cluster (with domain)", file_types::FT_ur50_dom_ids), $obj);

    add_file_type($file_info, array("FASTA files per $uniprotText cluster", file_types::FT_fasta), $obj);
    add_file_type($file_info, array("FASTA files per $uniprotText cluster (with domain)", file_types::FT_fasta_dom), $obj);
    add_file_type($file_info, array("FASTA files per UniRef90 cluster", file_types::FT_fasta_ur90), $obj);
    add_file_type($file_info, array("FASTA files per UniRef90 cluster (with domain)", file_types::FT_fasta_dom_ur90), $obj);
    add_file_type($file_info, array("FASTA files per UniRef50 cluster", file_types::FT_fasta_ur50), $obj);
    add_file_type($file_info, array("FASTA files per UniRef50 cluster (with domain)", file_types::FT_fasta_dom_ur50), $obj);

    array_push($file_info, array("Miscellaneous Files"));
    add_file_type($file_info, array("Cluster sizes", file_types::FT_sizes), $obj);
    add_file_type($file_info, array("Cluster-based convergence ratio for UniProt IDs", file_types::FT_conv_ratio), $obj);

    if ($want_clusters_file)
        add_file_type($file_info, array("SwissProt annotations by cluster", file_types::FT_sp_clusters), $obj);
    if ($want_singles_file)
        add_file_type($file_info, array("SwissProt annotations by singletons", file_types::FT_sp_singletons), $obj);

    $ssn_file_zip_size = format_file_size($obj->get_file_size(file_types::FT_ssn));
    $ssn_file_zip = $dl_base . "ft=" . file_types::FT_ssn;
}



require_once(__DIR__."/inc/header.inc.php");

$header_text = "Download Colored SSN Files";
if ($job_type === "NBCONN")
    $header_text = "Neighborhood Connectivity";
else if ($job_type === "CONVRATIO")
    $header_text = "Convergence Ratio";
else if ($job_type === "CLUSTER")
    $header_text = "Cluster Analyses and Downloads";

?>	

<h2><?php echo $header_text; ?></h2>

<h3>Uploaded Filename: <?php echo $job_name; ?></h3>

<div>
<?php

if ($job_type === "NBCONN") {
    echo get_nbconn_text();
} else if ($job_type === "COLORSSN") {
    echo get_colorssn_text();
} else if ($job_type === "CLUSTER") {
    echo get_cluster_text(count($pim_zip_file_list)> 0);
}

?>
</div>


<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Submission Summary</a></li>
        <li><a href="#data">Data File Download</a></li>
<?php if ($weblogo_graphics) { ?>
        <li><a href="#weblogo">WebLogos</a></li>
<?php } ?>
<?php if ($cr_list) { ?>
        <li><a href="#crinfo">Consensus Residues</a></li>
<?php } ?>
<?php if ($hmm_graphics) { ?>
        <li><a href="#hmm">HMMs</a></li>
<?php } ?>
<?php if ($lenhist_graphics) { ?>
        <li><a href="#lenhist">Length Histograms</a></li>
<?php } ?>
    </ul>
    <div>
        <!-- JOB INFORMATION -->
        <div id="info">
            <h4>Submission Summary Table</h4>
            <table width="100%" class="pretty no-stretch">
            <?php echo $table_string ?>
            </table>
            <div style="float:right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$est_id&key=$key&as-table=1$ex_param" ?>'><button class='normal'>Download Information</button></a></div>
            <div style="clear:both;margin-bottom:20px"></div>
        </div>
        <div id="data">
            <?php echo global_header::get_global_citation(); ?>
<?php
if ($job_type !== "CONVRATIO") {
?>
            <h4>Colored SSN</h4>
<?php
}
?>


<?php
if ($job_type === "NBCONN") {
?>
            <p>
            Each node in the submitted SSN has been colored by NC, and a Neighborhood Connectivity attribute
            has been added with the NC factor.
            </p>
<?php
} else if ($job_type === "CONVRATIO") {
?>
            <p>Convergence ratio has been computed.</p>
<?php
} else {
?>
            <p>Each cluster in the submitted SSN has been identified and assigned a unique number and color.</p>
<?php
}
?>

<?php
if ($job_type !== "CONVRATIO") {
?>
            <p>
            <a href="<?php echo "$ssn_file_zip"; ?>"><button class="normal">Download ZIP (<?php echo $ssn_file_zip_size; ?>)</button></a>
<?php
    if (($job_type === "COLORSSN" || $job_type === "CLUSTER") && global_settings::advanced_options_enabled()) {
        $args = "est-id=$est_id&est-key=$key";
?>
            <a href="<?php echo "index.php?mode=cr&$args"; ?>"><button class="normal">Transfer to CR</button></a>
<?php
    }
?>
            </p>
            
            <h4>Supplementary Files</h4>
<?php
} else {
?>
<?php
}
?>


            <table width="100%" class="pretty no-border">
<?php
$first = true;
foreach ($file_info as $info) {
    // Output the header
    if (count($info) == 1) {
        if (!$first)
            echo "                </tbody>\n";
        $first = false;
        echo <<<HTML
                <tbody>
                    <tr style="text-align:center;">
                        <td colspan="3" class="file-header-row">
                            $info[0]
                        </td>
                    </tr>
                </tbody>
                <tbody class="file-group">

HTML;
        } else {
            echo <<<HTML
                    <tr style="text-align:center;">

HTML;
            // Output file info
            if (is_array($info[1])) {
                $type1 = $info[1][0];
                $type2 = $info[1][1];
                echo <<<HTML
                        <td>
                            <a href="$f1"><button class='mini'>Download</button></a>
                            <a href="$f2"><button class='mini'>Download (ZIP)</button></a>
                        </td>

HTML;
            // Output group header
            } else {
                $type1 = $info[1];
                $url = $dl_base . "ft=$type1";
                $text = substr($info[1], -4) == ".zip" ? "Download All (ZIP)" : "Download";
                echo <<<HTML
                        <td><a href="$url"><button class='mini'>$text</button></a></td>

HTML;
            }
            echo <<<HTML
                        <td>$info[0]</td>
                    </tr>

HTML;
        }
    }
?>
                </tbody>
            </table>

<?php
if ($job_type !== "NBCONN" && $job_type !== "CONVRATIO" && !$is_example) {
?>
            <center>
                <a href="../efi-cgfp/index.php?<?php echo "est-id=$est_id&est-key=$key"; ?>"><button type="button" name="analyze_data" class="dark white">Run CGFP on Colored SSN</button></a>
            </center>
<?php
}
?>

        </div>
<?php if ($weblogo_graphics) { ?>
        <div id="weblogo">
            <h4>WebLogos</h4>
            <p>
            If the WebLogo is missing for Node Cluster 1 (and additional clusters with large numbers of nodes),
            repeat the job with a "Maximum Node Count" in the Sequence Filter input window.  MUSCLE can fail
            with a "large" number of sequences (variable, anywhere from &gt;750 to &gt;1500).
            </p>
<?php output_zip_file_table($weblogo_zip_file_list, $dl_base); ?>
<?php output_zip_file_table($msa_zip_file_list, $dl_base); ?>
<?php output_zip_file_table($pim_zip_file_list, $dl_base); ?>
            <br><br>
<?php
                    $weblogo_output_fn = function($cluster_num, $data, $info = "", $type = "", $quality = "") use ($est_id, $key, $alignment_list, $ex_param) {
                        $size_info = get_cluster_size_info($data);
                        $class = $type ? "weblogo-$type-$quality" : "";

                        $url = "save_logo.php?id=$est_id&key=$key&logo=$cluster_num-$type-$quality$ex_param";
                        $extra = "";
                        $type_html = $type ? '<div><b>'.ucfirst($type).' Sequences</b></div>' : "";
                        if (isset($alignment_list[$cluster_num][$type][$quality])) {
                            $afa_file = $alignment_list[$cluster_num][$type][$quality]["path"];
                            $extra = <<<HTML
<a href="$url&t=afa">MSA <i class="fas fa-download"></i></a>
HTML;
                        }
                        return <<<HTML
<div class="$class " style="margin-bottom: 20px">
    $type_html
    <div class="cluster-size">$size_info</div>
    <div>
        <span style="margin-right: 20px">$extra</span>
        <a href="$url&t=w">PNG <i class="fas fa-download"></i></a>
    </div>
    <div style="clear: both">
        <img src="$url&t=w" width="50%" alt="Cluster $cluster_num" data-logo="$cluster_num-$type-$quality">
    </div>
</div>\n
HTML;
                    };

                    $weblogo_cb_id_list = output_graphics_html("Show WebLogos:", "weblogo", $weblogo_graphics, $num_map, $weblogo_output_fn);
?>
        </div>
<?php } ?>
<?php if ($cr_list) { ?> 
        <div id="crinfo">
            <h4>Consensus Residues</h4>
            
            <table width="100%" class="pretty no-border">
<?php
                    foreach ($cr_list as $aa => $seq_types) {
                        echo <<<HTML
                <tbody>
                    <tr style="text-align:center;">
                        <td colspan="3" class="file-header-row">
                            $aa
                        </td>
                    </tr>
                </tbody>
                <tbody class="file-group">
HTML;
                        foreach ($seq_types as $seq_type => $file_list) {
                            foreach ($file_list as $file_type => $info) {
                                $path = $info["path"];
                                $size_text = "&lt;1 MB";
                                $dl_btn_text = "Download";
                                //if ($file_type == "zip") {
                                //    $info_text = "ID lists grouped by consensus residue percentage and number of residues in cluster ($seq_type)";
                                //    $dl_btn_text = "Download All (ZIP)";
                                //}
                                //elseif ($file_type == "position") {
                                if ($file_type == "position") {
                                    $info_text = "Consensus residue position summary table ($seq_type)";
                                } else {
                                    continue;
                                }
                                //elseif ($file_type == "percentage") {
                                //    $info_text = "Consensus residue percentage summary table ($seq_type)";
                                //}
                                $ft = $file_type == "position" ? file_types::FT_cons_res_pos : ($file_type == "percentage" ? file_types::FT_cons_res_pct : file_types::FT_cons_res_full);
                                $dl_path = "download.php?id=$est_id&key=$key&dl=ca&ft=$ft&aa=$aa";
                                echo <<<HTML
                    <tr style="text-align:center;">
                        <td><a href="$dl_path"><button class='mini'>$dl_btn_text</button></a></td>
                        <td>$info_text</td>
                        <td>$size_text</td>
                    </tr>
HTML;
                            }
                        }
                        echo <<<HTML
                </tbody>
HTML;
                    }
?>
            </table>
        </div>
<?php } ?>
<?php if ($hmm_graphics) { ?>
        <div id="hmm">
            <h4>HMMs</h4>
            <p>
            If the HMM is missing for Node Cluster 1 (and additional clusters with large numbers of nodes),
            repeat the job with a "Maximum Node Count" in the Sequence Filter input window.  MUSCLE can fail
            with a "large" number of sequences (variable, anywhere from &gt;750 to &gt;1500).
            </p>
<?php output_zip_file_table($hmm_zip_file_list, $dl_base); ?>
            <br><br>

<?php
    $output_fn = function($cluster_num, $data, $info = "", $type = "", $quality = "") use ($est_id, $key, $ex_param) {
        $file = $data["path"];
        if (isset($data["length"]))
            $info .= ", length=" . $data["length"];
        $size_info = get_cluster_size_info($data);
        $class = $type ? "hmm-$type-$quality" : "";
        $url = "save_logo.php?id=$est_id&key=$key&logo=$cluster_num-$type-$quality$ex_param";
        $type_html = $type ? '<div><b>'.ucfirst($type).' Sequences</b></div>' : "";
        return <<<HTML
<div class="$class " style="margin-bottom: 20px">
    $type_html
    <div class="cluster-size">$size_info</div>
    <div>
        <span style="margin-right: 20px"><a class="hmm-logo hmm-logo-link" data-logo="$cluster_num-$type-$quality">Skylign <i class="fas fa-eye"></i></a></span>
        <span style="margin-right: 20px"><a href="$url&t=hmm">HMM <i class="fas fa-download"></i></a></span>
        <a href="$url&t=hmm-png">PNG <i class="fas fa-download"></i></a>
    </div>
    <div>
    <img src="$url&t=hmm-png" width="100%" alt="Cluster $cluster_num">
    </div>
</div>\n
HTML;
    };

    $hmm_cb_id_list = output_graphics_html("Show HMMs:", "hmm", $hmm_graphics, $num_map, $output_fn);
?>
        </div>
<?php } ?>
<?php if ($lenhist_graphics) { ?>
        <div id="lenhist">
            <h4>Length Histograms</h4>
<?php output_zip_file_table($len_hist_zip_file_list, $dl_base); ?>
            <br><br>
<?php
                    $lenhist_output_fn = function($cluster_num, $data, $info = "", $type = "", $quality = "") use ($est_id, $key, $ex_param) {
                        $file = $data["path"];
                        $size_info = get_cluster_size_info($data);
                        $class = $type ? "lenhist-$type-$quality" : "";
                        $url = "save_logo.php?id=$est_id&key=$key&logo=$cluster_num-$type-$quality&t=l$ex_param";
                        $type_html = $type ? '<div><b>'.ucfirst($type).' Sequences</b></div>' : "";
                        return <<<HTML
<div class="$class " style="float: left; width: 50%; margin-bottom: 20px">
$type_html
<div class="cluster-size">$size_info</div>
<div><a href="$url">PNG <i class="fas fa-download"></i></a></div>
<div>
<img src="$url" width="100%" alt="Cluster $cluster_num" data-logo="$cluster_num-$type-$quality">
</div>
</div>
HTML;
                    };

                    $lenhist_cb_id_list = output_graphics_html("Show Length Histograms:", "lenhist", $lenhist_graphics, $num_map, $lenhist_output_fn);
?>
            <div style="clear: both"></div>
        </div>
<?php } ?>
    </div>
</div>
   
<script src="../js/progress.js" type="text/javascript"></script>
<script>
$(document).ready(function() {
    $(".tabs").tabs();

<?php
    //if ($weblogo_graphics) {
    //    output_graphics_js($weblogo_cb_id_list, "weblogo");
    //}
    //if ($lenhist_graphics) {
    //    output_graphics_js($lenhist_cb_id_list, "lenhist");
    //}
    if ($hmm_graphics) {
    //    output_graphics_js($hmm_cb_id_list, "hmm");
?>
    var progress = new Progress($("#progressLoader"));
    progress.start();
    $(".hmm-logo").click(function(evt) {
        var parm = $(this).data("logo");
        var windowSize = ["width=1500,height=600"];
        var url = "view_logo.php?<?php echo "id=$est_id&key=$key$ex_param"; ?>" + "&logo=" + parm;
        var theWindow = window.open(url, "", windowSize);
        evt.preventDefault();
    });
    $(window)
        .on("load", function() { progress.stop(); })
        .on("error", function() { progress.stop(); });
    setTimeout(function() { progress.stop(); }, 4000);
<?php
    }
?>
});
</script>

<div id="progressLoader" class="progress-loader" style="display: none">
    <i class="fas fa-spinner fa-spin"></i>
    <div>Loading...</div>
</div>

<?php
require_once(__DIR__."/inc/footer.inc.php");


function format_file_size($size) {
    $mb = round($size, 0);
    if ($mb == 0)
        return "&lt;1 MB";
    return $mb . " MB";
}


function output_graphics_js($cb_id_list, $prefix) {
    foreach ($cb_id_list as $cb_id) {
        echo <<<HTML
    $("#$prefix-$cb_id").click(function() {
        if (this.checked) {
            $(".$prefix-$cb_id").show();
            $(".$prefix-cluster-heading").show();
        } else {
            $(".$prefix-$cb_id").hide();
        }
    });
HTML;
    }
}


function output_graphics_html($header, $prefix, $graphics, $num_map, $output_fn) {
    $cb_types = array();

    $clusters = array_keys($graphics);
    //sort($clusters);

    $html = "";
    for ($i = 0; $i < count($clusters); $i++) {
        $cluster = $clusters[$i];
        $num = isset($num_map[$cluster]) ? $num_map[$cluster] : 0;
        $num_html = "Sequence Cluster $cluster" . ($num ? " / Node Cluster $num" : "");
        $html .= "<div style=\"clear:both\">\n";
        $html .= "<h4 class=\"$prefix-cluster-heading \" style=\"margin-top: 30px\">$num_html</h4>\n";
        $html .= "</div>\n";
        $html .= "<div>\n";
        foreach ($graphics[$cluster] as $seq_type => $group_data) {
            foreach ($group_data as $quality => $data) {
                //$seq_info = "$seq_type, <b>$quality</b>";
                $seq_info = "";
                $html .= $output_fn($cluster, $data, $seq_info, $seq_type, $quality);
                $cb_types[$seq_type][$quality] = 1;
            }
        }
        $html .= "</div>\n";
    }

    $cb_id_list = array();
    //$html2 = "$header<br>\n";
    //foreach ($cb_types as $seq_type => $qdata) {
    //    foreach ($qdata as $quality => $junk) {
    //        $dash_type = $seq_type . "-" . $quality;
    //        $comma_type = $seq_type . ", " . $quality;
    //        $html2 .= "<input type=\"checkbox\" id=\"$prefix-$dash_type\" name=\"sel-hmm\" value=\"$dash_type\"><label for=\"$prefix-$dash_type\">$comma_type</label>" . PHP_EOL;
    //        array_push($cb_id_list, $dash_type);
    //    }
    //    $html2 .= "<br>\n";
    //}
    //$html2 .= "<br><br><br>";
    //echo $html2;

    echo $html;

    return $cb_id_list;
}

function output_zip_file_table($zip_list, $dl_base) {
    if (count($zip_list) === 0)
        return;
    echo <<<HTML
            <table width="100%" class="pretty no-border">
                <tbody class="file-group">
HTML;
    foreach ($zip_list as $index => $zip_data) {
        $text = $zip_data[0];
        $type = $zip_data[1];
        $size = $zip_data[2];
        $url = $dl_base . "ft=" . $type;
        echo <<<HTML
                    <tr style="text-align:center;">
                        <td><a href="$url"><button class='mini'>Download All (ZIP)</button></a></td>
                        <td>$text</td>
                        <td>$size</td>
                    </tr>
HTML;
    }
    echo <<<HTML
                </tbody>
            </table>
HTML;
}

function get_cluster_size_info($data) {
    $info = array();
    if (isset($data["num_uniprot"]) && $data["num_uniprot"])
        array_push($info, "UniProt: " . number_format($data["num_uniprot"]));
    if (isset($data["num_uniref90"]) && $data["num_uniref90"])
        array_push($info, "UniRef90: " . number_format($data["num_uniref90"]));
    if (isset($data["num_uniref50"]) && $data["num_uniref50"])
        array_push($info, "UniRef50: " . number_format($data["num_uniref50"]));
    return "Number of IDs: " . implode(", ", $info);
}

function make_split_button($args) {
    return <<<HTML
<button class="mini">Transfer To:</button><div class="btn-split"><button class="mini"><i class="fa fa-caret-down"></i></button>
<div class="btn-split-content"><a href="index.php?mode=cr&$args">Convergence Ratio</a></div></div>
HTML;
}


function add_file_type(&$data, $info, $obj) {
    if (!isset($info[1])) {
        array_push($data, array($info[0]));
        return;
    }
    // Returns false if the file doesn't exist, other wise returns the file size
    $file_info = $obj->get_results_file_info($info[1]);
    if ($file_info !== false)
        array_push($data, array($info[0], $info[1], format_file_size($file_info["file_size"])));
}















function get_colorssn_text() {
    return <<<HTML
    <p>
    Six node attributes were added to the input SSN:
    <b>Cluster Sequence Count</b>, 
    <b>Sequence Count Cluster Number</b>,
    <b>Cluster Node Count</b>,
    <b>Node Count Cluster Number</b>, 
    <b>node.fillColor</b> (according to Cluster Sequence Count, hexadecimal), and
    <b>Node Count Fill Color</b> (according to Cluster Node Count, hexadecimal).
    </p>

    <p>
    The <b>Data File Download</b> tab provides the Color SSN with the nodes colored 
    according to <b>Cluster Sequence Count (node.fillColor)</b>.
    </p>

    <p>
    To change the node colors in Cytoscape to <b>Node Count Fill Color</b>:  1) select all nodes; 
    2) on the Style Panel, click on the "?" in the Fill Color Property; 3) select 
    "Remove Bypass"; 4) deselect the nodes (now default node color); and 5) open 
    the Fill Color Property and select "<b>Node Count Fill Color</b>" as the Column and 
    "Passthrough Mapping" as the Mapping Type.  The nodes will be colored with the 
    <b>Node Count Fill Color</b>.
    </p>

    <p>
    The <b>Data File Download</b> tab also provides files for 1) the UniProt 
    ID-Color-Cluster Number mapping table, 2) ID Lists and FASTA Files for each 
    cluster, 3) cluster sizes, and 4) SwissProt annotations for clusters and 
    singletons.
    </p>
HTML;
}


function get_cluster_text($has_pim) {
    $pim_text = "";
    if ($has_pim) {
        $pim_text = "This tab also provides the percent identity matrix for the multiple sequence alignment, as computed by Clustal-Omega.";
    }
    return <<<HTML
    <p>
    The <b>Data File Download</b> tab provides the Color SSN with the nodes colored
    according to <b>node.fillColor (Cluster Sequence Count)</b>.
    </p>
    
    <p>
    Six node attributes were added to the input SSN:  <b>Cluster Sequence Count</b>,
    <b>Sequence Count Cluster Number</b>, <b>Cluster Node Count</b>,
    <b>Node Count Cluster Number</b>,
    <b>node.fillColor</b> (according to Cluster Sequence Count, hexadecimal), and
    <b>Node Count Fill Color</b> (according to Cluster Node Count, hexadecimal).
    <!--, and 7) Convergence Ratio.-->
    </p>
    
    <p>
    To change the node colors in Cytoscape to <b>Node Count Fill Color</b>:  1) select all nodes; 2) on
    the Style Panel, click on the "?" in the Fill Color Property; 3) select "Remove
    Bypass"; 4) deselect the nodes (default node color); and 5) open the Fill Color
    Property and select "<b>Node Count Fill Color</b>" as the Column and "Passthrough
    Mapping" as the Mapping Type.  The nodes will be recolored.
    </p>
    
    <p>
    The <b>Data File Download</b> tab also provides files for 1) UniProt ID-Color-Cluster
    Number mapping table, 2) ID Lists and FASTA Files for each cluster, 3) cluster
    sizes, and 4) SwissProt annotations for clusters and singletons.
    <!-- and 5) convergence ratios for clusters.  The value of the convergence ratio for each cluster also
    is displayed with the-->
    The number of UniRef/UniProt IDs for each cluster is displayed in the
    <b>WebLogos</b>, <b>HMMs</b>, and <b>Length Histograms</b> tabs.
    </p>
    
    <p>
    The <b>WebLogos</b> tab provides the WebLogo (generated using
    <a href="http://weblogo.threeplusone.com/">http://weblogo.threeplusone.com/</a>) and MSA (generated using MUSCLE) for the node
    IDs in each SSN cluster containing at least the specified "Minimum Node Count".
    The MSA can be viewed with Jalview (<a href="https://www.jalview.org/">https://www.jalview.org/</a>).
$pim_text
    </p>
    
    <p>
    The <b>Consensus Residues</b> tab provides a tab-delimited text file with the number
    of the conserved residues and their MSA positions for each specified residue in
    each SSN cluster (numbered by <b>Cluster Sequence Count</b>) containing at least the
    specified "<b>Minimum Node Count</b>". 
    </p>
    
    <p>
    The <b>HMMs</b> tab provides the HMM for each SSN cluster containing at least the
    specified "<b>Minimum Node Count</b>". The Skylign download provides the image of the
    HMM generated from the MSA (<a href="https://skylign.org/">https://skylign.org/</a>). The HMM text file can be
    viewed interactively by uploading to <a href="https://skylign.org/">https://skylign.org/</a> and selecting
    "Information Content – Above Background"; the probability of each amino acid
    residue and probability and length of an insert at each position is provided.
    The p
    </p>
    
    <p>
    The <b>Length Histograms</b> tab provides length histograms for each cluster
    containing at least the specified "Minimum Node Count". 
    </p>
HTML;
}


function get_nbconn_text() {
    return <<<HTML
    <p>
    Two node attributes were added to the input SSN:  1) <b>Neighborhood Connectivity</b>
    that quantitates the internode connections and 2) <b>Neighborhood Connectivity Coloring</b>
    that assigns a color based on the value of the NC.  
    </p>
    
    <p>
    The <b>Data File Download</b> tab provides the SSN with the nodes colored according to
    the value of the <b>Neighborhood Connectivity Coloring</b>.
    </p>
    
    <p>
    The <b>Neighborhood Connectivity Color Scale</b> download provides the color scale in
    the SSN.
    </p>

<!--    
    <p>
    If the input SSN was generated using the "domain" option, the download files
    include information for the full-length sequences as well as the domains in the
    node IDs. 
    </p>
-->
HTML;
}    






