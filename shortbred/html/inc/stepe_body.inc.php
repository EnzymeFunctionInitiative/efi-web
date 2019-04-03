<?php

if (!isset($HeatmapWidth))
    $HeatmapWidth = 940;
$HeatmapIframeWidth = $HeatmapWidth + 30;

// Vars that are set in stepe_vars.inc.php:
//     $table_string
//     $size_data
//     $file_types
//     $html_labels
//     $dl_ssn_items
//     $dl_misc_items
//     $dl_median_items
//     $dl_mean_items
//     $hm_parm_string
//
//
// Vars that are set in the main .php page:
//     $id_query_string
//     $job_obj
//
?>

<div class="tabs-efihdr tabs">
    <ul>
        <li><a href="#info">Submission Summary</a></li>
        <li><a href="#data">Quantify Results</a></li>
        <li class="ui-tabs-active"><a href="#hm">Heatmaps</a></li>
    </ul>

    <div>
        <div id="info">
            <h4>Submission Summary Table</h4>
            
            <table class="pretty" style="border-top: 1px solid #aaa;">
                <tbody>
                    <?php echo $table_string; ?>
                </tbody>
            </table>
            <?php if (!$IsExample) { ?>
            <div style="float:right"><a href="stepe.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="normal">Download Information</button></a></div>
            <?php } ?>
            <div style="clear:both"></div>

            <h4>Metagenomes Submitted to Quantification Step</h4>
            <div style='margin-top:20px; margin-left: 40px; max-height: 300px; overflow-y: auto'>
            <?php
                $mg_data = $job_obj->get_metagenome_data();
                foreach ($mg_data as $row) {
                    $mg_id = $row[0];
                    $bodysite = $row[1];
                    $info = "$mg_id: $bodysite<br>";
                    echo $info;
                }
            ?>
            </div>

        </div>

        <div id="data">
            <p>
            The markers that uniquely define clusters in the submitted SSN have been quantified in the metagenomes selected for analysis.
            </p>

            <p>
            Files are provided that contain details about the markers that have been identified present in metagenomes and their abundances.
            </p>
            
            <div class="tabs-efihdr tabs" id="download-tabs">
                <ul class="tab-headers">
                    <li class="active"><a href="#download-ssn">SSN and CD-HIT Files</a></li>
                    <li><a href="#download-median">CGFP Output (using median method)</a></li>
                    <li><a href="#download-mean">CGFP Output (using mean method)</a></li>
                </ul>
            
                <div class="tab-content tab-content-normal">
                    <div id="download-ssn" class="tab active">
                        <h4>SSN With Quantify Results</h4>
                        <p>
                        The SSN submited has been edited so that the markers and their abundances in the
                        selected metagenomes are included as node attributes.
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                            <?php 
                                make_results_row($id_query_string,
                                    array($file_types["ssn"], $file_types["ssn_zip"]),
                                    array("Download", "Download (ZIP)"),
                                    array($size_data["ssn"], $size_data["ssn_zip"]),
                                    $html_labels["ssn"]);
                            ?>
                            </tbody>
                        </table>

                        <h4>CGFP Family and Marker Data</h4>
                        <p>
                        The <b><?php echo $html_labels["cdhit"]; ?></b> file contains mappings of ShortBRED
                        families to SSN cluster number as well as a color that is assigned to each unique
                        ShortBRED family.  The <b><?php echo $html_labels["markers"]; ?></b> file lists the markers
                        that were identified.  Finally, the <b><?php echo $html_labels["mg-info"]; ?></b> file
                        provides available metadata associated with the selected metagenomes.
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                            <?php 
                                make_results_row($identify_only_id_query_string, $file_types["cdhit"],
                                    "Download", $size_data["cdhit"], $html_labels["cdhit"]);
                                make_results_row($identify_only_id_query_string, $file_types["markers"],
                                    "Download", $size_data["markers"], $html_labels["markers"]);
                                make_results_row($id_query_string, $file_types["mg-info"],
                                    "Download", $size_data["mg-info"], $html_labels["mg-info"]);
                            ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="download-median" class="tab">
                        <p>
                        The default is for ShortBRED to report the abundance of metagenome hits for
                        CD-HIT families using the "median method." The numbers of metagenome hits
                        identified by all of the markers for a CD-HIT consensus sequence are arranged
                        in increasing numerical order; the value for the median marker is used as the
                        abundance. This method assumes that the distribution of hits across the markers
                        for CD-HIT consensus sequence is uniform (expected if the metagenome sequencing
                        is "deep," i.e., multiple coverage). For seed sequences with an even number of
                        markers, the average of the two "middle" markers is used as the abundance. 
                        </p>
                        
                        <p>
                        Files detailing the abundance information are available for download.
                        </p>

                        <h4>Raw Abundance Data</h4>
                        <p>
                        Raw results for the individual proteins in the SSN (<?php echo $html_labels["protein"]; ?>)
                        as well as summarized by SSN cluster (<?php echo $html_labels["cluster"]; ?>)
                        are provided.  Units are in reads per kilobase of sequence per million sample reads (RPKM).
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                                <?php
                                    make_results_row($id_query_string, $file_types["protein"],
                                        "Download", $size_data["protein"], $html_labels["protein"]);
                                    make_results_row($id_query_string, $file_types["cluster"],
                                        "Download", $size_data["cluster"], $html_labels["cluster"]);
                                ?>
                            </tbody>
                        </table>

                        <h4>Average Genome Size-Normalized Abundance Data</h4>
                        <p>
                        Data are provided using Average Genome Size (AGS) normalization for
                        individual proteins in the SSN 
                        as well as summarized by SSN cluster.
                        Units are have been converted from RPKM to counts per microbial genome, using AGS estimated by MicrobeCensus.
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                                <?php
                                    make_results_row($id_query_string, $file_types["protein_genome_norm"],
                                        "Download", $size_data["protein_genome_norm"], $html_labels["protein_genome_norm"]);
                                    make_results_row($id_query_string, $file_types["cluster_genome_norm"],
                                        "Download", $size_data["cluster_genome_norm"], $html_labels["cluster_genome_norm"]);
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div id="download-mean" class="tab">
                        <p>
                        In the mean method for reporting abundances, the average value the abundances
                        identified by the markers for each CD-HIT consensus sequence marker is used to
                        report abundance. This method reports the presence of "any" hit for a marker
                        for a seed sequence. An asymmetric distribution of hits a seed sequence with
                        multiple markers is expected for "false positives," so the mean method should
                        be used with caution.
                        </p>

                        <p>
                        Files detailing the abundance information are available for download.
                        </p>

                        <h4>Raw Abundance Data</h4>
                        <p>
                        Raw results for the individual proteins in the SSN (<?php echo $html_labels["protein_mean"]; ?>)
                        as well as summarized by SSN cluster (<?php echo $html_labels["cluster_mean"]; ?>)
                        are provided.  Units are in reads per kilobase of sequence per million sample reads (RPKM).
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                                <?php
                                    make_results_row($id_query_string, $file_types["protein_mean"],
                                        "Download", $size_data["protein_mean"], $html_labels["protein_mean"]);
                                    make_results_row($id_query_string, $file_types["cluster_mean"],
                                        "Download", $size_data["cluster_mean"], $html_labels["cluster_mean"]);
                                ?>
                            </tbody>
                        </table>

                        <h4>Average Genome Size-Normalized Abundance Data</h4>
                        <p>
                        Data are provided using Average Genome Size (AGS) normalization for
                        individual proteins in the SSN 
                        as well as summarized by SSN cluster.
                        Units are have been converted from RPKM to counts per microbial genome, using AGS estimated by MicrobeCensus.
                        </p>
                        <table class="pretty">
                            <thead><th></th><th>File</th><th>Size</th></thead>
                            <tbody>
                                <?php
                                    make_results_row($id_query_string, $file_types["protein_genome_norm_mean"],
                                        "Download", $size_data["protein_genome_norm_mean"], $html_labels["protein_genome_norm_mean"]);
                                    make_results_row($id_query_string, $file_types["cluster_genome_norm"],
                                        "Download", $size_data["cluster_genome_norm"], $html_labels["cluster_genome_norm"]);
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div id="hm">
            <p>
            Heatmaps representing the quantification of sequences from SSN clusters per
            metagenome are available. 
            </p>
            
            <p>
            The y-axis lists the SSN cluster numbers for which metagenome hits were
            identified; the x-axis lists the metagenome datasets selected on the Identify
            Results page. A color scale is located on the right that displays the AGS
            normalized abundance of the number of gene copies for the "hit" per microbial
            genome in the metagenome sample.
            </p>
            
            <p>
            The metagenomes are grouped according to body site so that trends/consensus
            across the six body sites can be easily discerned. The default heat map is
            calculated using the median method to report abundances.
            </p>

            <div class="tabs-efihdr tabs" id="heatmap-tabs">
                <!--
                In Firefox version 64, the initial heatmap view doesn't show all of the data.  The missing data can be
                exposed by moving the mouse over the heatmap, or by scrolling the page.  This problem does not
                occur in earlier versions of Firefox, or in the Chrome, Safari, and Edge web browsers.  The URL
                from Firefox can be copied and pasted into another browser for visualization.  We are working
                on addressing the problem.
                -->

                <ul class="tab-headers">
                    <li class="active"><a href="#heatmap-clusters">Cluster Heatmap</a></li>
                    <li><a href="#heatmap-singletons">Singleton Heatmap</a></li>
                    <li><a href="#heatmap-combined">Combined Heatmap</a></li>
                </ul>
            
                <div class="tab-content">
                    <div id="heatmap-clusters" class="tab active">
                        This heatmap presents information for SSN cluster/metagenome hit pairs.
                        <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=c&g=q&w=<?php echo $HeatmapWidth; ?>" width="<?php echo $HeatmapIframeWidth; ?>" height="840" style="border: none"></iframe>
                    </div>
            
                    <div id="heatmap-singletons" class="tab">
                        This heatmap presents information for SSN singleton/metagenome hit pairs instead of SSN cluster/metagenome hit pairs.
                        <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=s&g=q&w=<?php echo $HeatmapWidth; ?>" width="<?php echo $HeatmapIframeWidth; ?>" height="840" style="border: none"></iframe>
                    </div>
            
                    <div id="heatmap-combined" class="tab">
                        This heatmap combines the information obtained for SSN cluster and singleton/metagenome hit pairs.
                        <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=m&g=q&w=<?php echo $HeatmapWidth; ?>" width="<?php echo $HeatmapIframeWidth; ?>" height="840" style="border: none"></iframe>
                    </div>
                </div>
            </div>

            <p>
            Tools for downloading and manipulating the heat map can be accessed by hovering and
            clicking above and to the right of the plot.
            </p>

            <p>
            Several filters are available for manipulating the heatmap. 
            <ul>
                <li>
                    <b>Show specific clusters</b>: input individual cluster numbers separated by
                    commas and/or a range of cluster numbers.  Only these input clusters are displayed
                    in the heatmap.
                </li>
                <li>
                    <b>Abundance to display</b>: hide any data values that are outside of the minimum
                    and/or maximum. These hidden values appear as a zero value cell (i.e. the lowest
                    color range).
                </li>
                <li>
                    <b>Use mean</b>:
                    display the heatmap using the mean method for reporting abundances instead of
                    the defaut median method.
                </li>
                <li>
                    <b>Display hits only</b>: show a black and white heatmap showing presence/absence
                    of "hits" (which makes it easier to see low abundance hits).
                </li>
                <li>
                    <b>Body Sites</b>: checkboxes are provided for each body site in the heatmap;
                    selecting one or more of these checkboxes will show data for those body sites only.
                </li>
            </ul>
            </p>

        </div>
    </div>
</div>

<div style="margin-top: 50px"></div>

