<?php
require_once(__DIR__."/../../init.php");

require_once(__BASE_DIR__."/includes/login_check.inc.php");

use \efi\global_settings;
use \efi\global_header;
use \efi\user_auth;
use \efi\taxonomy\taxonomy_jobs;
use \efi\taxonomy\taxonomy_ui;
use \efi\est\settings;
use \efi\est\functions;


$user_email = "Enter your e-mail address";

$show_jobs_tab = false;
$jobs = array();
$tjobs = array(); // training jobs
$IsAdminUser = false;
if (global_settings::get_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $user_jobs = new taxonomy_jobs($db);
    $user_token = user_auth::get_user_token();
    $user_jobs->load_jobs($user_token);
    $jobs = $user_jobs->get_jobs();
    $user_email = $user_jobs->get_email();
    $IsAdminUser = $user_jobs->is_admin();
    $show_jobs_tab = count($jobs) > 0;
}

$max_full_family = settings::get_maximum_full_family_count(1);

$use_advanced_options = global_settings::advanced_options_enabled();

$db_modules = global_settings::get_database_modules();


$ShowCitation = true;
$IncludeSubmitJs = true;
require_once(__DIR__."/inc/header.inc.php");

?>

<p></p>

<p>
As the UniProt database increases in size, users may encounter difficulties in 
opening and visualizing SSNs with Cytoscape (too many nodes and edges, 
insufficient RAM available).  One solution is to restrict the input sequences 
to specific taxonomic groups.  Options A, B, C, and D include a "Filter by 
Taxonomy" option so that the user can select specific taxonomic categories to 
include in their SSNs.
</p>

<p>
This utility assists the user in selecting taxonomic categories by providing a 
preview of the taxonomic distribution of input sequences (families, Option B; 
FASTA files, Option C; accession IDs, Option D) and the number of sequences at 
selected categories before the SSN is generated.    
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
    var AutoCheckedUniRef = false;
    var FamilySizeOk = true;

    $(document).ready(function() {
        $("#main-tabs").tabs();

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

        var idData = setupFamilyInputs(["optb", "optc", "optd"]);
        var familySizeHelper = new FamilySizeHelper("../efi-est/get_family_counts.php");
        familySizeHelper.setupFamilyInputs(idData);

        var app = new AppTaxSubmit(idData, taxonomyApp);

        $("#fasta-file").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optc").val())
                $("#job-name-optc").val(fileName);
        });

        $("#accession-file-uniprot").on("change", function(e) {
            var fileName = '';
            fileName = e.target.value.split( '\\' ).pop();
            if (fileName && !$("#job-name-optd").val())
                $("#job-name-optd").val(fileName);
        });

        $("button.submit-job").click(function() {
            var obj = $(this);
            var colorSsnOption = obj.data("color-ssn-option-id");
            if (colorSsnOption) {
                app.submitColorSsnForm(colorSsnOption);
            } else {
                app.submitOptionForm(obj.data("option-id"));
            }
        });

        $(".option-panels > div").accordion({
            heightStyle: "content",
                collapsible: true,
                active: false,
        });
        
        $(".initial-open").accordion("option", {active: 0});

        setupTaxonomyUi(taxonomyApp);
        setupArchiveUi();
    });

</script>

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
HTML;
    list($db_html) = make_db_mod_option($db_modules, $option_id);
    $html .= $db_html;
    $html .= <<<HTML
    $extra_html
</div>
HTML;
    return array($html);
}


