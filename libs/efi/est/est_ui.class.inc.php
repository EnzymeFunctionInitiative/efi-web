<?php
namespace efi\est;

const RT_GENERATE = 1;
const RT_COLOR = 2;
const RT_ANALYSIS = 3;
const RT_NESTED_COLOR = 4;
const RT_NESTED_COLOR_X2 = 5;

use \efi\est\user_jobs;


class est_ui {
    

    public static function output_job_list($jobs, $show_archive = false, $sort_method = user_jobs::SORT_TIME_COMPLETED, $is_example = false, $show_all_ids = false) {
        $date_col_header = "<i class='fas fa-chevron-down'></i> Date Completed";
        $date_url = "?sb=" . user_jobs::SORT_TIME_ACTIVITY;
        $id_col_header = "<i class='fas fa-chevron-down'></i> ID";
        $id_url = "?sb=" . user_jobs::SORT_ID;
        if ($sort_method == user_jobs::SORT_TIME_ACTIVITY) {
            $date_col_header = "<i class='fas fa-chevron-up'></i> Recent Activity";
            $date_url = "?"; //"?sb=" . user_jobs::SORT_TIME_COMPLETED;
        } else if ($sort_method == user_jobs::SORT_ID) {
            $id_col_header = "<i class='fas fa-chevron-down'></i> ID (Desc)";
            $id_url = "?sb=" . user_jobs::SORT_TIME_COMPLETED;
        }

//        if ($toggle_id)
//            $toggle_id = <<<HTML
//<span id="$toggle_id" class="sort-toggle" title="Click to toggle between primary job ordering (with analysis jobs grouped with primary job), or by most recent job activity from newest to oldest."><i class="fas fa-list-alt"></i></span> 
//HTML;
        $html = <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col"><a href="$id_url">$id_col_header</a></th>
                    <th>Job Name</th>
                    <th class="date-col"><a href="$date_url">$date_col_header</a></th>
                </thead>
                <tbody>
HTML;

        $order = $jobs["order"];
        $cjobs = $jobs["color_jobs"];
        $gjobs = $jobs["generate_jobs"];
    
        $get_bg_color = new bg_color_toggle();
    
        for ($i = 0; $i < count($order); $i++) {
            $id = $order[$i];
    
            if (isset($gjobs[$id])) {
                $html .= self::output_generate_job($id, $gjobs[$id], $get_bg_color, $show_archive, $is_example, $show_all_ids);
            } elseif (isset($cjobs[$id])) {
                $html .= self::output_top_color_job($id, $cjobs[$id], $get_bg_color, $show_archive, $is_example);
            }
        }
        $html .= <<<HTML
                </tbody>
            </table>
HTML;
        return $html;
    }

    private static function output_top_color_job($id, $job, $get_bg_color, $show_archive, $is_example) {
        $bg_color = $get_bg_color->get_color();
        $link_class = "hl-color";
        $html = self::output_colorssn_row($id, $job, $bg_color, $show_archive, $is_example);
        if (isset($job["color_jobs"])) {
            foreach ($job["color_jobs"] as $cjob) {
                $htmlc = self::output_nested_colorssn_row($cjob["id"], $cjob, $bg_color, $show_archive, $is_example);
                $html .= $htmlc;
            }
        }
        return $html;
    }
    
    private static function output_generate_job($id, $job, $get_bg_color, $show_archive, $is_example, $show_all_ids = false) {
        $bg_color = $get_bg_color->get_color();
        $link_class = "hl-est";
        $html = self::output_generate_row($id, $job, $bg_color, $show_archive, $is_example);
    
        foreach ($job["analysis_jobs"] as $ajob) {
            $htmla = self::output_analysis_row($id, $job["key"], $ajob, $bg_color, $show_archive, $is_example, $show_all_ids);
            $html .= $htmla;
            if (isset($ajob["color_jobs"])) {
                foreach ($ajob["color_jobs"] as $cjob) {
                    $htmlc = self::output_nested_colorssn_row($cjob["id"], $cjob, $bg_color, $show_archive, $is_example, $show_all_ids);
                    $html .= $htmlc;
                    if (isset($cjob["color_jobs"])) {
                        $x2 = true;
                        foreach ($cjob["color_jobs"] as $xjob) {
                            $htmlx = self::output_nested_colorssn_row($xjob["id"], $xjob, $bg_color, $show_archive, $is_example, $show_all_ids, $x2);
                            $html .= $htmlx;
                        }
                    }
                }
            }
        }
        return $html;
    }

    private static function output_generate_row($id, $job, $bg_color, $show_archive, $is_example) {
        return self::output_row(RT_GENERATE, $id, NULL, $job["key"], $job, $bg_color, $show_archive, $is_example);
    }

    private static function output_colorssn_row($id, $job, $bg_color, $show_archive, $is_example) {
        return self::output_row(RT_COLOR, $id, NULL, $job["key"], $job, $bg_color, $show_archive, $is_example);
    }

