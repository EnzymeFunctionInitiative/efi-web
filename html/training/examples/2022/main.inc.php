
<div class="">

<div style="text-align: center; font-size: 1.5em; font-weight: bold;">
SSNs for Taxonomy Categories:   Taxonomy Tool and Filter by Taxonomy
</div>
<div>
<a href="examples/2022/nov2022overview_lg.png"><img src="examples/2022/nov2022overview.png" width="90%" /></a>
</div>

<p>
As the UniProtKB database and the protein families identified by Pfam and 
InterPro continue to increase in size, analyses of sequence-function space in 
SSNs is increasingly difficult.  We have developed the ability to generate SSNs 
from taxonomy categories, allowing higher resolution analyses of focused 
regions of sequence-function space using UniProt IDs instead of UniRef90 
clusters or UniRef90 clusters instead of UniRef50 clusters.  
</p>

<p>
A manuscript has been submitted that describes the Taxonomy Tool and Filter by 
Taxonomy feature that we have developed.  The manuscript also includes examples 
of the use of both the Taxonomy Tool and Filter by Taxonomy with analyses of 
both the glycyl radical enzyme superfamily (GRE; IPR004184) and the radical SAM 
superfamily (RSS).
</p>

<p>
The <a href="examples/2022/JMB_Resource_MS_Final.pdf">manuscript</a> and
<a href="examples/2022/JMB_Resource_SI_Final.pdf">supplementary figures and table</a>
are both available for download.
</p>

<p>
On this page we provide access to the EFI-EST, EFI-GNT, EFI-CGFP, and Taxonomy 
Tool jobs described in the manuscript.  
</p>

<?php

// $config is from the file we are including this from

use \efi\est\est_ui;
use \efi\gnt\gnt_ui;
use \efi\cgfp\cgfp_ui;
use \efi\training\training_ui;


echo training_ui::get_tab_header(["gre-tab" => "GRE", "rss-tab" => "RSS"], 0);

$gre_html = get_gre_html($config, 4);
$rss_html = get_rss_html($config, 4);

echo training_ui::get_tab("gre-tab", "GRE", $gre_html, 2);
echo training_ui::get_tab("rss-tab", "RSS", $rss_html, 2);

echo training_ui::get_tab_footer();


//echo "<h4>EST Jobs</h4>\n";
//$est_jobs = $config->get_est_jobs("est.jobs");
//if (isset($est_jobs["order"]) && count($est_jobs["order"]) > 0) {
//    echo est_ui::output_job_list($est_jobs, false, false, "biochem");
//}
//
//echo "<h4 style=\"margin-top:50px\">GNT Job</h4>\n";
//$gnt_jobs = $config->get_gnt_jobs("gnt.jobs");
//if (count($gnt_jobs) > 0) {
//    echo gnt_ui::output_job_list($gnt_jobs, "biochem");
//}
//
//echo "<h4 style=\"margin-top:50px\">CGFP Job</h4>\n";
//$cgfp_jobs = $config->get_cgfp_jobs("cgfp.jobs");
//if (count($cgfp_jobs) > 0) {
//    echo cgfp_ui::output_job_list($cgfp_jobs, false, "biochem");
//}

?>

    </div>
</div>


<script>
$(document).ready(function() {
    $("#jobs").tabs();
    $("#gre-tab").tabs();
    $("#rss-tab").tabs();
    $("#est-gre-1").tabs();
}).tooltip();
</script>




<?php


