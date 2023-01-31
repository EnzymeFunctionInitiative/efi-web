
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

$gre_html = get_gre_html($config, false);
$rss_html = get_tab_html($config, "rss_sb.png", "rss");

echo training_ui::get_tab("gre-tab", "GRE", $gre_html, 2);
echo training_ui::get_tab("rss-tab", "RSS", $rss_html, 2);

echo training_ui::get_tab_footer();

?>

    </div>
</div>


<script>
$(document).ready(function() {
    $("#jobs").tabs();
    $(".option-panels > div").accordion({
        heightStyle: "content",
            collapsible: true,
            active: false,
    });
    $(".initial-open").accordion("option", {active: 0});
    /*
    $("#gre-tab").tabs();
    $("#rss-tab").tabs();
    $("#est-gre-1").tabs();
     */
}).tooltip();
</script>




<?php


function get_gre_html($config, $use_tabs = false) {
    $html = <<<HTML

<div><img src="examples/2022/gre_sb.png" width="90%" /></div>

<div class="option-panels">
HTML;

    $sections = $config->get_section_order();

    $show_title = false;
    for ($i = 0; $i < count($sections); $i++) {
        $id = $sections[$i];
        $tab = $config->get_tab($id);

        if ($tab != "gre")
            continue;

        $section_html = $config->output_section($id, $show_title);
        $title = $config->get_title($id);

        $html .= output_accordion($section_html, $title);
    }

    $html .= <<<HTML
</div>
HTML;

    return $html;
}


function get_tab_html($config, $image, $target_tab) {
    $html = <<<HTML

<div><img src="examples/2022/$image" width="90%" /></div>

<div class="option-panels">
HTML;

    $sections = $config->get_section_order();

    $show_title = false;
    for ($i = 0; $i < count($sections); $i++) {
        $id = $sections[$i];
        $tab = $config->get_tab($id);

        if ($tab != $target_tab)
            continue;

        $section_html = $config->output_section($id, $show_title);
        $title = $config->get_title($id);

        $html .= output_accordion($section_html, $title);
    }

    $html .= <<<HTML
</div>
HTML;

    return $html;
}


function output_accordion($html, $title) {
    $html = <<<HTML
<div>
    <h3>$title</h3>
    <div>
        $html
    </div>
</div>
HTML;
    return $html;
}




