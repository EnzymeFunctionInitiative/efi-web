<?php 
require_once(__DIR__."/../../init.php");

use \efi\ui;
use \efi\table_builder;
use \efi\global_settings;
use \efi\user_auth;

use \efi\est\stepa;
use \efi\est\analysis;
use \efi\est\dataset_shared;
use \efi\est\functions;
use \efi\training\example_config;
use \efi\sanitize;


$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);

if ($id === false || $key === false) {
    error_404();
    exit;
}


$is_example = example_config::is_example();
$generate = new stepa($db, $id, $is_example);
$gen_id = $id;

if ($generate->get_key() != $key) {
    error500("Unable to find the requested job.");
}

if ($generate->is_expired()) {
    $header_file = __DIR__ . "/inc/header.inc.php";
    $footer_file = __DIR__ . "/inc/footer.inc.php";
    error_expired($header_file, $footer_file, $generate->get_time_completed());
}


$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}

$use_advanced_options = global_settings::advanced_options_enabled();

$gen_type = $generate->get_type();
$generate = dataset_shared::create_generate_object("TAXONOMY", $db, $is_example);

$has_tax_data = $generate->has_tax_data();

$job_name = $generate->get_job_name();

//$uniref = dataset_shared::get_uniref_version($gen_type, $generate);
//$sunburst_app_uniref = $uniref ? $uniref : "false"; // Is the entire app UniRef-based?
//$hasUniref = ($gen_type == "FAMILIES" || $gen_type == "ACCESSION") ? "true" : "false";
$sunburst_app_primary_id_type = "UniProt";
$has_uniref = "false";
$sunburst_app_uniref = 0;
if ($gen_type == "FAMILIES" || $gen_type == "ACCESSION") {
    $sunburst_app_uniref = 50;
    $has_uniref = "true";
}
$sunburst_post_sunburst_text = get_tax_sb_text($gen_type);
$sunburst_tax_info_text = get_tax_info_text($gen_type);

$table = new table_builder($table_format);
dataset_shared::add_generate_summary_table($generate, $table, false, false);
$table_string = $table->as_string();


if (isset($_GET["as-table"])) {
    $file_job_name = $gen_id . "_" . $gen_type;
    $table_filename = functions::safe_filename($file_job_name) . "_summary.txt";

    dataset_shared::send_table($table_filename, $table_string);
    exit(0);
}


$IncludeSunburstJs  = true;
require_once(__DIR__."/inc/header.inc.php");

$date_completed = $generate->get_time_completed_formatted();

$histo_info = $generate->get_length_histogram_info();

$ex_param = $is_example ? "&x=$is_example" : "";

?>	



<h2>Dataset Completed</h2>

<?php if ($job_name) { ?>
<h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4>
<?php } ?>

<?php if (!$has_tax_data) { ?>

<div>No sequences were identified. If Taxonomy Filtering was used, please verify that the selected taxonomy parameters are spelled correctly.</div>

<?php } else { ?>

<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Dataset Summary</a></li>
        <li data-tab-id="sunburst"><a href="#taxonomy-tab">Taxonomy Sunburst</a></li>
<?php if ($histo_info !== false) { ?>
        <li><a href="#histo">Length Histograms</a></li>
<?php } ?>
    </ul>
    <div>
        <!-- JOB INFORMATION -->
        <div id="info">
            <p>
            The parameters for generating the initial dataset are summarized in the table. 
            </p>
            
            <table width="100%" class="pretty no-stretch">
                <?php echo $table_string; ?>
            </table>
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=$id&key=$key&as-table=1" ?>'><button class="normal">Download Information</button></a></div>
            <div style="clear: both"></div>
        </div>

        <div id="taxonomy-tab">
<?php echo $sunburst_tax_info_text; ?>
            <div id="taxonomy" style="position: relative">
            </div>
        </div>

<?php if ($histo_info !== false) { ?>
        <div id="histo">
<?php

for ($i = 0; $i < count($histo_info); $i++) {
    $type = $histo_info[$i][1];
    $url = "graphs.php?id=$gen_id&type=len_hist&gtype=$type&key=$key$ex_param";
?>
    <div>
        <h5 style="margin-top: 30px">Number of sequences at each length - <?php echo $histo_info[$i][0]; ?></h5>
        <div>
            <img src="<?php echo $url; ?>" width="700" /><br />
            <a href="<?php echo $url; ?>"><button class='file_download'>Download high resolution <img src='../images/download.svg' /></button></a>
        </div>
    </div>
<?php
}

?>
        </div>
<?php } ?>
    </div>
</div>

<?php } ?>
        
<div style="margin-top:85px"></div>

<center>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</center>

