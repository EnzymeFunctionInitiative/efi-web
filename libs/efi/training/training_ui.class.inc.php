<?php
namespace efi\training;

require_once(__DIR__."/../../../init.php");


class training_ui {

    public static function get_tab_header($headers) {
        $html = <<<HTML
<div class="tabs-efihdr ui-tabs ui-widget-content">
    <ul class="ui-tabs-nav ui-widget-header">

HTML;
        $first = true;
        foreach ($headers as $id => $title) {
            $css = $first ? "ui-tabs-active" : "";
            $first = false;
            $html .= <<<HTML
        <li class="$css"><a href="#$id">$title</a></li>

HTML;
        }
        $html .= <<<HTML
    </ul>
    <div>

HTML;
        return $html;
    }

    public static function get_tab($id, $title, $content) {
        $html = <<<HTML
<div id="$id" class="ui-tabs-panel ui-widget-content">
    <h4>$title</h4>
    <div>
        $content
    </div>
</div>

HTML;
        return $html;
    }

    public static function get_tab_footer() {
        return <<<HTML
    </div>
</div>

HTML;
    }

}



