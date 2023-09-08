<?php

function render_filter_input($params) {

?>

                        <div class="initial-hidden">
                            <i class="fas fa-filter" aria-hidden="true"> </i> <span class="sidebar-header">Filtering</span>
                            <div class="filter-cb-div" id="filter-container-tabs">
                                <div class="tooltip-text" id="filter-anno-toggle-text">
                                    <input id="filter-anno-toggle" type="checkbox" />
                                    <label for="filter-anno-toggle"><span>Show SwissProt Annotations</span></label>
                                </div>
                            </div>

                            <div class="panel-group" id="filter-accordion">
                                <div class="filter-cb-div filter-cb-toggle-div" id="filter-container-toggle">
                                    <input id="filter-cb-toggle" type="checkbox" />
                                    <label for="filter-cb-toggle"><span id="filter-cb-toggle-text">Show Family Numbers</span></label>
                                </div>
<?php create_family_accordion_panel("PFam Families", "pfam"); ?>
<?php create_family_accordion_panel("InterPro Families", "interpro"); ?>
                            </div>
                            <div><input type="text" id="filter-search" /></div>
                            <button type="button" id="filter-clear"><i class="fas fa-times" aria-hidden="true"></i> Clear Filter</button>
                            <div class="active-filter-list" id="active-filter-list">
                            </div>
                        </div>

<?php

}


