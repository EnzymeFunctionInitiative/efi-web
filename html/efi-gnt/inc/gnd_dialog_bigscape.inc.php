<?php

function render_bigscape_dialog($params) {

?>
        <div id="run-bigscape-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Run BiG-SCAPE</h4>
                    </div>
                    <div class="modal-body">
                        <div>
<?php if ($params->bigscape_status === bigscape_job::STATUS_NONE) { ?>
                            The <a href="https://git.wageningenur.nl/medema-group/BiG-SCAPE">Biosynthetic Genes
                            Similarity Clustering and Prospecting (BiG-SCAPE)</a> tool can be used to cluster the individual
                            diagrams based on their genomic context.  This can take several hours to complete, depending on
                            the size of the clusters.  If you proceed, you will to notified when the clustering has been
                            completed, and your arrow diagrams will be updated to reflect the new ordering.  You can continue
                            to use the tool as before while BiG-SCAPE is running.  Do you wish to continue?
<?php } else { ?>
                            The BiG-SCAPE clustering is currently pending or
                            executing.  You will receive an email when the clustering has begun and completed.
<?php } ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div id="run-bigscape-footer">
<?php if ($params->bigscape_status === bigscape_job::STATUS_NONE) { ?>
                            <button type="button" class="btn btn-default" class="btn-confirm" id="run-bigscape-confirm">Yes</button>
<?php } ?>
                            <button type="button" class="btn btn-default" class="btn-reject" data-dismiss="modal"><?php echo $params->bigscape_modal_close_text; ?></button>
                        </div>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

<?php
}

