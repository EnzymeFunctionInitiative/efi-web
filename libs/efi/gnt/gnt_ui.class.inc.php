<?php
namespace efi\gnt;

require_once(__DIR__."/../../../init.php");

use \efi\global_settings;
use \efi\global_functions;
use \efi\gnt\settings;


class gnt_ui {

    public static function add_advanced_options() {
        if (!global_settings::advanced_options_enabled())
            return;

        echo <<<HTML
<div>
    <span class="input-name">Use Extra Ram [DEV ONLY]:</span>
    <span class="input-field">
        <input type="checkbox" id="extra_ram" name="extra_ram">
    </span>
    <div class="input-desc">
        Check to use additional RAM (800GB) [default: off]
    </div>
</div>
HTML;
    }

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

    public static function process_load_rows($db, $rows, $include_failed_jobs, $email, $gnn_table = "gnn") {
        $jobs = array();
        $index_map = array();

        $child_jobs = array();

        $idx = 0;
        foreach ($rows as $row) {
            $is_finished = false;
            $status = $row["{$gnn_table}_status"];
            $comp = $status;
            if ($status == __FINISH__) {
                $comp = $row["{$gnn_table}_time_completed"];
                $comp = date_format(date_create($comp), "n/j h:i A");
                $is_finished = true;
            } else if ($status == __NEW__) {
                $comp = "PENDING";
            }
            $params = global_functions::decode_object($row["{$gnn_table}_params"]);
            $filename = pathinfo($params["filename"], PATHINFO_BASENAME);
            $job_name = "<span class='job-name'>$filename</span><br><span class='job-metadata'>Neighborhood Size=" . $params["neighborhood_size"] . " Co-occurrence=" . $params["cooccurrence"] . "</span>";

            $id = $row["{$gnn_table}_id"];
            $job_info = array("id" => $id, "key" => $row["{$gnn_table}_key"], "filename" => $job_name, "completed" => $comp, "is_child" => false, "is_finished" => $is_finished);

            $is_child = false;
            if (isset($row["{$gnn_table}_parent_id"]) && $row["{$gnn_table}_parent_id"]) {
                // Get parent email address and if it's not the same as the current email address then treat this job
                // as a normal job.
                $sql = "SELECT gnn_email FROM gnn WHERE gnn_id = " . $row["{$gnn_table}_parent_id"];
                $parent_row = $db->query($sql);
                $is_child = !$parent_row || $parent_row[0]["gnn_email"] == $email || !$email;  // !$email is true for training jobs
            }

            if ($is_child) {
                $parent_id = $row["{$gnn_table}_parent_id"];
                $job_info["is_child"] = true;
                $job_info["parent_id"] = $parent_id;
                if (isset($child_jobs[$parent_id]))
                    array_push($child_jobs[$parent_id], $job_info);
                else
                    $child_jobs[$parent_id] = array($job_info);
            } else {
                array_push($jobs, $job_info);
                $index_map[$id] = $idx;
                $idx++;
            }
        }

        for ($i = 0; $i < count($jobs); $i++) {
            $id = $jobs[$i]["id"];
            if (isset($child_jobs[$id])) {
                array_splice($jobs, $i+1, 0, $child_jobs[$id]);
            }
        }

        return $jobs;
    }

    public static function output_job_list($jobs, $is_example = false) {
        $html = <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

        $example_arg = $is_example ? "&x=".$is_example : "";
        $base_url_dir = global_settings::get_gnt_web_path() . "/";

        $last_bg_color = "#eee";
        for ($i = 0; $i < count($jobs); $i++) {
            $key = $jobs[$i]["key"];
            $id = $jobs[$i]["id"];
            $name = $jobs[$i]["filename"];
            $date_completed = $jobs[$i]["completed"];
            $is_active = $date_completed == "PENDING" || $date_completed == "RUNNING";
        
            $link_start = $is_active ? "" : "<a class=\"hl-gnt\" href=\"{$base_url_dir}stepc.php?id=$id&key=$key$example_arg\">";
            $link_end = $is_active ? "" : "</a>";
            $link_start .= "<span title='$id'>";
            $link_end = "</span>" . $link_end;
            $id_text = "$link_start{$id}$link_end";
        
            $name_style = "";
            if ($jobs[$i]["is_child"]) {
                $id_text = "";
                $name_style = "style=\"padding-left: 50px;\"";
            } else {
                if ($last_bg_color == "#eee")
                    $last_bg_color = "#fff";
                else
                    $last_bg_color = "#eee";
            }
        
            if (array_key_exists("diagram", $jobs[$i]))
                $link_start = "<a class=\"hl-gnt\" href='{$base_url_dir}view_diagrams.php?upload-id=$id&key=$key$example_arg'>";
        
            $html .= <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$id_text</td>
                        <td $name_style>$link_start{$name}$link_end</td>
                        <td>$date_completed</td>
                    </tr>
HTML;
        }
        $html .= <<<HTML
                </tbody>
            </table>
HTML;
        return $html;
    }
}


