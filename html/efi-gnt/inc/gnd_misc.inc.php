<?php

function render_page_title_cells($params) {

    $nb_size_div = "";
    $cooc_div = "";
    $job_type_div = "";
    $job_id_div = "";
    if ($params->is_direct_job) {
        $job_type_div = $params->job_type_text ? "<div>Job Type: $params->job_type_text</div>" : "";
    } else {
        $nb_size_div = $params->nb_size ? "<div>Neighborhood size: $params->nb_size</div>"  : "";
        $cooc_div = $params->cooccurrence ? "<div>Co-occurrence: $params->cooccurrence</div>" : "";
    }
    $job_id_div = ($params->gnn_id && $params->gnn_id > 0) ? "<div>Job ID: $params->gnn_id</div>" : "";

    //TODO: handle real-time search
    if ($params->is_realtime_job) {
?>
                    <td id="header-body-title" class="header-title">
                        Genome Neighborhood Diagrams
                    </td>
                    <td id="header-job-info">
                    </td>
<?php
    } else {
?>
                    <td id="header-body-title" class="header-title">
                        Genome Neighborhood Diagrams for <?php echo $params->gnn_name_text; ?>
                        <span id="cluster-uniref-id" class="display: none"><br></span>
                    </td>
                    <td id="header-job-info">
                        <?php echo $job_type_div; ?>
                        <?php echo $job_id_div; ?>
                        <?php echo $cooc_div; ?>
                        <?php echo $nb_size_div; ?>
                    </td>
<?php
    }
}

function render_page_footer_stats_cells($params) {
    
    //TODO: handle real-time search
    if (false) {
?>
                    <td>
                    </td>
                    <td style="width:250px">
                    </td>
<?php
    } else {
?>
                    <td>
                        <div class="initial-hidden">
                            <div>
                                Showing <span id="diagrams-displayed-count">0</span> of <span id="diagrams-total-count">0</span> diagrams.
                            </div>
                            <div id="diagram-filter-count-container" style="display: none"> 
                                Number of Diagrams with Selected Families: <span>0</div>
                            </div>
                        </div>
                    </td>
                    <td style="width:250px">
                        <button type="button" class="btn btn-default" id="show-all-arrows-button">Show All</button>
                        <button type="button" class="btn btn-default" id="show-more-arrows-button">Show <?php echo $params->num_diagrams; ?> More</button>
                    </td>
<?php
    }
}


function create_family_accordion_panel($panelTitle, $idSuffix) {
    echo <<<HTML
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title" data-toggle="collapse" data-parent="#filter-accordion" data-target="#filter-accordion-panel-$idSuffix">
              <span class="accordion-arrow glyphicon glyphicon-triangle-right" aria-hidden="true"></span> <a class="accordion-toggle">$panelTitle</a>
            </h4>
        </div>
        <div id="filter-accordion-panel-$idSuffix" class="panel-collapse collapse">
          <div class="filter-panel panel-body">
                            <div style="width:100%;height:12em;" class="filter-container" id="filter-container-$idSuffix">
                            </div>
          </div>
        </div>
    </div>
HTML;
}