<script>
    $(document).ready(function() {
        $(".tabs").tabs({
            activate: sunburstTabActivate // comes from shared/js/sunburst.js
        });
    });
        
    var acc = document.getElementsByClassName("panel-toggle");
    for (var i = 0; i < acc.length; i++) {
        acc[i].onclick = function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.maxHeight){
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            } 
        }
    }

    var hasUniref = <?php echo $has_uniref; ?>;

    var estPath = "<?php echo global_settings::get_web_path('est'); ?>/index.php";
    var gntPath = "<?php echo global_settings::get_web_path('gnt'); ?>/index.php";
    var jobId = "<?php echo $gen_id; ?>";
    var jobKey = "<?php echo $key; ?>";
    var onComplete = makeOnSunburstCompleteFn(estPath, gntPath, jobId, jobKey);
    var isExample = <?php echo $is_example ? "\"$is_example\"" : "\"\""; ?>;

    var apiExtra = [];
    if (isExample)
        apiExtra.push(["x", isExample]);

    var sunburstTextFn = function() {
        return $('<div><?php echo $sunburst_post_sunburst_text; ?></div>');
    };

    var scriptAppDir = "<?php echo $SiteUrlPrefix; ?>/vendor/efiillinois/sunburst/php";
    var sbParams = {
            apiId: "<?php echo $gen_id; ?>",
            apiKey: "<?php echo $key; ?>",
            apiExtra: apiExtra,
            appUniRefVersion: <?php echo $sunburst_app_uniref; ?>,
            appPrimaryIdTypeText: "<?php echo $sunburst_app_primary_id_type; ?>",
            appPostSunburstTextFn: sunburstTextFn,
            scriptApp: scriptAppDir + "/get_tax_data.php",
            fastaApp: scriptAppDir + "/get_sunburst_fasta.php",
            hasUniRef: hasUniref
    };

    var sunburstApp = new AppSunburst(sbParams);
    sunburstApp.attachToContainer("taxonomy");
    sunburstApp.addSunburstFeatureAsync(onComplete);
</script>

<script src="html/taxonomy/js/export_sunburst.js">
<script>addDownloadLinks()</script>

<?php

function get_tax_info_text($gen_type) {
    $text = "";

    if ($gen_type == "FAMILIES") {
        $text = <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs identified as members of the 
input list of families is displayed. 
</p>

<p>
The UniRef90 and UniRef50 clusters containing the UniProt IDs in the sunburst 
are identified using the lookup table provided by UniProt/UniRef. These 
UniRef90 and UniRef50 clusters may contain UniProt IDs from other families; in 
addition, the UniRef90 and UniRef50 clusters at a selected taxonomy category 
may contain UniProt IDs from other categories. This results from conflation of 
UniProt IDs in UniRef90 and UniRef50 clusters that share &ge;90% and &ge;50% sequence 
identity, respectively. 
</p>

<p>
The numbers of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs for 
the selected category are displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will return the display to the next highest rank. 
</p>
TEXT;
    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        $text = <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs identified in the input 
FASTA-formatted sequences is displayed. 
</p>

<p>
The number of UniProt IDs for the selected category is displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomy category by clicking on that category; clicking on the center circle 
will return the display to the next highest rank. 
</p>
TEXT;
    } else if ($gen_type == "ACCESSION") {
        $text = <<<TEXT
<p>
The taxonomy distribution for the UniProt IDs identified in the input list of 
UniProt, UniRef90 cluster, or UniRef50 cluster IDs is displayed. 
</p>

<p>
The UniRef90 and UniRef50 clusters containing the UniProt IDs in the sunburst 
are identified using the lookup table provided by UniProt/UniRef. The numbers 
of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs for the selected 
category are displayed. The UniRef90 and UniRef50 clusters may contain UniProt 
IDs from other taxonomy categories. This results from conflation of UniProt IDs 
in UniRef90 and UniRef50 clusters that share &ge;90% and &ge;50% sequence identity, 
respectively. 
</p>

<p>
The numbers of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs for 
the selected category are displayed. 
</p>

<p>
The sunburst is interactive, providing the ability to zoom to selected taxonomy 
category by clicking on that category; clicking on the center circle will 
return the display to the next highest rank. 
</p>
TEXT;
    }

    return $text;
}


function get_tax_sb_text($gen_type) {
    $text = "";

    if ($gen_type == "FAMILIES") {
        $text = <<<TEXT
<p>
Lists of UniProt, UniRef90, and UniRef50 IDs and FASTA-formatted sequences can 
be downloaded. 
</p>

<p>
The UniProt, UniRef90, or UniRef50 IDs can be transferred to the Accession IDs 
option of EFI-EST to generate an SSN. The Accession IDs option provides both 
Filter by Family and Filter by Taxonomy that should be used to remove internal 
UniProt IDs from UniRef90 or UniRef50 clusters that are not members of the 
selected families and/or taxonomy category. 
</p>

<p>
The lists also can be transferred to the GND-Viewer to obtain GNDs. 
</p>
TEXT;
    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        $text = <<<TEXT
<p>
Lists of UniProt IDs and FASTA-formatted sequences can be downloaded. 
</p>

<p>
The UniProt IDs can be transferred to the Accession IDs option of EFI-EST to 
generate an SSN. 
</p>

<p>
The list also can be transferred to the GND-Viewer to obtain GNDs. 
</p>
TEXT;
    } else if ($gen_type == "ACCESSION") {
        $text = <<<TEXT
<p>
Lists of UniProt, UniRef90, and UniRef50 IDs and FASTA-formatted sequences can 
be downloaded. 
</p>

<p>
The UniProt, UniRef90, or UniRef50 IDs can be transferred to the Accession IDs 
option of EFI-EST to generate an SSN. The Accession IDs option provides Filter 
by Taxonomy that should be used to remove internal UniProt IDs that are not 
members of the selected taxonomy category. 
</p>

<p>
The lists also can be transferred to the GND-Viewer to obtain GNDs. 
</p>
TEXT;
    }

    $text = preg_replace('/\n/', " ", $text);

    return $text;
}


require_once(__DIR__."/inc/footer.inc.php");


