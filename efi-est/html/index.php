<?php

require_once(__DIR__ . "/../includes/main.inc.php");
require_once(__DIR__ . "/../libs/user_jobs.class.inc.php");
require_once(__DIR__ . "/../libs/est_ui.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/includes/login_check.inc.php");
require_once(__BASE_DIR__ . "/libs/ui.class.inc.php");

$user_email = "Enter your e-mail address";

$show_jobs_tab = false;
$jobs = array();
$tjobs = array(); // training jobs
$IsAdminUser = false;
if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $user_jobs = new user_jobs();
    $user_jobs->load_jobs($db, user_auth::get_user_token());
    $jobs = $user_jobs->get_jobs();
    $tjobs = $user_jobs->get_training_jobs();
    $user_email = $user_jobs->get_email();
    $IsAdminUser = $user_jobs->is_admin();
    $show_jobs_tab = has_jobs($jobs) || has_jobs($tjobs);
}

$max_full_family = est_settings::get_maximum_full_family_count(1);

$use_advanced_options = global_settings::advanced_options_enabled();

$db_modules = global_settings::get_database_modules();

//$update_msg =
//    "The \"From the Bench\" article describing the tools and their use is available on the " . 
//    "<i class='fas fa-question'></i> <b>Training</b> page.<br>" .
//    "Access to videos about the use of Cytoscape for interacting with SSNs is also available on the same page.<br>" .
//    "We now provide the \"Fragment Option\" to exclude UniProt-defined fragments from SSNs.<br>" .
$update_msg = 
    'Cluster Analysis is a new tool that provides a color SSN as well as WebLogos, MSAs, and HMMs for the clusters.<br>' .
    'Options B and D now provide an expanded "Family Domain Boundary Option" to generate SSNs for sequences N- or C-terminal to the specified family domain.<br>' .
    'A list of publications citing the tools is available on the <i class="fas fa-question"></i> Training page.<br>' .
    //"Sequence regions adjacent to the domain can be selected in the domain option for Families and Accession IDs.<br>" .
    "<small>" . functions::get_update_message() . "</small>";


$IncludeSubmitJs = true;
require_once "inc/header.inc.php";

$sort_by_group = true;
if (isset($_GET["sb"]) && $_GET["sb"] == 1) {
    $sort_by_group = false;

    $sort_fn = function($a, $b) use ($jobs) {
        if (isset($jobs["date_order"][$a]) && isset($jobs["date_order"][$b])) {
            $tm1 = strtotime($jobs["date_order"][$a]);
            $tm2 = strtotime($jobs["date_order"][$b]);
            return $tm1 > $tm2 ? -1 : ($tm1 < $tm2 ? 1 : 0);
        } else {
            return $a < $b ? 1 : ($a > $b ? -1 : 0);
        }
    };

    usort($jobs["order"], $sort_fn);
}


?>


<p></p>
<p>
A sequence similarity network (SSN) allows for visualization of relationships among 
protein sequences. In SSNs, the most related proteins are grouped together in 
clusters.
The Enzyme Similarity Tool (EFI-EST) makes it possible to easily generate SSNs.
<a href="http://www.cytoscape.org/">Cytoscape</a> is used to explore SSNs.
</p>


<div id="update-message" class="update-message">
<div class="new-feature"></div>
<?php if (isset($update_msg)) echo $update_msg; ?>
</div>


<p>
A listing of new features and other information pertaining to EST is available on the
<a href="notes.php">release notes</a> page.
</p>

<p>
<a href="https://www.ebi.ac.uk/interpro/search/sequence-search">InterProScan sequence search</a> can be used to
find matches within the InterPro database for a given sequence.
</p>

<p>
Information on Pfam families and clans and InterPro family sizes is available on
the <a href="family_list.php">Family Information page</a>.
</p>

<?php
include_once("inc/index_helpers.inc.php");
include_once("inc/index_sections.inc.php");
$show_example = false;
$show_tutorial = false;
output_tab_page($db, $show_jobs_tab, $jobs, $tjobs, $use_advanced_options, $db_modules, $user_email, $show_tutorial, $show_example);
?>

<div align="center">
    <p>
    UniProt Version: <b><?php echo global_settings::get_uniprot_version(); ?></b><br>
    InterPro Version: <b><?php echo global_settings::get_interpro_version(); ?></b><br>
    </p>
</div>

