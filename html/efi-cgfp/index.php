<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\global_header;
use \efi\user_auth;
use \efi\ui;
use \efi\cgfp\cgfp_ui;
use \efi\cgfp\functions;
use \efi\cgfp\settings;
use \efi\cgfp\settings_shared;
use \efi\cgfp\job_manager;
use \efi\cgfp\job_types;
use \efi\sanitize;


$user_email = "Enter your e-mail address";

$IsLoggedIn = false; // contains the email address if the user is logged in. for the UI.
$show_previous_jobs = false;
$jobs = array();
$training_jobs = array();
$IsAdminUser = false;
$IsExample = true;
$user_token = "";
$is_enabled = \efi\global_settings::get_submit_enabled();
$is_sb_enabled = global_settings::get_shortbred_enabled();
$use_advanced_options = global_settings::advanced_options_enabled();

if (user_auth::has_token_cookie() && $db !== false) {
    $user_token = user_auth::get_user_token();
    $user_email = user_auth::get_email_from_token($db, $user_token);
    $IsAdminUser = user_auth::get_user_admin($db, $user_email);
    if ($user_email)
        $IsLoggedIn = $user_email;

    if ($is_sb_enabled) {
        $job_manager = job_manager::init_by_user($db, job_types::Identify, $user_token);
//        $is_enabled = $IsAdminUser || functions::is_shortbred_authorized($db, $user_token);

        // if $job_manager is a bit of a hack.  we're trying to track down an issue.
        if ($job_manager && settings::get_recent_jobs_enabled()) {
            $jobs = $job_manager->get_jobs_by_user($user_token);
            $training_jobs = $job_manager->get_training_jobs($user_token);
            $show_previous_jobs = count($jobs) > 0 || count($training_jobs) > 0;
        }
    }
}

$db_modules = global_settings::get_database_modules();
$default_cdhit_id = settings_shared::get_default_cdhit_id();
$default_ref_db = settings_shared::get_default_ref_db();
$default_search = settings_shared::get_default_identify_search();

    //'Please cite your use of the EFI tools:<br><br>' .
    //'R&eacute;mi Zallot, Nils Oberg, and John A. Gerlt, <b>The EFI Web Resource for Genomic Enzymology Tools: Leveraging Protein, Genome, and Metagenome Databases to Discover Novel Enzymes and Metabolic Pathways</b>. Biochemistry 2019 58 (41), 4169-4182. <a href="https://doi.org/10.1021/acs.biochem.9b00735">https://doi.org/10.1021/acs.biochem.9b00735</a>'
    //;
    //"The \"From the Bench\" article describing the tools and their use is available on the " . 
    //"<i class='fas fa-question'></i> <b>Training</b> page.<br>" .
    //"Access to videos about the use of Cytoscape for interacting with SSNs is also available on the same page.<br>" .
$update_msg = "";

if (!global_settings::get_shortbred_enabled()) {
    error404();
}

$est_id = sanitize::validate_id("est-id", sanitize::GET);
$est_key = sanitize::validate_key("est-key", sanitize::GET);

$est_filename = "";
if ($est_id !== false && $est_key !== false) {
    $est_filename = functions::get_est_job_filename($db, $est_id, $est_key);
} else {
    $est_id = 0;
}

$ShowCitation = true;
include("inc/header.inc.php");

?>



<p>
Chemically guided functional profiling (CGFP) maps metagenome protein abundance 
to clusters in sequence similarity networks (SSNs) generated by the EFI-EST web 
tool. 
</p>

<p>
EFI-CGFP uses the ShortBRED software package developed by Huttenhower and colleagues 
in two successive steps: 1) <b>identify</b> sequence markers that are unique to 
members of families in the input SSN that are identified by ShortBRED and share 
85% sequence identity using the CD-HIT algorithm (CD-HIT 85 clusters) and 2) 
<b>quantify</b> the marker abundances in metagenome datasets and then map these to the 
SSN clusters. 
</p>

<p>
Currently, a library of 380 metagenomes is available for analysis. The dataset 
originates from the Human Microbiome Project (HMP) and consists of metagenomes 
from healthy adult women and men from six body sites [stool, buccal mucosa 
(lining of cheek and mouth), supragingival plaque (tooth plaque), anterior 
nares (nasal cavity), tongue dorsum (surface), and posterior fornix (vagina)].
</p>

<?php if ($update_msg) { ?>
<div id="update-message" class="update-message initial-hidden">
</div>
<div class="new-feature"></div>
<?php } ?>

<p><?php echo functions::get_update_message(); ?></p>