function get_gre_html($config) {
    $html = <<<HTML

<div><img src="examples/2022/gre_sb.png" width="90%" /></div>

HTML;

    $html .= training_ui::get_tab_header(["tax-gre" => "Tax. Tool", "est-gre-1" => "Filter by Tax. Analysis Step",
        "est-gre-2" => "Filter by Tax. Generate Step", "est-gre-3" => "Tax. Tool Transfer to Option D"]);

    /////////////////////
    // Taxonomy
    $tax_html = "";
    $tax_jobs = $config->get_est_jobs("tax.gre");
    if (isset($tax_jobs["order"]) && count($tax_jobs["order"]) > 0)
        $tax_html = est_ui::output_job_list($tax_jobs, false, false, "2022", false, true); // last true is $is_taxonomy = true

    /////////////////////
    // Version 1
    $est1_html = "";
    $est1_jobs = $config->get_est_jobs("est.gre.1");
    if (isset($est1_jobs["order"]) && count($est1_jobs["order"]) > 0)
        $est1_html = est_ui::output_job_list($est1_jobs, false, false, "2022");
    $gnt1_html = "";
    $gnt1_jobs = $config->get_gnt_jobs("gnt.gre");
    if (count($gnt1_jobs) > 0)
        $gnt1_html = gnt_ui::output_job_list($gnt1_jobs, "2022");
    $cgfp1_html = "";
    $cgfp1_jobs = $config->get_cgfp_jobs("cgfp.gre");
    if (count($cgfp1_jobs) > 0)
        $cgfp1_html = cgfp_ui::output_job_list($cgfp1_jobs, false, "2022");

    $gre2_html = training_ui::get_tab_header(["est-gre-1-est" => "EFI-EST", "est-gre-1-gnt" => "EFI-GNT", "est-gre-1-cgfp" => "EFI-CGFP"]);
    $gre2_html .= training_ui::get_tab("est-gre-1-est", "EFI-EST", $est1_html);
    $gre2_html .= training_ui::get_tab("est-gre-1-gnt", "EFI-GNT", $gnt1_html);
    $gre2_html .= training_ui::get_tab("est-gre-1-cgfp", "EFI-CGFP", $cgfp1_html);
    $gre2_html .= training_ui::get_tab_footer();

    /////////////////////
    // Version 2
    $est2_html = "";
    $est2_jobs = $config->get_est_jobs("est.gre.2");
    if (isset($est2_jobs["order"]) && count($est2_jobs["order"]) > 0)
        $est2_html = est_ui::output_job_list($est2_jobs, false, false, "2022");
    
    /////////////////////
    // Version 3
    $est3_html = "";
    $est3_jobs = $config->get_est_jobs("est.gre.3");
    if (isset($est3_jobs["order"]) && count($est3_jobs["order"]) > 0)
        $est3_html = est_ui::output_job_list($est3_jobs, false, false, "2022");

    $html .= training_ui::get_tab("tax-gre", "Taxonomy Tool", $tax_html);
    $html .= training_ui::get_tab("est-gre-1", "Filter by Taxonomy Analysis Step", $gre2_html);
    $html .= training_ui::get_tab("est-gre-2", "Filter by Taxonomy Generate Step", $est2_html);
    $html .= training_ui::get_tab("est-gre-3", "Taxonomy Tool Transfer to Option D", $est3_html);

    $html .= training_ui::get_tab_footer();

    return $html;
}


function get_rss_html($config) {
    $html = <<<HTML

<div><img src="examples/2022/rss_sb.png" width="90%" /></div>

HTML;

    $html .= training_ui::get_tab_header(["tax-rss" => "Tax. Tool", "est-rss-1" => "Filter by Tax. Generate Step",
        "est-rss-2" => "Tax. Tool Transfer to Option D"]);

    /////////////////////
    // Taxonomy
    $tax_html = "";
    $tax_jobs = $config->get_est_jobs("tax.gre");
    if (isset($tax_jobs["order"]) && count($tax_jobs["order"]) > 0)
        $tax_html = est_ui::output_job_list($tax_jobs, false, false, "2022", false, true);

    /////////////////////
    // Version 1
    $est1_html = "";
    $est1_jobs = $config->get_est_jobs("est.rss.1");
    if (isset($est1_jobs["order"]) && count($est1_jobs["order"]) > 0)
        $est1_html = est_ui::output_job_list($est1_jobs, false, false, "2022");

    /////////////////////
    // Version 2
    $est2_html = "";
    $est2_jobs = $config->get_est_jobs("est.rss.2");
    if (isset($est2_jobs["order"]) && count($est2_jobs["order"]) > 0)
        $est2_html = est_ui::output_job_list($est2_jobs, false, false, "2022");

    $html .= training_ui::get_tab("tax-rss", "Taxonomy Tool", $tax_html);
    $html .= training_ui::get_tab("est-rss-1", "Filter by Taxonomy Generate Step", $est1_html);
    $html .= training_ui::get_tab("est-rss-2", "Taxonomy Tool Transfer to Option D", $est2_html);

    $html .= training_ui::get_tab_footer();

    return $html;
}



