<?php

class gnt_ui {

    public static function add_cooccurrence_setting($use_header = true, $input_cooc = "") {
        $default_cooc = settings::get_default_cooccurrence();
        $title = "Minimal Co-occurrence Percentage Lower Limit";

        if (!$input_cooc)
            $input_cooc = $default_cooc;
        
        if ($use_header)
            echo <<<HTML
<h3>$title</h3>
HTML;

        $title = $use_header ? "Cooccurrence" : "$title";

        echo <<<HTML
<div>
    <span class="input-name">$title:</span>
    <span class="input-field">
        <input type="text" id="cooccurrence" name="cooccurrence" maxlength="3" size="4" value="$input_cooc">
    </span>
    <div class="input-desc">
        Filters out the neighboring Pfams for which the co-occurrence percentage is lower than the set value (noise filter).
        The default value is $default_cooc and valid values are 0-100.
    </div>
</div>
HTML;
    }


    public static function add_neighborhood_size_setting($use_header = true) {
        $neighbor_size_html = "";
        $default_neighbor_size = settings::get_default_neighbor_size();
        $min_nb = 3;
        $max_nb = 20;
        for ($i = $min_nb; $i <= $max_nb; $i++) {
            if ($i == $default_neighbor_size)
                $neighbor_size_html .= "<option value='" . $i . "' selected='selected'>" . $i . "</option>";
            else
                $neighbor_size_html .= "<option value='" . $i . "'>" . $i . "</option>";
        }

        $title = "Neighborhood Size";
        if ($use_header)
            echo <<<HTML
<h3>$title</h3>
HTML;

        $title = $use_header ? "Size" : "$title";

        echo <<<HTML
<div>
    <span class="input-name">$title:</span>
    <span class="input-field">
        <select name="neighbor_size" id="neighbor_size" class="bigger">
            $neighbor_size_html;
        </select>
    </span>
    <div class="input-desc">
        The Pfam families for N neighboring genes upstream and downstream will be 
        collected and analyzed. The default value is $default_neighbor_size and the minimum and maximum are 
        $min_nb and $max_nb, respectively.
    </div>
</div>
HTML;
    }

}

?>
