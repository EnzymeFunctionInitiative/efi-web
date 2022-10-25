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


if ((!isset($_GET['id'])) || (!is_numeric($_GET['id']))) {
    error500("Unable to find the requested job.");
}

$generate = new stepa($db, $_GET['id'], false);
$gen_id = $generate->get_id();
$key = $_GET['key'];

if ($generate->get_key() != $_GET['key']) {
    error500("Unable to find the requested job.");
}

if ($generate->is_expired()) {
    require_once(__DIR__."/inc/header.inc.php");
    echo "<p class='center'><br>Your job results are only retained for a period of " . global_settings::get_retention_days(). " days";
    echo "<br>Your job was completed on " . $generate->get_time_completed();
    echo "<br>Please go back to the <a href='" . functions::get_server_name() . "'>homepage</a></p>";
    require_once(__DIR__."/inc/footer.inc.php");
    exit;
}


$table_format = "html";
if (isset($_GET["as-table"])) {
    $table_format = "tab";
}

$use_advanced_options = global_settings::advanced_options_enabled();

$gen_type = $generate->get_type();
$generate = dataset_shared::create_generate_object("TAXONOMY", $db, false);

if (!$generate->has_tax_data()) {
    error500("Invalid job type.");
}

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

?>	



<h2>Dataset Completed</h2>

<?php if ($job_name) { ?>
<h4 class="job-display">Submission Name: <b><?php echo $job_name; ?></b></h4>
<?php } ?>

<div class="tabs-efihdr tabs">
    <ul class="">
        <li class="ui-tabs-active"><a href="#info">Dataset Summary</a></li>
        <li><a href="#taxonomy-tab">Taxonomy Sunburst</a></li>
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
            <div style="float: right"><a href='<?php echo $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&key=$key&as-table=1" ?>'><button class="normal">Download Information</button></a></div>
            <div style="clear: both"></div>
        </div>

        <div id="taxonomy-tab">
<p>
The taxonomy distribution for the UniProt IDs identified as members of the 
input list of families is displayed.   
</p>

<p>
The UniRef90 and UniRef50 clusters containing the UniProt IDs are identified 
using the lookup table provided by UniProt/UniRef.  These UniRef90 and UniRef50 
clusters may contain UniProt IDs from other families; in addition, the UniRef90 
and UniRef50 clusters at a selected taxonomy level/category may contain UniProt 
IDs from other levels/categories.   This results from conflation of UniProt IDs 
in UniRef90 and UniRef50 clusters that share &ge;90% and &ge;50% sequence identity, 
respectively.
</p>

<p>
The numbers of UniProt IDs, UniRef90 cluster IDs, and UniRef50 cluster IDs at 
the selected level are displayed.
</p>

<p>
The sunburst is interactive, providing the ability to zoom to a selected 
taxonomic level by clicking on that level/category; clicking on the center 
circle will zoom the display to the next highest level.
</p>

            <div id="taxonomy">
            </div>
        </div>

<?php if ($histo_info !== false) { ?>
        <div id="histo">
<?php

for ($i = 0; $i < count($histo_info); $i++) {
    $type = $histo_info[$i][2];
?>
    <div>
        <!--<div><?php echo $histo_info[$i][0]; ?></div>-->
        <div>
            <img src="<?php echo $histo_info[$i][1]; ?>" /><br />
            <a href='graphs.php?id=<?php echo $gen_id; ?>&type=<?php echo $type; ?>&key=<?php echo $key; ?>'><button class='file_download'>Download high resolution <img src='../images/download.svg' /></button></a>
        </div>
    </div>
<?php
}

?>
        </div>
<?php } ?>
    </div>
</div>

        
<div style="margin-top:85px"></div>

<center>Portions of these data are derived from the Universal Protein Resource (UniProt) databases.</center>

<script>
    $(document).ready(function() {
        $(".tabs").tabs();
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

    var onComplete = function(app) {
        var estClickFn = function(app) {
            var estPath = "<?php echo global_settings::get_web_path('est'); ?>/index.php";
            var info = app.getCurrentNode();
            var encTaxName = encodeURIComponent(info.name);
            var args = [
                "tax-id=<?php echo $gen_id; ?>",
                "tax-key=<?php echo $key; ?>",
                "tree-id=" + info.id,
                "id-type=" + info.idType,
                "tax-name=" + encTaxName,
                "mode=tax",
            ];
            var urlArgs = args.join("&");
            var url = estPath + "?" + urlArgs;
            window.location = url;
        };
        var gndClickFn = function(app) {
            var gntPath = "<?php echo global_settings::get_web_path('gnt'); ?>/index.php";
            var info = app.getCurrentNode();
            var encTaxName = encodeURIComponent(info.name);
            var args = [
                "tax-id=<?php echo $gen_id; ?>",
                "tax-key=<?php echo $key; ?>",
                "tree-id=" + info.id,
                "id-type=" + info.idType,
                "tax-name=" + encTaxName,
            ];
            var urlArgs = args.join("&");
            var url = gntPath + "?" + urlArgs;
            window.location = url;
        };
        app.addTransferAction("sunburst-transfer-to-est", "Transfer to EFI-EST", "Transfer to EFI-EST", () => estClickFn(app));
        app.addTransferAction("sunburst-transfer-to-gnt", "Transfer to EFI-GND Viewer", "Transfer to EFI-GND Viewer", () => gndClickFn(app));
    };

    var sunburstTextFn = function() {
        return $('<div><?php echo $sunburst_post_sunburst_text; ?></div>');
    };

    var scriptAppDir = "<?php echo $SiteUrlPrefix; ?>/vendor/efiillinois/sunburst/php";
    var sbParams = {
            apiId: "<?php echo $gen_id; ?>",
            apiKey: "<?php echo $key; ?>",
            apiExtra: [],
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

<?php

function get_tax_sb_text($gen_type) {
    $text = "";

    if ($gen_type == "FAMILIES") {
        $text = <<<TEXT
<p>
Lists of UniProt, UniRef90, and UniRef50 IDs and FASTA-formatted sequences can 
be downloaded.
</p>

<p>
The lists of UniProt, UniRef90, and UniRef50 IDs can be transferred to the Accession option 
of EFI-EST to generate an SSN.  The Accession option provides both the Filter by Family and 
Filter by Taxonomy options that should be used to remove internal UniProt IDs 
that are not members of the input families and selected taxonomy 
level/category.
</p>

<p>
The lists also can be transferred to the GND-Viewer to obtain GNDs.
</p>
TEXT;
    } else if ($gen_type == "FASTA" || $gen_type == "FASTA_ID") {
        $text = <<<TEXT
<p>
The UniProt IDs and FASTA-formatted sequences can be downloaded.
</p>

<p>
The list of UniProt IDs can be transferred to the Accession option of EFI-EST to generate 
an SSN.  The Accession option provides both the Filter by Family and Filter by Taxonomy 
options that should be used to remove UniProt IDs that are not members of 
desired families and the selected taxonomy level/category.
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
The lists of UniProt, UniRef90, and UniRef50 IDs can be transferred to the Accession option 
of EFI-EST to generate an SSN.  The Accession option provides both the Filter by Family and 
Filter by Taxonomy options that should be used to remove internal UniProt IDs 
that are not members of the input families and selected taxonomy 
level/category.
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

