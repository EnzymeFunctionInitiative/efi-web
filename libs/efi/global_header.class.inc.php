<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");

use \efi\global_settings;


class global_header {

    public static function get_global_citation() {
        $citation = global_settings::get_global_citation();
        return <<<CITATION
            <div class="citation-message">
                <div id="clipboard-citation">$citation</div>
            </div>
CITATION;
    }

}