<div class="tabs-efihdr tabs" id="tab-container">
    <ul class="tab-headers">
        <?php if ($show_previous_jobs) { ?>
            <li class="active <?php if (!$est_id) echo "ui-tabs-active"; ?>"><a href="#jobs">Previous Jobs</a></li>
        <?php } ?>
        <li class="<?php if ($est_id) echo "ui-tabs-active"; ?>"><a href="#create">Run CGFP/ShortBRED</a></li>
        <li <?php if (! $show_previous_jobs) echo "class=\"ui-tabs-active\""; ?>><a href="#tutorial">Tutorial</a></li>
        <li><a href="#example">Example</a></li>
    </ul>

    <div class="tab-content">
        <?php if ($show_previous_jobs) { ?>
        <div id="jobs" class="tab <?php if (!$est_id) echo "ui-tabs-active"; ?>">
        <?php } ?>
        <?php if (count($jobs) > 0) { ?>
            <h4>CGFP Jobs</h4>
            <?php
                $allow_cancel = true;
                echo cgfp_ui::output_job_list($jobs, $allow_cancel);
            ?>
        <?php } ?>
            
        <?php if (count($training_jobs) > 0) { ?>
            <h4>Training Resources</h4>
            <?php
                $allow_cancel = false;
                echo cgfp_ui::output_job_list($training_jobs, $allow_cancel);
            ?>
        <?php } ?>
            
        <?php if ($show_previous_jobs) { ?>
        </div>
        <?php } ?>

        <div id="create" class="tab <?php if ($est_id) echo "ui-tabs-active"; ?>">
<?php
    if (!$is_enabled) {
        echo "Submission is currently disabled due to site maintenance.";
    } else {
?>
            <p>
            Upload the SSN for which you want to run CGFP/ShortBRED.
            The initial identify step will be performed: unique markers in the input SSN will be identified.
            </p>
    
            <p>
            </p>
    
            <form name="upload_form" id="upload_form" method="post" action="" enctype="multipart/form-data">
    
                <div class="primary-input">
                    <?php echo ui::make_upload_box("SSN File:", "ssn_file", "progress_bar", "progress_number", "", $SiteUrlPrefix, $est_filename); ?>
                    <p>
                    <b>The input SSN MUST be a Colored SSN generated with the Color SSN utility of EFI-EST or 
                    the colored SSN generated by EFI-GNT.</b>
                    The accepted format is XGMML (or compressed XGMML as zip).
                </div>

                <div class="option-panels">
                    <div id="seq-len-input">
                        <h3>Sequence Length Restriction Options</h3>
                        <div class="option-panel">
                            <p>
                            If the submitted SSN was generated using the UniRef90 or 50 option, then <b>it is
                            recommended to specify a minimum sequence length, in order to eliminate
                            fragments</b> that may be included in UniRef clusters. A maximum length can also
                            be specified.
                            </p>
                            <div>
                                <span class="input-name">
                                    Minimum:
                                </span>
                                <span class="input-field">
                                    <input name="ssn_min_seq_len" id="ssn_min_seq_len" type="text" size="7" /> (default: none)
                                </span>
                            </div>
                            <div>
                                <span class="input-name">
                                    Maximum:
                                </span>
                                <span class="input-field">
                                    <input name="ssn_max_seq_len" id="ssn_max_seq_len" type="text" size="7" /> (default: none)
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3>Marker Identification Options</h3>
                        <div class="option-panel">
                            <div>
                                Several parameters are available for the identify step.
                            </div>
                            <div>
                                <span class="input-name">
                                    Reference database:
                                </span>
                                <span class="input-field">
                                    <?php
                                       $ref_db_uniprot = $default_ref_db == "uniprot" ? "selected" : "";
                                       $ref_db_uniref90 = $default_ref_db == "uniref90" ? "selected" : "";
                                       $ref_db_uniref50 = $default_ref_db == "uniref50" ? "selected" : "";
                                    ?>
                                    <select name="ssn_ref_db" id="ssn_ref_db">
                                        <option value="uniprot" <?php echo $ref_db_uniprot; ?>>Full UniProt</option>
                                        <option value="uniref90" <?php echo $ref_db_uniref90; ?>>UniRef90</option>
                                        <option value="uniref50" <?php echo $ref_db_uniref50; ?>>UniRef50</option>
                                    </select>
                                </span>
                                <div class="input-desc">
                                    ShortBRED uses the UniProt, UniRef90 or UniRef50 databases to evaluate markers
                                    in order to eliminate those that could give false positives during the
                                    quantify step. The default database used in this process is UniRef90.
                                </div>
                            </div>
                            <div>
                                <span class="input-name">
                                    CD-HIT sequence identity (default <?php echo $default_cdhit_id; ?>%):
                                </span>
                                <span class="input-field">
                                    <input type="text" name="ssn_cdhit_sid" id="ssn_cdhit_sid" value="" size="4">
                                </span>
                                <div class="input-desc">
                                    This is the sequence identity parameter that will be used for determining the
                                    ShortBRED consensus sequence families.
                                </div>
                            </div>
        
                            <?php if (settings::get_diamond_enabled()) { ?>
                                <div>
                                    <span class="input-name">
                                        Sequence search type:
                                    </span>
                                    <span class="input-field">
                                        <?php
                                            $search_blast = $default_search == "BLAST" ? "selected" : "";
                                            $search_diamond = $default_search == "DIAMOND" ? "selected" : "";
                                        ?>
                                        <select name="ssn_search_type" id="ssn_search_type">
                                            <option <?php echo $search_blast; ?>>BLAST</option>
                                            <option <?php echo $search_diamond; ?>>DIAMOND</option>
                                        </select>
                                    </span>
                                    <div class="input-desc">
                                        This is the search algorithm that will be used to remove false positives and identify unique markers.
                                    </div>
                                </div>
        
                                <input type="hidden" name="ssn_diamond_sens" id="ssn_diamond_sens" value="normal" />
                            <?php } ?>
                        </div>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <h3>Dev Site Options</h3>
                        <div class="option-panel">
                            <?php
                            if (count($db_modules) > 1) {
                                echo <<<HTML
                            <div>
                                <span class="input-name">
                                    Database version: 
                                </span>
                                <span class="input-field">
                                    <select name="ssn_db_mod" id="ssn_db_mod">
HTML;
                                foreach ($db_modules as $mod_info) {
                                    $mod_name = $mod_info[1];
                                    echo "                            <option value=\"$mod_name\">$mod_name</option>\n";
                                }
                                echo <<<HTML
                                    </select>
                                </span>
                            </div>
HTML;
                            } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>

                <p>
                    <span class="input-name">
                        E-mail address: 
                    </span>
                    <span class="input-field">
                        <input name="ssn_email" id="ssn_email" type="text" value="<?php echo $user_email; ?>" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;">
                    </span>
                </p>
                <p>
                    You will receive an email when the markers have been generated.
                </p>
    
                <div id="ssn_message" style="color: red">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <?php if (!$IsAdminUser) { ?>
                    <div>
                        Multiple SSNs may be submitted, but due to resource constraints only one computation
                        will run at any given time.  Submitted jobs will be queued and executed when any
                        running job completes.
                    </div>
                <?php } ?>
                <center>
                    <div><button type="button" id="ssn_submit" name="ssn_submit" class="dark" onclick="submitJob()">
                                Upload SSN
                        </button></div>
                </center>
            </form>
<?php 
    }
