<?php

use \efi\gnt\bigscape_job;


function render_gnd_tools($params) {
?>
                        <div id="page-tools" class="initial-hidden">
                            <i class="fas fa-wrench" aria-hidden="true"></i> <span class="sidebar-header">Tools</span>
                            <div class="btn-group" style="width: 100%; margin-button: 30px">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    Export Data <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="#" id="export-gene-graphics-button"><i class="far fa-image" aria-hidden="true"></i> Export to Gene Graphics</a></li>
<?php if (!$params->is_example && $params->supports_download && !$params->is_uploaded_diagram) { ?>
                                    <li><a id="download-data" href="download_files.php?<?php echo $params->id_key_query_string; ?>&type=data-file"
                                        title="Download the data to upload it for future analysis using this tool.">
                                                <i class="fas fa-download" aria-hidden="true"></i> Download Data as SQLite
                                            </a></li>
<?php } ?>
                                </ul>
                            </div>

<?php if ($params->supports_export) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="save-canvas-button">
                                    <i class="far fa-image" aria-hidden="true"></i> Save as SVG
                                </button>
                            </div>
<?php } ?>
                            <div>
                                <a href="view_diagrams.php?<?php echo $params->id_key_query_string; ?>" target="_blank">
                                    <button type="button" class="btn btn-default tool-button">
                                        <i class="fas fa-window-restore" aria-hidden="true"></i> New Window
                                    </button>
                                </a>
                            </div>

<?php if ($params->is_direct_job) {?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-uniprot-ids">
                                    <i class="far fa-thumbs-up" aria-hidden="true"></i> <?php if (!$params->is_blast) echo "Recognized"; ?> UniProt IDs
                                </button>
                            </div>
<?php if ($params->has_unmatched_ids) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-unmatched-ids">
                                <i class="fas fa-thumbs-down" aria-hidden="true"></i> Unmatched IDs
                                </button>
                            </div>
<?php } ?>
<?php if ($params->is_blast) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="show-blast-sequence">
                                <i class="fas fa-file-alt" aria-hidden="true"></i> Input Sequence
                                </button>
                            </div>
<?php } ?>
<?php } ?>
<?php if ($params->is_bigscape_enabled) { ?>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="run-bigscape-btn" <?php if ($params->bigscape_status === bigscape_job::STATUS_FINISH) echo "data-toggle=\"button\""; ?>>
                                    <i class="fas <?php echo $params->bigscape_btn_icon; ?>"></i> <span id="run-bigscape-btn-text"><?php echo $params->bigscape_btn_text; ?></span>
                                </button>
                            </div>

<?php if ($params->bigscape_status === bigscape_job::STATUS_FINISH) { ?>
                            <div>
                                <a href="download_files.php?<?php echo $params->id_key_query_string; ?>&type=bigscape"
                                    title="Download the BiG-SCAPE clan data.">
                                        <button type="button" class="btn btn-default tool-button" id="view-bigscape-list-btn">
                                            <i class="fas fa-download" aria-hidden="true"></i> Get BiG-SCAPE Data
                                        </button>
                                </a>
                            </div>
<?php } ?>
<?php } ?>
                            <div>
                                <a href="diagram_tutorial.pdf">
                                <button type="button" class="btn btn-default tool-button" id="show-blast-sequence">
                                <i class="fas fa-file-alt" aria-hidden="true"></i> Tutorial
                                </button>
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="help-modal-button"><i class="fas fa-question"></i> Quick Tips</button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-default tool-button" id="info-modal-button"><i class="fas fa-info"></i> Licenses</button>
                            </div>
                        </div>

<?php } ?>

