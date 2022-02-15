

<?php

function add_sunburst_download_warning() {
?>
            <div id="sunburst-fasta-download" title="Download Warning" style="display: none">
                <p>
                    <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
                    This download may take a long time.
                </p>
            </div>

<?php
}


function add_sunburst_download_dialogs() {
?>
            <div id="sunburstDownloadModal" class="" style="display: none" tabindex="-1" role="dialog" style="margin-top: 200px">
                <div>
                    <h5 style="">Download Files</h5>
                    <button type="button" class="btn btn-primary" id="sbDownloadBtn"><a href="" id="sbDownloadLink">Download List</a></button><br><br>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
<?php
}


function add_sunburst_container() {
?>

<!--
            <div id="sunburstModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Species in Cluster</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
-->
                        <div class="modal-body text-center modal-sunburst" id="sunburstChartContainer" style="display: flex">
                            <div id="sunburstChart" style="display: inline-block">
                            </div>
                            <div style="display: inline-block; align-self: flex-end" id="sunburstChartLevels">
                            </div>
                            <div id="sunburstProgressLoader" class="progress-loader progress-loader-sm" style="display: none">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div>
                            <div id="sunburstIdNums" class="cluster-size cluster-size-sm float-right">
                            </div>
                            <div style="clear: both">
                                Click on a region to zoom into that part of the taxonomic hierarchy.  Clicking on the
                                center circle will zoom out to the next highest level.
                            </div>
                        </div>
        <!--
                            <div>
                                <canvas id="sunburstPngCanvas" width="600" height="600"></canvas>
                            </div>
        -->
                        <div class="modal-footer">
                            <div class="mr-auto">
                                <hr class="light">
                                <div class="p-2" id="sunburstTypeDownloadContainer" style="display: none">
                                    ID type: 
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="sunburstIdType" id="sunburstIdTypeUniProt" value="uniprot" checked>
                                        <label class="form-check-label" for="sunburstIdTypeUniProt">UniProt</label>
                                    </div>
                                    <div class="form-check form-check-inline" style="display: none" id="sunburstIdTypeUniRef90Container">
                                        <input class="form-check-input" type="radio" name="sunburstIdType" id="sunburstIdTypeUniRef90" value="uniref90">
                                        <label class="form-check-label" for="sunburstIdTypeUniRef90">UniRef90</label>
                                    </div>
                                    <div class="form-check form-check-inline" style="display: none" id="sunburstIdTypeUniRef50Container">
                                        <input class="form-check-input" type="radio" name="sunburstIdType" id="sunburstIdTypeUniRef50" value="uniref50">
                                        <label class="form-check-label" for="sunburstIdTypeUniRef50">UniRef50</label>
                                    </div>
        <!--
                                    <select class="form-control w-50" data-toggle="tooltip" title="By default the full set of UniProt IDs is downloaded. By selecting this option, only the UniRef50 IDs will be downloaded.">
                                        <option value="uniprot" selected>UniProt (default)</option>
                                        <option value="uniref50">UniRef50</option>
                                        <option value="uniref90">UniRef90</option>
                                    </select>
        -->
                                </div>
                                <div>
                                    <button type="button" class="normal btn btn-default btn-secondary" data-toggle="tooltip" title="Download the UniProt IDs that are visible in the sunburst diagram" id="sunburstDlIds">Prepare ID Download</button>
                                    <button type="button" class="normal btn btn-default btn-secondary mr-auto" data-toggle="tooltip" title="Download the FASTA sequences that are visible in the sunburst diagram" id="sunburstDlFasta">Prepare FASTA Download</button>
                                    <!--<button type="button" class="btn btn-default mr-auto" data-toggle="tooltip" title="Download a SVG file of the sunburst diagram" id="sunburstSvg">Download SVG</button>-->
                                </div>
                            </div>
<!--
                            <div class="mt-auto">
                                <button type="button" class="btn btn-default btn-secondary" data-dismiss="modal">Close</button>
                            </div>
-->
                        </div>
<!--                        
                    </div>
                    <div id="sunburstProgressLoader" class="progress-loader-tax" style="display: none">
                        <div>Please wait, this may take a while...
                        <div><i class="fas fa-spinner fa-spin"></i></div>
                        </div>
                    </div>
                </div>
            </div>
-->

<?php
}