?>
        </div>

        <div id="tutorial" class="tab <?php if (!$show_previous_jobs) echo "ui-tabs-active"; ?>">

            <h3>Chemically-Guided Functional Profiling Overview</h3>
            
            <p>
            Experimental assignment of functions to uncharacterized enzymes in predicted 
            pathways is expensive and time-consuming. Therefore, targets that are 'worth 
            the effort' must be selected. Balskus, Huttenhower and their coworkers 
            described 'chemically guided functional profiling' (CGFP). CGFP identifies SSN 
            clusters that are abundant in
            <a href="https://www.sciencedirect.com/topics/biochemistry-genetics-and-molecular-biology/metagenome">metagenome</a>
            datasets to prioritize targets for functional characterization.
            </p>
             
            <h3>EFI-CGFP Acceptable Input</h3>
            
            <p>
            The input for EFI-CGFP is a colored sequence similarity network (SSN).
            To obtain SSNs compatible with EFI-CGFP analysis, users need to be familiar 
            with both EFI-EST
            (<a href="../efi-est/">https://efi.igb.illinois.edu/efi-est/</a>)
            to generate SSNs for protein families, and Cytoscape
            (<a href="http://www.cytoscape.org/">http://www.cytoscape.org/</a>) to visualize, 
            analyze, and edit SSNs. Users should also be familiar with the EFI-GNT web tool 
            (<a href="../efi-gnt/">https://efi.igb.illinois.edu/efi-gnt/</a>)
            that colors SSNs, and collects, analyzes, and represents genome neighborhoods for bacterial and fungal 
            sequences in SSN clusters.
            </p>
            
            <h3>Principle of CGFP Analysis</h3>
             
            <p>
            EFI-CGFP uses the ShortBRED software package developed by Huttenhower and 
            colleagues in two successive steps: 1) <b>identify</b> sequence markers that are 
            unique to members of families in the input SSN that are identified by ShortBRED 
            and share 85% sequence identity using the CD-HIT algorithm (CD-HIT 85 clusters) 
            and 2) <b>quantify</b> the marker abundances in metagenome datasets and then map these 
            to the SSN clusters. 
            </p>
            
            <h3>EFI-CGFP Output</h3>
            
            <p>
            When the "Identify" step has been performed, several files are available. They 
            include: a SSN enhanced with the markers that have been identified and their 
            type as node attributes, additional files that describe the markers and
            the ShortBRED families that were used to identify them.
            </p>
            
            <p>
            After the "quantify" step has been performed, heatmaps summarizing the 
            quantification of metagenome hits per SSN clusters are available. Several 
            additional files are provided: the SSN enhanced with metagenome hits 
            that have been identified and quantification results given in abundance within
            metagenomes, per protein and per cluster.
            </p>
            
            <h3>Recommended Reading</h3>
            
            <p>
            R&eacute;mi Zallot, Nils Oberg, John A. Gerlt, <b>"Democratized" genomic enzymology web 
            tools for functional assignment</b>, Current Opinion in Chemical Biology, Volume 
            47, 2018, Pages 77-85,
            <a href="https://doi.org/10.1016/j.cbpa.2018.09.009">https://doi.org/10.1016/j.cbpa.2018.09.009</a>
            </p>
            
            <p>
            John A. Gerlt,
            <b>Genomic enzymology: Web tools for leveraging protein family sequence–function space and genome context to discover novel functions</b>,
            Biochemistry, 2017 - ACS Publications
            </p>

            <center><a href="tutorial_intro.php"><button class="light">Continue Tutorial</button></a></center>
        </div>

        <div id="example" class="tab">
            <p>
                This example recreates the CGFP analysis for the GRE family (IPR004184) as it was initially described by
                Levin, et al (2017; full reference below).
            </p>

            <p>
                The SSN was generated on EFI-EST with 
                InterPro 71 and UniProt 2018_10, with UniRef90 seed sequences.
                An alignment score of 300 and a minimum length filter of 500 AA was applied.
                As required, the obtained SSN was colored using the EFI-EST Color SSN utility prior to submission
                to EFI-CGFP for analysis.
            </p>
            <?php $HeatmapWidth = 860; ?>
            <?php $JobName = "Example"; ?>
            <?php include("stepe_example.php"); ?>
        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->

<div id="cancel-confirm" title="Cancel the job?" style="display: none">
    <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
    All progress will be lost.
    </p>    
</div>

<div id="archive-confirm" title="Archive the job?" style="display: none">
    <p>
    <span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
    This job will be permanently removed from your list of jobs.
    </p>    
</div>

<div align="center">
    <p>
    UniProt Version: <b><?php echo settings::get_uniprot_version(); ?></b><br>
    </p>
</div>

<script>
    $(document).ready(function() {
        <?php include("inc/stepe_script.inc.php"); ?>

        $("#tab-container .tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            if (curAttrValue == "#example")
                handleTabPress("#heatmap-tabs", $("#heatmap-tabs .tab-headers .active a").first());
        });

        $(".cancel-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var qid = $(this).data("quantify-id");
            if (!qid)
                qid = "";
            var requestType = "cancel";
            var jobType = "";

            $("#cancel-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Stop Job": function() {
                        requestJobUpdate(id, key, qid, requestType, jobType);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });
        });
        
        $(".archive-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var requestType = "archive";
            var qid = $(this).data("quantify-id");
            if (!qid)
                qid = "";
            var jobType = "";

            $("#archive-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Archive Job": function() {
                        requestJobUpdate(id, key, qid, requestType, jobType);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });
        });
        
        $(".option-panels > div").accordion({
            heightStyle: "content",
            collapsible: true,
            active: false,
        });
        $("#seq-len-input").accordion("option", "active", 0);
        $(".tabs").tabs();

    });

    function submitJob() {
        var estId = <?php echo $est_id; ?>;
        var estKey = "<?php echo $est_key; ?>";
        if (estId && estKey)
            submitFromESTJob(estId, estKey);
        else
            uploadInitialSSNFile();
    }
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>


<?php

include_once("tutorial/refs.inc.php");

echo "<hr>\n";

require_once("inc/footer.inc.php");


?>

