<?php

function render_search_input($params) {

?>

<i class="fas fa-search" aria-hidden="true"> </i> <span class="sidebar-header">Search</span>
<div>
    <!-- start uniref -->
    <div id="advanced-search-use-uniref-container" style="display: none; margin-bottom: 10px;">
        <div style="font-size:0.9em">Show ID Type: <i class="fas fa-question-circle" data-placement="bottom" data-toggle="tooltip" title="UniRef options decrease the number of nodes that are displayed by grouping sequences together at a 50% and/or 90% sequence percent identity, depending on job options."></i></div>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-default" id="uniref50-btn">
                <input type="radio" name="display-id-type" id="uniref50-cb" autocomplete="off" value="50"> UniRef50
            </label>
            <label class="btn btn-default" id="uniref90-btn">
                <input type="radio" name="display-id-type" id="uniref90-cb" autocomplete="off" value="90"> UniRef90
            </label>
            <label class="btn btn-default active" id="uniprot-btn">
                <input type="radio" name="display-id-type" id="uniprot-cb" autocomplete="off" value="uniprot" checked> UniProt
            </label>
        </div>
        <!--
        <button type="button" class="btn btn-default tool-button" id="advanced-search-use-uniref">
            <i class="fas fa-plus"></i>
            Display UniRef50 Only
        </button>
        -->
        <!--
        <label><input type="checkbox" id="advanced-search-use-uniref" name="advanced-search-use-uniref"> Use UniRef50 IDs</label>
        -->
    </div> <!-- enduniref -->

    <div id="advanced-search-input-container" <?php if (!$params->is_realtime_job) { echo 'style="display:none"'; } ?>>
<?php if ($params->is_direct_job || $params->is_realtime_job) { ?>
        <div style="font-size:0.9em">Input specific UniProt IDs to display only those diagrams.</div>
<?php } else { ?>
        <div style="font-size:0.9em">Input multiple clusters and/or individual UniProt IDs.</div>
<?php } ?>
        <textarea id="advanced-search-input"></textarea>
        <div>
            <button type="button" class="btn btn-light" id="advanced-search-cluster-button">Query</button>
<?php if ($params->is_direct_job || $params->is_realtime_job) { ?>
            <button type="button" class="btn btn-light" id="advanced-search-reset-button"><?php echo $params->is_realtime_job ? "Clear" : "Reset View"; ?></button>
<?php } ?>
        </div>
    </div>
</div>

<?php

}


