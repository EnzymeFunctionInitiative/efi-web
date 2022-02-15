<?php
require_once(__DIR__ . "/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\ui;
use \efi\taxonomy\taxonomy_job_list_ui;


function output_option_b($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $example_fn = $example_fn === false ? function(){} : $example_fn;
?>
        <div id="optionBtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for families. 
            </p>

<p>The sequences from input Pfam families, InterPro families/domains, and/or 
Pfam clans (superfamilies) are retrieved. </p>

<p>The taxonomic distribution of the sequences is displayed as a "sunburst" in 
which the levels of classification (superkingdom, kingdom, phylum, class, 
order, family, genus, species) displayed radially, with superkingdom at the 
center and species in the outermost ring. </p>

<p>The sunburst is interactive, providing the ability to zoom to a selected 
taxonomic level.   </p>

<p>The number of sequences at the selected level is displayed.  The 
UniProt/UniRef90/UniRef50 IDs and/or UniProt/UniRef90/UniRef50 FASTA-formatted 
sequences at the selected level can be downloaded. </p>

            </p>

            <form name="optionBform" id="optionBform" method="post" action="">
                <?php echo add_family_input_option_family_only("optb", false)[0]; ?>

                <div class="option-panels">
                    <div>
                        <?php echo add_taxonomy_filter("optb")[0] ?>
                    </div>
                    <div>
                        <?php echo add_fragment_option("optb")[0] ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optb", $db_modules, get_advanced_seq_html("optb"))[0]; ?>
                    </div>
                    <?php } ?>
                </div>

                <?php echo add_submit_html("optb", "optBoutputIds", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_option_c($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $show_example = $example_fn !== false;
    $example_fn = $example_fn === false ? function(){} : $example_fn;
?>
        <div id="optionCtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for FASTA files. 
            </p>

<p>Input a list of protein sequences in FASTA format or upload a 
FASTA-formatted sequence file. </p>

<p>The FASTA headers are necessarily “read” to identify the UniProt accession 
ID so the taxonomic classification can be retrieved.   </p>

<p>The taxonomic distribution of the sequences is displayed as a "sunburst" in 
which the levels of classification (superkingdom, kingdom, phylum, class, 
order, family, genus, species) displayed radially, with superkingdom at the 
center and species in the outermost ring. </p>

<p>The sunburst is interactive, providing the ability to zoom to the selected 
taxonomic level. </p>

<p>The number of sequences at the selected level is displayed.  The UniProt IDs 
and their FASTA-formatted sequences at the specified level can be downloaded. 
</p>

            
            <form name="optionCform" id="optionCform" method="post" action="">
                <div class="primary-input">
                    <div class="secondary-name">
                        Sequences:
                    </div>
                    <textarea id="fasta-input" name="fasta-input"></textarea>
                    <?php echo ui::make_upload_box("FASTA File:", "fasta-file", "progress-bar-fasta", "progress-num-fasta"); ?>
                </div>

                <div class="option-panels">
                    <div>
                        <?php echo add_taxonomy_filter("optc")[0] ?>
                    </div>
                    <div>
                        <?php echo add_fragment_option("optc")[0] ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optc", $db_modules)[0]; ?>
                    </div>
                    <?php } ?>
                </div>

                <?php echo add_submit_html("optc", "optCoutputIds", $user_email)[0]; ?>
            </form>
        </div>
<?php
}

function output_option_d($use_advanced_options, $db_modules, $user_email, $show_example = false) {
?>
        <div id="optionDtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for accession IDs. 
            </p>

<p>Input a list of UniProt accession ID or upload a text file. </p>

<p>The taxonomic distribution of the sequences is displayed as a "sunburst" in 
which the levels of classification (superkingdom, kingdom, phylum, class, 
order, family, genus, species) displayed radially, with superkingdom at the 
center and species in the outermost ring. </p>

<p>The sunburst is interactive, providing the ability to zoom to the selected 
taxonomic level. </p>

<p>The number of sequences at the selected level is displayed.  The UniProt IDs 
and their FASTA-formatted sequences at the specified level can be downloaded. 
</p>


            <form name="optionDform" id="optionDform" method="post" action="">
                <div id="optionD-source-uniprot" class="tab ui-tabs-active">
                    <div class="primary-input">
                        <div class="secondary-name">
                            Accession IDs:
                        </div>
                        <textarea id="accession-input-uniprot" name="accession-input-uniprot"></textarea>
                        <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniprot", "progress-bar-accession-uniprot", "progress-num-accession-uniprot"); ?>
                        </div>
                    </div>
                </div>

                <div class="option-panels">
                    <div>
                        <?php echo add_taxonomy_filter("optd")[0] ?>
                    </div>
                    <div>
                        <?php echo add_fragment_option("optd")[0] ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_dev_site_option("optd", $db_modules)[0]; ?>
                    </div>
                    <?php } ?>
                </div>

                <?php echo add_submit_html("optd", "optDoutputIds", $user_email)[0]; ?>
            </form>
        </div>
<?php
}


function output_tab_page_header($show_jobs_tab, $show_tutorial, $selected_tab = "", $class_fn = false, $url_fn = false) {
    $ul_class = $class_fn !== false ? $class_fn("ul") : "ui-tabs-nav ui-widget-header";
    $active_class = $class_fn !== false ? $class_fn("active") : "ui-tabs-active";
    if ($url_fn === false) {
        $url_fn = function($id) {
            return "#$id";
        };
    }
?>
    <ul class="<?php echo $ul_class; ?>">
<?php if ($show_jobs_tab) { ?>
        <li <?php echo (!$selected_tab              ? "class=\"$active_class\"" : ""); ?>"><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li <?php echo ($selected_tab == "option_b" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionBtab"); ?>" title="Option B">Families</a></li> <!-- Pfam and/or InterPro families</a></li>-->
        <li <?php echo ($selected_tab == "option_c" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionCtab"); ?>" title="Option C">FASTA</a></li>
        <li <?php echo ($selected_tab == "option_d" ? "class=\"$active_class\"" : ""); ?>><a href="<?php echo $url_fn("optionDtab"); ?>" title="Option D">Accession IDs</a></li>
    </ul>
<?php
}

function output_tab_page_start($class_fn = false, $id = "main-tabs") {
    $tab_class = $class_fn !== false ? $class_fn("tab-container") : "tabs-efihdr ui-tabs ui-widget-content";
    echo <<<HTML
<div class="$tab_class" id="$id"> <!-- style="display:none">-->
HTML;
}

function output_tab_page_end() {
    echo <<<HTML
</div> <!-- tabs -->
HTML;
}

function output_tab_page($db, $show_jobs_tab, $jobs, $tjobs, $use_advanced_options, $db_modules, $user_email, $show_tutorial, $example_fn = false, $show_all_ids = false) {

    $sel_tab = "";

    output_tab_page_start();
    output_tab_page_header($show_jobs_tab, $show_tutorial, $sel_tab);
?>
    <div>
<?php if ($show_jobs_tab) { ?>
        <div id="jobs" class="ui-tabs-panel ui-widget-content">

            <h4>Jobs</h4>
<?php 
        $show_archive = true;

        $job_ui = new taxonomy_job_list_ui($db, $user_email, false);
        echo $job_ui->output_job_list($jobs, "Job Name");
    
?>
         </div>
<?php
    }

    output_option_b($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_option_c($use_advanced_options, $db_modules, $user_email, $example_fn);
    output_option_d($use_advanced_options, $db_modules, $user_email, $example_fn);
?>

    </div> <!-- tab-content -->
<?php
    output_tab_page_end();
}


