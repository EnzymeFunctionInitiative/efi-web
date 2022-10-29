<?php
require_once(__DIR__ . "/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\ui;
use \efi\taxonomy\taxonomy_job_list_ui;


function output_option_b($use_advanced_options, $db_modules, $user_email, $example_fn = false) {
    $example_fn = $example_fn === false ? function(){} : $example_fn;

    $tax_filt_text = <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the UniProt IDs in the sunburst to these taxonomy groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The UniProt IDs also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>
TEXT;
?>
        <div id="optionBtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for families. 
            </p>

<p>
The UniProt sequences for a list of Pfam families, InterPro families, and/or 
Pfam clans are retrieved.  UniProt IDs are used to generate the sunburst.  For 
UniRef90 and UniRef50 clusters, the UniProt IDs are retrieved using the lookup 
table provided by UniProt/UniRef.  
</p>

            <form name="optionBform" id="optionBform" method="post" action="">
                <?php echo add_family_input_option_family_only("optb", false)[0]; ?>

                <div class="option-panels">
                    <div class="initial-open">
                        <?php echo add_fragment_option("optb")[0]; ?>
                    </div>
                    <div>
                        <?php echo add_taxonomy_filter("optb", $tax_filt_text)[0]; ?>
                    </div>
                    <?php if ($use_advanced_options) { ?>
                    <div>
                        <?php echo add_length_filter("optb")[0]; ?>
                    </div>
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

    $tax_filt_text = <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the UniProt IDs in the sunburst to these taxonomy groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The UniProt IDs also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other.  
</p>
TEXT;
?>
        <div id="optionCtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for FASTA files. 
            </p>

<p>
The input is a list of FASTA-formatted sequences in which the headers contain a 
UniProt ID. The UniProt ID is required because it is used to retrieve the 
taxonomy from the UniProt database (FASTA header "reading"). 
</p>

<p>
The UniProt IDs for the family members are retrieved; these are used to 
calculate the sunburst.   
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
                    <div class="initial-open">
                        <?php echo add_fragment_option("optc")[0] ?>
                    </div>
                    <div>
                        <?php echo add_taxonomy_filter("optc", $tax_filt_text)[0] ?>
                    </div>
                    <div>
                        <?php echo add_family_filter("optc")[0]; ?>
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

    $tax_filt_text = <<<TEXT
<p>
From preselected conditions, the user can select "Bacteria, Archaea, Fungi", 
"Eukaryota, no Fungi", "Fungi", "Viruses", "Bacteria", "Eukaryota", or 
"Archaea" to restrict the UniProt IDs in the sunburst to these taxonomy groups. 
</p>

<p>
"Bacteria, Archaea, Fungi", "Bacteria", "Archaea", and "Fungi" select organisms 
that may provide genome context (gene clusters/operons) useful for inferring 
functions. 
</p>

<p>
The UniProt IDs also can be restricted to taxonomy categories within the 
Superkingdom, Kingdom, Phylum, Class, Order, Family, Genus, and Species ranks. 
Multiple conditions are combined to be a union of each other. 
</p>
TEXT;

    $fam_filt_text = <<<TEXT
<p>
The selected sequences from the UniRef90 and UniRef90 databases are UniRef90 
and UniRef50 clusters for which the cluster ID ("representative sequence") 
matches the specified families. The UniProt members in these UniRef90 and 
Uni/Ref50 clusters that do not match the specified families are removed from 
the clusters.
</p>
TEXT;

?>
        <div id="optionDtab" class="ui-tabs-panel ui-widget-content">
            <p class="p-heading">
Retrieve taxonomy for accession IDs. 
            </p>

<p>
The input is a list of UniProt, UniRef90 cluster or UniRef50 cluster IDs. For 
the UniRef90 and UniRef50 clusters, the UniProt IDs in the clusters are 
retrieved using the lookup table provided by UniProt/UniRef. The UniProt IDs 
are used to generate the sunburst. 
</p>

<p>
The list of UniRef90 or UniRef50 cluster IDs may have been obtained from a 
Families option (Option B) EFI-EST SSN using the Color SSN or Cluster Analysis 
utility. If so, the UniRef90 or UniRef50 clusters in the SSN were filtered to 
remove UniProt IDs that are not members of the selected families (and, if 
specified, taxonomy categories).  However, because the Taxonomy Tool retrieves 
the UniProt IDs for input UniRef90 and UniRef50 clusters from the lookup table 
provided by UniProt/UniRef, the UniProt IDs that were removed in the Option B 
job will be included in the sunburst if they are not removed. 
</p>

<p>
Or, the list of UniRef90 or UniRef50 cluster IDs may have been obtained from 
the Taxonomy Tool (Families and Accession IDs options). The UniProt IDs 
identified for the input clusters also will contain the complete set of UniProt 
IDs provided by UniProt/UniRef, so these will be included in the sunburst if 
they are not removed.
</p>

<p>
In both cases, the user should (must!) apply Filter by Family and Filter by 
Taxonomy to include UniProt IDs from specific families and/or taxonomy 
categories in the sunburst. 
</p>

            <form name="optionDform" id="optionDform" method="post" action="">
                <div class="tabs tabs-efihdr" id="optionD-src-tabs">
                    <ul class="tab-headers">
                        <li class="ui-tabs-active"><a href="#optionD-source-uniprot">Use UniProt IDs</a></li>
                        <li><a href="#optionD-source-uniref">Use UniRef50 or UniRef90 Cluster IDs</a></li>
                    </ul>
                    <div class="tab-content" style="min-height: 250px">
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
                        <div id="optionD-source-uniref" class="ui-tabs-panel ui-widget-content">
                            <p>
                            Input a list of UniRef50 or UniRef90 cluster accession IDs, or upload a text
                            file.
                            </p>
                            <div class="primary-input">
                                <div class="secondary-name">
                                    Accession IDs:
                                </div>
                                <textarea id="accession-input-uniref" name="accession-input-uniref"></textarea>
                                <div>
<?php echo ui::make_upload_box("Accession ID File:", "accession-file-uniref", "progress-bar-accession-uniref", "progress-num-accession-uniref"); ?>
                                </div>
                                <div id="accession-seq-type-container" style="margin-top:15px">
                                    <span class="input-name">Input accession IDs are:</span>
                                    <select id="accession-seq-type">
                                        <option value="uniref90">UniRef90 cluster IDs</option>
                                        <option value="uniref50">UniRef50 cluster IDs</option>
                                    </select>
                                    <a class="question" title="
                                        The list of sequences that is put into
                                        the tool will be end up being the node IDs, and node attributes with the UniRef clusters
                                        will be included in the output SSN.">?</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="option-panels">
                    <div class="initial-open">
                        <?php echo add_fragment_option("optd")[0]; ?>
                    </div>
                    <div class="initial-open">
                        <?php echo add_taxonomy_filter("optd", $tax_filt_text)[0] ?>
                    </div>
                    <div>
                        <?php echo add_family_filter("optd", $fam_filt_text)[0]; ?>
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


