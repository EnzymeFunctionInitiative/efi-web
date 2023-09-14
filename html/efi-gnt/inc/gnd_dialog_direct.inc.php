<?php

function render_direct_job_dialogs($params) {
    $gnn_id = $params->gnn_id;
    $gnn_name = $params->gnn_name;
?>

        <div id="uniprot-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">UniProt IDs Identified</h4>
                    </div>
                    <div class="modal-body" id="uniprot-ids">
                        <table border="0">
                            <thead>
                                <th width="120px">UniProt ID</th>
                                <th>Query ID</th>
                            </thead>
                            <tbody>
<?php echo $params->uniprot_id_modal_text; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $params->id_key_query_string; ?>&type=uniprot"
                            title="Download the list of UniProt IDs that are contained within the diagrams.">
                                <button type="button" class="btn btn-default" id="save-uniprot-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "{$gnn_id}_{$gnn_name}_UniProt_IDs.txt" ?>", "uniprot-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php if ($params->has_unmatched_ids) { ?>
        <div id="unmatched-ids-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">IDs Detected Without UniProt Match</h4>
                    </div>
                    <div class="modal-body" id="unmatched-ids">
<?php echo $params->unmatched_id_modal_text; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $params->id_key_query_string; ?>&type=unmatched"
                            title="Download the list of IDs that were not matched to a UniProt ID.">
                                <button type="button" class="btn btn-default" id="save-unmatched-ids-btn">Save to File</button>
                        </a>
                            <!--                            onclick='saveDataFn("<?php echo "{$gnn_id}_{$gnn_name}_Unmatched.txt" ?>", "unmatched-ids")'>Save to File</button>-->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>
<?php if ($params->is_blast) { ?>
        <div id="blast-sequence-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Sequence Used in BLAST</h4>
                    </div>
                    <div class="modal-body" id="blast-sequence">
<?php echo $params->blast_seq; ?>
                    </div>
                    <div class="modal-footer">
                        <a href="download_files.php?<?php echo $params->id_key_query_string; ?>&type=blast"
                            title="Download the list of UniProt IDs that are contained within the diagrams.">
                                <button type="button" class="btn btn-default" id="save-blast-seq-btn">Save to File</button>
                        </a>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php } ?>

<?php
}