    private static function output_nested_colorssn_row($id, $job, $bg_color, $show_archive, $is_example, $show_all_ids = false, $x2 = false) {
        return self::output_row(($x2 ? RT_NESTED_COLOR_X2 : RT_NESTED_COLOR), $id, NULL, $job["key"], $job, $bg_color, $show_archive, $is_example, $show_all_ids);
    }

    private static function output_analysis_row($id, $key, $job, $bg_color, $show_archive, $is_example, $show_all_ids = false) {
        return self::output_row(RT_ANALYSIS, $id, $job["analysis_id"], $key, $job, $bg_color, $show_archive, $is_example, $show_all_ids);
    }

    // $aid = NULL to not output an analysis (nested) job
    private static function output_row($row_type, $id, $aid, $key, $job, $bg_color, $show_archive, $is_example, $show_all_ids = false) {
        $script = \efi\global_settings::get_est_web_path() . "/" . self::get_script($row_type);
        $link_class = self::get_link_class($row_type);
    
        $name = $job["job_name"];
        $date_completed = $job["date_completed"];
        $is_completed = $job["is_completed"];
        //if ($row_type == RT_NESTED_COLOR)
        //    var_dump($job);
    
        $link_start = "";
        $link_end = "";
        $name_style = "";
        $data_aid = "";
        $archive_icon = "fa-stop-circle cancel-btn";
        $request_type = "cancel";
        if ($is_completed) {
            $aid_param = $row_type == RT_ANALYSIS ? "&analysis_id=$aid" : "";
            $ex_param = $is_example ? "&x=1" : "";
            $link_start = "<a href='$script?id=$id&key=${key}${aid_param}${ex_param}' class='$link_class'>";
            $link_end = "</a>";
            $archive_icon = "fa-trash-alt";
            $request_type = "archive";
        } elseif ($date_completed == __FAILED__) {
            $archive_icon = "fa-trash-alt";
            $request_type = "archive";
        }
        $id_text = "$link_start${id}$link_end";
        
        $indent = $row_type == RT_ANALYSIS ? 35 : ($row_type == RT_NESTED_COLOR ? 70 : ($row_type == RT_NESTED_COLOR_X2 ? 95 : 0));
        if ($indent) {
            $name_style = "style=\"padding-left: ${indent}px;\"";
            if (!$show_all_ids)
                $id_text = "";
            else
                $id_text = $aid;
            if ($row_type == RT_ANALYSIS)
                $data_aid = "data-analysis-id='$aid'";
        }
        $data_parent_id = "";
        if ($row_type == RT_ANALYSIS) {
            $data_parent_id = "data-parent-id='" . $job["parent_id"] . "'";
        } else if ($row_type == RT_NESTED_COLOR || $row_type == RT_NESTED_COLOR_X2) {
            $akey = isset($job["parent_aid"]) ? "aid" : "id";
            $data_parent_id = "data-parent-$akey='" . $job["parent_$akey"] . "'";
        }
//        if ($row_type == RT_ANALYSIS) {
//            $name_style = "style=\"padding-left: 35px;\"";
//            if (!$show_all_ids)
//                $id_text = "";
//            else
//                $id_text = $aid;
//            $data_aid = "data-analysis-id='$aid'";
//        } elseif ($row_type == RT_NESTED_COLOR) {
//            $name_style = "style=\"padding-left: 70px;\"";
//            if (!$show_all_ids)
//                $id_text = "";
//            else
//                $id_text = $id;
//        }
        $name = "<span title='$id'>$name</span>";
    
        $status_update_html = "";
        if ($show_archive)
            $status_update_html = "<div style='float:right' class='archive-btn' data-type='generate' data-rt='$request_type' data-id='$id' data-key='$key' $data_aid $data_parent_id title='Archive Job'><i class='fas $archive_icon'></i></div>";
    
        return <<<HTML
                    <tr style="background-color: $bg_color">
                        <td>$id_text</td>
                        <td $name_style>$link_start${name}$link_end</td>
                        <td>$date_completed $status_update_html</td>
                    </tr>
HTML;
    }

    private static function get_script($row_type) {
        switch ($row_type) {
        case RT_GENERATE:
            return "stepc.php";
        case RT_ANALYSIS:
            return "stepe.php";
        case RT_COLOR:
        case RT_NESTED_COLOR:
        case RT_NESTED_COLOR_X2:
            return "view_coloredssn.php";
        default:
            return "";
        }
    }
    
    private static function get_link_class($row_type) {
        switch ($row_type) {
        case RT_COLOR:
        case RT_NESTED_COLOR:
        case RT_NESTED_COLOR_X2:
            return "hl-color";
        default:
            return "hl-est";
        }
    }

}


class bg_color_toggle {

    private $last_bg_color = "#eee";

    // Return the color and then toggle it.
    public function get_color() {
        if ($this->last_bg_color == "#fff")
            $this->last_bg_color = "#eee";
        else
            $this->last_bg_color = "#fff";
        return $this->last_bg_color;
    }
}

