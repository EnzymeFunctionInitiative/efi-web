<?php

function render_window_tools($params) {
?>
                        <div id="window-tools" class="initial-hidden">
                            <i class="fas fa-window-maximize" aria-hidden="true"></i> <span class="sidebar-header">Genome Window</span>
                            <div>
                                <select id="window-size" class="light zoom-btn">
<?php
    for ($i = 1; $i <= $params->max_nb_size; $i++) {
        $sel = $i == $params->nb_size ? "selected" : "";
        echo "                                    <option value=\"$i\" $sel>$i</option>\n";
    }
?>
                                </select> genes
                                <button type="button" class="btn btn-default tool-button auto zoom-btn" id="refresh-window">
                                    <i class="fas fa-refresh" aria-hidden="true"></i> Apply
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-out-large" style="font-size: 1.2em" title="0.25x">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-out-small" title="0.888x">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                Zoom
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-in-small" title="1.125x">
                                    <i class="fas fa-search-plus" title="1.125x"></i>
                                </button>
                                <button type="button" class="btn btn-default tool-button zoom-btn" id="scale-zoom-in-large" style="font-size: 1.2em" title="4x">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <!--Scale factor: 1000 AA=<input type="text" width="5" name="scale-factor" id="scale-factor" value="15" style="line-height: 1.5em; padding: 2px; width: 35px; color: black" title="press enter to apply" />%-->
                            </div>
                        </div>
<?php } ?>