<script>
    const SORT_DATE_DESC = 1;
    const SORT_DATE_GROUP = 3;
    var AutoCheckedUniRef = false;
    var FamilySizeOk = true;
    var familySizeHelper = new FamilySizeHelper();

    var optAinputIds = getInputIds("opta");
    var optAoutputIds = getOutputIds("opta");
    var optBinputIds = getInputIds("optb");
    var optBoutputIds = getOutputIds("optb");
    var optCinputIds = getInputIds("optc");
    var optCoutputIds = getOutputIds("optc");
    var optDinputIds = getInputIds("optd");
    var optDoutputIds = getOutputIds("optd");
    var optEinputIds = getInputIds("opte");
    var optEoutputIds = getOutputIds("opte");

    var sortMethod = <?php echo $sort_by_group ? "SORT_DATE_GROUP" : "SORT_DATE_DESC"; ?>;

    $(document).ready(function() {
        $("#main-tabs").tabs();
        $("#optionD-src-tabs").tabs();

        resetForms();
        $("#optionD-src-tabs").data("source", "uniprot");

        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            if (curAttrValue.substr(0, 15) == "#optionD-source") {
                var source = curAttrValue.substr(16);
                $("#optionD-src-tabs").data("source", source);
            }
        });

        familySizeHelper.setupFamilyInput("opta", optAinputIds, optAoutputIds);
        familySizeHelper.setupFamilyInput("optb", optBinputIds, optBoutputIds);
        familySizeHelper.setupFamilyInput("optc", optCinputIds, optCoutputIds);
        familySizeHelper.setupFamilyInput("optd", optDinputIds, optDoutputIds);
        familySizeHelper.setupFamilyInput("opte", optEinputIds, optEoutputIds);

        $(".cb-use-uniref").click(function() {
            if (this.checked) {
//                $(".fraction").val("1").prop("disabled", true);
                $(".fraction").val("1");
            } else {
//                $(".fraction").prop("disabled", false);
            }
        });

        $(".fraction").on("input", function() {
            if (this.value != "1") {
                $(".cb-use-uniref").prop("checked", false);
            }
        });
        
        $(".archive-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var aid = $(this).data("analysis-id");
            var requestType = "archive";
            var jobType = "generate";

            if (!aid)
                aid = 0;

            $("#archive-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Archive Job": function() {
                        requestJobUpdate(id, aid, key, requestType, jobType);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });
        });

        $("#fasta-file").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optc").val())
                $("#job-name-optc").val(fileName);
        });

        $("#accession-file-uniref").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName) {
                if (fileName.includes("UniRef50"))
                    $("#accession-seq-type").val("uniref50");
                else if (fileName.includes("UniRef90"))
                    $("#accession-seq-type").val("uniref90");
                else if (fileName.includes("UniProt"))
                    $("#accession-seq-type").val("uniprot");
            }
            if (fileName && !$("#job-name-optd").val())
                $("#job-name-optd").val(fileName);
        });

        $("#accession-file-uniprot").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optd").val())
                $("#job-name-optd").val(fileName);
        });


        var updateSortIcon = function() {
            var sortIcon = sortMethod == SORT_DATE_DESC ? "<i class='fas fa-chevron-down'></i>" : "<i class='fas fa-list-alt'></i>";
            $("#sort-jobs-toggle").html(sortIcon);
        };
        var toggleSortIcon = function() {
            sortMethod = sortMethod == SORT_DATE_DESC ? SORT_DATE_GROUP : SORT_DATE_DESC;
            updateSortIcon();
        };
        updateSortIcon();
        $("#sort-jobs-toggle").click(function() {
            toggleSortIcon();
            window.location.replace("<?php echo $_SERVER['PHP_SELF']; ?>" + (sortMethod == SORT_DATE_DESC ? "?sb=1" : ""));
        });

        $("#domain-optd").change(function() {
            var status = $(this).prop("checked");
            var disableFamilyInput = status;
            $("#domain-family-optd").prop("disabled", !status);
            $(".domain-region-optd").prop("disabled", !status);
            familySizeHelper.setDisabledState("optd", disableFamilyInput);
            if (disableFamilyInput)
                familySizeHelper.resetInput("optd");
            if (!status)
                $(".domain-region-optd").prop("checked", false);
            else
                $("#domain-region-domain-optd").prop("checked", true)
        });
        $("#domain-optb").change(function() {
            var status = $(this).prop("checked");
            $(".domain-region-optb").prop("disabled", !status);
            if (!status)
                $(".domain-region-optb").prop("checked", false);
            else
                $("#domain-region-domain-optb").prop("checked", true)
        });

        $(".option-panels > div").accordion({
            heightStyle: "content",
                collapsible: true,
                active: false,
        });
        
        $(".initial-open").accordion("option", {active: 0});
    });

    function resetForms() {
        for (i = 0; i < document.forms.length; i++) {
            document.forms[i].reset();
        }
        document.getElementById("domain-family-optd").disabled = true;
<?php if ($use_advanced_options) { ?>
        document.getElementById("domain-region-nterminal-optd").disabled = true;
        document.getElementById("domain-region-domain-optd").disabled = true;
        document.getElementById("domain-region-cterminal-optd").disabled = true;
        document.getElementById("domain-region-nterminal-optb").disabled = true;
        document.getElementById("domain-region-domain-optb").disabled = true;
        document.getElementById("domain-region-cterminal-optb").disabled = true;
<?php } ?>
    }
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<div id="family-warning" class="hidden" title="UniRef Family Warning">
<div style="color:red;" id="family-warning-size-info">
The family(ies) selected has <span id="family-warning-total-size"> </span> proteins&mdash;this is greater than
the maximum allowed (<?php echo $max_full_family; ?>). To reduce computing time and the size of
output SSN, UniRef<span class="family-warning-uniref">90</span> cluster ID sequences will automatically be used.
</div>

