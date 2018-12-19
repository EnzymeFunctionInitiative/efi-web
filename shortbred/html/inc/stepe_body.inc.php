<?php

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

<h3>Job Information</h3>

<table class="pretty" style="border-top: 1px solid #aaa;">
    <tbody>
        <?php echo $table_string; ?>
    </tbody>
</table>
<div style="float:left"><button type="button" class="mini" id="job-stats-btn">Show Job Statistics</button></div>
<?php if (!$IsExample) { ?>
<div style="float:right"><a href="stepe.php?<?php echo $id_query_string; ?>&as-table=1"><button type="button" class="mini">Download Information</button></a></div>
<?php } ?>
<div style="clear:both"></div>

<h3>Downloadable Data</h3>

<div class="tabs" id="download-tabs">
    <ul class="tab-headers">
        <li class="active"><a href="#download-ssn">SSN and CD-HIT Files</a></li>
        <li><a href="#download-median">CGFP Output (using median method)</a></li>
        <li><a href="#download-mean">CGFP Output (using mean method)</a></li>
    </ul>

    <div class="tab-content tab-content-normal">
        <div id="download-ssn" class="tab active">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_ssn_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
<?php outputResultsTable($dl_misc_items, $identify_only_id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
        <div id="download-median" class="tab">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_median_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
        <div id="download-mean" class="tab">
            <table class="pretty">
                <thead><th></th><th>File</th><th>Size</th></thead>
                <tbody>
<?php outputResultsTable($dl_mean_items, $id_query_string, $size_data, $file_types, $html_labels); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<h3>Heatmaps</h3>

<div class="tabs" id="heatmap-tabs">
    <ul class="tab-headers">
        <li class="active"><a href="#heatmap-clusters">Cluster Heatmap</a></li>
        <li><a href="#heatmap-singletons">Singleton Heatmap</a></li>
        <li><a href="#heatmap-combined">Combined Heatmap</a></li>
    </ul>

    <div class="tab-content">
        <div id="heatmap-clusters" class="tab active">
            <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=c&g=q" width="970" height="840" style="border: none"></iframe>
        </div>

        <div id="heatmap-singletons" class="tab">
            <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=s&g=q" width="970" height="840" style="border: none"></iframe>
        </div>

        <div id="heatmap-combined" class="tab">
            <iframe src="heatmap.php?<?php echo $hm_parm_string; ?>&res=m&g=q" width="970" height="840" style="border: none"></iframe>
        </div>
    </div>
</div>

<br><br>
Metagenomes:
<div style='margin-left: 40px; max-height: 300px; overflow-y: auto'>
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

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<hr style='margin: 20px 0'>



