<?php

function render_new_features_dialog($params) {
?>

        <div class="new-features-alert alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            You can now click on an arrow to keep the info box open.  The info box has
            a link to the UniProt page for the protein, as well as a button for copying the
            information in the box onto the clipboard.
        </div>
<?php
}


function render_popup_dialog($params) {
    $hide_interpro = $params->is_interpro_enabled ? "" : 'style="display:none"';
?>
        <div id="info-popup" class="info-popup hidden">
            <div id="copy-info"><i class="far fa-copy"></i></div>
            <div id="info-popup-id">UniProt ID: <a href="https://www.uniprot.org/uniprot" target="_blank"><span class="popup-id"></span></a></div>
            <div id="info-popup-desc">Description: <span class="popup-pfam"></span></div>
            <div id="info-popup-sptr">Annotation Status: <span class="popup-pfam"></span></div>
            <div class="info-popup-group">
                <div class="info-hdr">Pfam</div>
                <div id="info-popup-fam"><span class="popup-pfam"></span></div>
                <div id="info-popup-fam-desc"><span class="popup-pfam"></span></div>
            </div>
            <div class="info-popup-group" <?php echo $hide_interpro; ?>>
                <div class="info-hdr">InterPro</div>
                <div id="info-popup-ipro-fam"><span class="popup-pfam"></span></div>
                <div id="info-popup-ipro-fam-desc"><span class="popup-pfam"></span></div>
            </div>
            <!--    <div id="info-popup-coords">Coordinates: <span class="popup-pfam"></span></div>-->
            <div id="info-popup-seqlen" class="info-popup-group">Sequence Length: <span class="popup-pfam"></span></div>
            <!--    <div id="info-popup-dir">Direction: <span class="popup-pfam"></span></div>-->
            <!--    <div id="info-popup-num">Gene Index: <span class="popup-pfam"></span></div>-->
        </div>
<?php
}


function render_license_dialog($params) {
?>
        <div id="info-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Software Licenses and Attribution</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                        This site uses <a href="https://fontawesome.com">Font Awesome 5</a> and is used by
                        Creative Commons Attribution 4.0 International
                        <a href="https://fontawesome.com/license">license</a>.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
<?php
}


function render_help_dialog($params) {
?>
        <div id="help-modal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Tips for Exploring</h4>
                    </div>
                    <div class="modal-body">
                        <p>
                        <div><b>Interactive Filtering</b></div>
                        The mouse can be used to select families to filter.  To
                        do this, press and hold the Ctrl key on the keyboard
                        and click on a protein.  All of the PFam families that
                        are associated with the protein will be highlighted.
                        </p>
                        <p>
                        <div><b>Viewing Metadata</b></div>
                        Moving the mouse over a specific protein will show a
                        popup box containing metadata.  As soon as the mouse is
                        moved away from the protein, the box disappears.  To
                        keep the box open, click on the protein, and the box
                        will remain visible until the mouse is moved over a
                        different protein.
                        </p>
                        <p>
                        <div><b>Copying Metadata</b></div>
                        Clicking the copy <i class="far fa-copy"></i> icon when
                        the metadata popup box is visible will copy the
                        metadata to the clipboard.  This information can be
                        pasted into another document for further use.
                        </p>
                        <p>
                        <div><b>Direct Link to UniProt Data</b></div>
                        The UniProt ID in the metadata popup box is a link that
                        can be used to access the UniProt website for the given protein.
                        </p>
                        <p>
                        <div><b>Changing the Window (Scale)</b></div>
                        By default a maximum of 40 kbp are shown.  This window
                        scale factor can be increased <i class="fas fa-search-minus"></i> or
                        decreased <i class="fas fa-search-plus"
                        title="1.125x"></i> by using the zoom buttons.  All
                        visible diagrams wil be reloaded when using the zoom
                        buttons.
                        </p>
                        <p>
                        <div><b>Changing the Window (Gene)</b></div>
                        The GND explorer can display from 1 to 20 genes on
                        either side of the query gene (center, red).  This can be
                        changed by clicking the "genes" drop down menu in the
                        Genome Window section, and clicking the Apply button.
                        </p>
                        <p>
                        <div><b>Updating the Filter Legend</b></div>
                        Selecting a family filter makes that family, along with its
                        assigned color, appear in a legend box below the "Clear Filter"
                        button.  Individual families can be removed from the legend
                        by moving the mouse over the color box and pressing the X
                        button that appears in the color box.  For InterPro
                        families, the color is not assigned, but the functionality
                        is the same.
                        </p>
                        <p>
                        <div><b>Data Export</b></div>
                        <?php if ($params->supports_download && !$params->is_uploaded_diagram) { ?>
                        It is possible to export a file that provides the data used to generate the diagrams.
                        The data file format is SQLite, and it can be uploaded to the EFI-GNT
                        tool and viewed again in the future via the <i>View Saved Diagrams</i>
                        option.
                        For advanced use, the file can also be opened with
                        <a href="https://sqlitebrowser.org/" target="_blank">DB Browser for SQLite</a>
                        to manually extract data.
                        <?php } ?>
                        </p>
                        <p>
                        The currently displayed diagrams can be exported to the Gene Graphics
                        format and displayed using the
                        <a href="https://katlabs.cc/genegraphics/" target="_blank">Gene Graphics</a>
                        comparative genomics visualization tool.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>
<?php
}