<p>In UniRef<span class="family-warning-uniref">90</span>, sequences that share
&ge;<span class="family-warning-uniref">90</span>% sequence identity over 80% of the sequence 
length are grouped together and represented by an accession ID known as the cluster ID. The output 
SSN is equivalent a to <span class="family-warning-uniref">90</span>% Representative Node
Network with each node corresponding to a UniRef cluster ID, and for which the node attribute
"UniRef<span class="family-warning-uniref">90</span> Cluster IDs" lists 
all the sequences represented by a node. UniRef<span class="family-warning-uniref">90</span>
SSNs are compatible with the Color SSN utility as well as the EFI-GNT tool.
</p>

<p>Press Ok to continue with UniRef<span class="family-warning-uniref">90</span>.</p>

<?php /* ?>
    The family(ies) selected has <span id="family-warning-total-size"> </span> 
    proteins<span id="family-warning-fraction-size"></span>, which is greater
    than the maximum allowed (<?php echo $max_full_family; ?>) for full family
    inclusion.  UniRef<span class="family-warning-uniref">90</span> cluster IDs will automatically be 
    used instead of the full number of proteins in the family(ies).  Press OK to continue with UniRef<span class="family-warning-uniref">90</span>
    or Cancel to enter a different family, or option.
<?php */ ?>
</div>

<div id="archive-confirm" title="Archive the job?" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
This job will be permanently removed from your list of jobs.
</p>    
</div>

<?php require_once('inc/footer.inc.php'); ?>

<?php


function is_interactive() {
    return true;
}
function get_default_fraction() {
    return functions::get_fraction();
}
function get_default_evalue() {
    return est_settings::get_evalue();
}
function get_max_full_family_count() {
    return est_settings::get_maximum_full_family_count();
}
function get_max_blast_seq() {
    return est_settings::get_max_blast_seq();
}
function get_default_blast_seq() {
    return est_settings::get_default_blast_seq();
}
function make_db_mod_option($db_modules, $option) {
    if (count($db_modules) < 2)
        return "";

    $id = "db-mod-$option";
    $html = <<<HTML
<div>
    <span class="input-name">
        Database version:
    </span><span class="input-field">
        <select name="$id" id="$id">
HTML;

    foreach ($db_modules as $mod) {
        $mod_name = $mod[1];
        $html .= "            <option value=\"$mod_name\">$mod_name</option>\n";
    }

    $html .= <<<HTML
        </select>
    </span>
</div>
HTML;
    return array($html);
}
function add_dev_site_option($option_id, $db_modules, $extra_html = "") {
    $html = <<<HTML
<h3>Dev Site Options</h3>
<div>
    <div>
        <span class="input-name">
            CPUx2:
        </span><span class="input-field">
            <input type="checkbox" id="cpu-x2-$option_id" name="cpu-x2-$option_id" value="1">
            <label for="cpu-x2-$option_id">Check to use two times the number of processors (default: off)</label>
        </span>
    </div>
HTML;
    list($db_html) = make_db_mod_option($db_modules, $option_id);
    $html .= $db_html;
    if ($option_id == "optc") {
        $html .= <<<HTML
    <div>
        <span class="input-name">
            SSN FASTA:
        </span><span class="input-field">
            <input type="checkbox" id="include-all-seq-$option_id" name="include-all-seq-$option_id" value="1" />
            <label for="include-all-seq-$option_id">Check to include all FASTA sequences in the output SSN, not just the unidentified ones.</label>
        </span>
    </div>
HTML;
    }
    if (functions::get_program_selection_enabled()) {
        $html .= <<<HTML
    <div>
        <span class="input-name">
            Select Program to use:
        </span><span class="input-field">
        <select name="program-$option_id" id="program-$option_id">
            <option value="BLAST">BLAST</option>
            <option value="BLAST+">BLAST+</option>
            <option selected="selected" value="DIAMOND">Diamond</option>
        	<option value="DIAMONDSENSITIVE">Diamond Sensitive</option>
        </select>
        </span>
    </div>
HTML;
    }
    $html .= <<<HTML
    $extra_html
</div>
HTML;
    return array($html);
}

function has_jobs($jobs) {
    return (isset($jobs["order"]) && count($jobs["order"]) > 0);
}



?>

