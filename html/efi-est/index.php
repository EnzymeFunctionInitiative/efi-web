<?php
require_once(__DIR__."/../../init.php");

require_once(__BASE_DIR__."/includes/login_check.inc.php");

use \efi\global_settings;
use \efi\global_header;
use \efi\user_auth;
use \efi\est\settings;
use \efi\est\functions;
use \efi\est\user_jobs;
use \efi\sanitize;


$user_email = "Enter your e-mail address";

$show_jobs_tab = false;
$jobs = array();
$tjobs = array(); // training jobs
$IsAdminUser = false;
$sort_method = user_jobs::SORT_TIME_COMPLETED;
if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $sb = sanitize::get_sanitize_num("sb");
    if (isset($sb)) {
        if ($sb == user_jobs::SORT_TIME_ACTIVITY) {
            $sort_method = user_jobs::SORT_TIME_ACTIVITY;
        } else if ($sb == user_jobs::SORT_ID) {
            $sort_method = user_jobs::SORT_ID;
        } else if ($sb == user_jobs::SORT_ID_REVERSE) {
            $sort_method = user_jobs::SORT_ID_REVERSE;
        }
    }
    $user_jobs = new user_jobs(null, array("TAXONOMY"));
    $user_jobs->load_jobs($db, user_auth::get_user_token(), $sort_method);
    $jobs = $user_jobs->get_jobs();
    $tjobs = $user_jobs->get_training_jobs();
    $user_email = $user_jobs->get_email();
    $IsAdminUser = $user_jobs->is_admin();
    $show_jobs_tab = has_jobs($jobs) || has_jobs($tjobs);
}

$max_full_family = settings::get_maximum_full_family_count(1);

$use_advanced_options = global_settings::advanced_options_enabled();

$db_modules = global_settings::get_database_modules();

$update_msg = settings::get_update_message(); 

$js_version = settings::get_js_version();

$ShowCitation = true;
$IncludeSubmitJs = true;
$JsAdditional = array('<script src="js/mem_calc.js?v='.$js_version.'" type="text/javascript"></script>');
require_once(__DIR__."/inc/header.inc.php");

?>


<p></p>
<p>
A sequence similarity network (SSN) allows for visualization of relationships among 
protein sequences. In SSNs, the most related proteins are grouped together in 
clusters.
The Enzyme Similarity Tool (EFI-EST) makes it possible to easily generate SSNs.
<a href="http://www.cytoscape.org/">Cytoscape</a> is used to explore SSNs.
</p>


<?php if ($update_msg) { ?>
<div id="update-message" class="update-message">
<?php echo $update_msg; ?>
<div class="new-feature"></div>
</div>
<?php } ?>


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

<p><?php echo functions::get_update_message(); ?></p>

<?php
include_once("inc/index_helpers.inc.php");
include_once("inc/index_sections.inc.php");
$show_example = false;
$show_tutorial = false;
output_tab_page($db, $show_jobs_tab, $jobs, $tjobs, $use_advanced_options, $db_modules, $user_email, $show_tutorial, $sort_method, $show_example);
?>

<div align="center">
    <p>
    UniProt Version: <b><?php echo global_settings::get_uniprot_version(); ?></b><br>
    InterPro Version: <b><?php echo global_settings::get_interpro_version(); ?></b><br>
    </p>
</div>

<script>
    var AutoCheckedUniRef = false;
    var FamilySizeOk = true;

    $(document).ready(function() {
        $("#main-tabs").tabs();
        $("#utility-tabs").tabs();
        $("#optionD-src-tabs").tabs();

        resetForms();

<?php
    $option_d_source = "uniprot";
    $mode_data = check_for_taxonomy_input($db);
    if (!empty($mode_data))
        $option_d_source = $mode_data["id_type"];
?>
        $("#optionD-src-tabs").data("source", "<?php echo $option_d_source; ?>");

        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            if (curAttrValue.substr(0, 15) == "#optionD-source") {
                var source = curAttrValue.substr(16);
                $("#optionD-src-tabs").data("source", source);
            }
        });

        var taxSearchApp = "<?php echo $SiteUrlPrefix; ?>/vendor/efiillinois/taxonomy/php/get_tax_typeahead.php";
        var taxContainerFn = function(opt) { return "#taxonomy-" + opt + "-container"; };
        var taxonomyApp = new AppTF(taxContainerFn, taxSearchApp);

        var idData = setupFamilyInputs(["opta", "optb", "optc", "optd", "opte", "opt_tax"]);
        var familySizeHelper = new FamilySizeHelper();
        familySizeHelper.setupFamilyInputs(idData);

        var app = new AppEstSubmit(idData, familySizeHelper, taxonomyApp);

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
        
        setupFileInputEvents();
        setupArchiveUi();

        $("button.submit-job").click(function() {
            var obj = $(this);
            var colorSsnOption = obj.data("color-ssn-option-id");
            if (colorSsnOption) {
                app.submitColorSsnForm(colorSsnOption);
            } else {
                app.submitOptionForm(obj.data("option-id"));
            }
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

        $(".extra-ram-calc-ram-from-edges").on('input', function() {
            var edges = $(this).val();
            var ram = get_memory_size(edges, 0);
            if (ram) {
                var destId = $(this).data("dest-id");
                $("#"+destId).val(ram);
                $(".extra-ram-cb").prop("checked", true);
            }
        });
        $(".extra-ram-val").on("input", function() {
            $(".extra-ram-cb").prop("checked", true);
        });


        setupTaxonomyUi(taxonomyApp);
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

<?php require_once(__DIR__."/inc/footer.inc.php"); ?>

<?php


function is_interactive() {
    return true;
}
function get_default_fraction() {
    return functions::get_fraction();
}
function get_default_evalue() {
    return settings::get_evalue();
}
function get_max_full_family_count() {
    return settings::get_maximum_full_family_count();
}
function get_max_blast_seq() {
    return settings::get_max_blast_seq();
}
function get_default_blast_seq() {
    return settings::get_default_blast_seq();
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
HTML;
    $html .= add_extra_ram_option($option_id);
    $html .= <<<HTML
    </div>
    <div>
        <span class="input-name">
            CPUx2:
        </span><span class="input-field">
            <input type="checkbox" id="cpu-x2-$option_id" name="cpu-x2-$option_id" value="1">
            <label for="cpu-x2-$option_id">Check to use two times the number of processors (default: off)</label>
        </span>
    </div>
    <div>
        <span class="input-name">
            Extra Memory:
        </span><span class="input-field">
            <input type="checkbox" id="large-mem-$option_id" name="large-mem-$option_id" value="1">
            <label for="large-mem-$option_id">Check to use extra memory (default: off)</label>
        </span>
    </div>
HTML;
    list($db_html) = make_db_mod_option($db_modules, $option_id);
    $html .= $db_html;
//    if ($option_id == "optc") {
//        $html .= <<<HTML
//    <div>
//        <span class="input-name">
//            SSN FASTA:
//        </span><span class="input-field">
//            <input type="checkbox" id="include-all-seq-$option_id" name="include-all-seq-$option_id" value="1" />
//            <label for="include-all-seq-$option_id">Check to include all FASTA sequences in the output SSN, not just the unidentified ones.</label>
//        </span>
//    </div>
//HTML;
//    }
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


