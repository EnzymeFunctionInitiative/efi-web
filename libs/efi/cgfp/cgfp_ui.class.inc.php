<?php
namespace efi\cgfp;
require_once(__DIR__."/../../../init.php");

use \efi\global_functions;
use \efi\global_settings;
use \efi\cgfp\settings_shared;


class cgfp_ui {

    public static function output_job_list($jobs, $allow_cancel, $is_example = false) {
        $html = <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;
        $last_bg_color = "#eee";
        $ex_param = $is_example ? "&x=".$is_example : "";
        $script_dir = $is_example ? global_settings::get_cgfp_web_path() . "/" : "";
        for ($i = 0; $i < count($jobs); $i++) {
            $key = $jobs[$i]["key"];
            $id = $jobs[$i]["id"];
            $name = $jobs[$i]["job_name"];
            $is_completed = $jobs[$i]["is_completed"];
            $date_completed = $jobs[$i]["date_completed"];
            $is_finished = $date_completed && $date_completed != "PENDING" && $date_completed != "RUNNING" && $date_completed != "FAILED";
            $search_type = $jobs[$i]["search_type"];
            $ref_db = $jobs[$i]["ref_db"];
        
            $link_start = "";
            $link_end = "";
            $name_style = "";
            $id_field = $id;
            $quantify_id = "";
            $job_name = "";
            $job_info = "";
        
            if ($jobs[$i]["is_quantify"]) {
                $quantify_id = $jobs[$i]["quantify_id"];
                $title_str = "title=\"" . $jobs[$i]["full_job_name"] . "\"";
                if ($is_completed) {
                    $link_start = "<a class=\"hl-cgfp\" href=\"{$script_dir}stepe.php?id=$id&key=$key&quantify-id=$quantify_id$ex_param\" $title_str>";
                    $link_end = "</a>";
                } else {
                    $link_start = "<span $title_str>";
                    $link_end = "</span>";
                }
    
                $par_text = "";
                if ($jobs[$i]["identify_parent_id"])
                    $par_text = "Identify " . $jobs[$i]["id"] . "-";
    
                $name_style = "style=\"padding-left: 50px;\"";
                $job_name = $name;
                $job_info = "[{$par_text}Quantify $quantify_id]";
                $id_field = "";
            } else {
                $link_start = $is_finished ? "<a class=\"hl-cgfp\" href=\"{$script_dir}stepc.php?id=$id&key=$key$ex_param\">" : "";
                $link_end = $is_finished ? "</a>" : "";
                if ($last_bg_color == "#fff")
                    $last_bg_color = "#eee";
                else
                    $last_bg_color = "#fff";
                if ($jobs[$i]["identify_parent_id"]) {
                    $job_name = "Child job of Identify " . $jobs[$i]["identify_parent_id"];
                    $date_completed = "PENDING";
                } else {
                    $job_name = $name;
                }
            }
    
            if ($search_type)
                $job_info .= " Search=$search_type";
            if ($ref_db)
                $job_info .= " RefDB=$ref_db";
    
            $job_action_code = "";
            if ($allow_cancel) {
                if ($date_completed == "RUNNING" || $date_completed == "NEW") {
                    $job_action_code = "<div style=\"float:right\" class=\"cancel-btn\" data-type=\"gnn\" title=\"Cancel Job\" data-id=\"$id\" data-key=\"$key\"";
                    if ($quantify_id)
                        $job_action_code .= " data-quantify-id=\"$quantify_id\"";
                    $job_action_code .= "><i class=\"fas fa-stop-circle cancel-btn\"></i></div>";
                } else {
                    $job_action_code = "<div style=\"float:right\" class=\"archive-btn\" data-type=\"gnn\" data-id=\"$id\" data-key=\"$key\"";
                    if ($quantify_id)
                        $job_action_code .= ' data-quantify-id="' . $quantify_id . '"';
                    $job_action_code .= "title=\"Archive Job\"><i class=\"fas fa-trash-alt\"></i></div>";
                }
            }
            
    
            $html .= <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$link_start{$id_field}$link_end</td>
                        <td $name_style>$link_start<span class='job-name'>$job_name</span><br><span class='job-metadata'>$job_info</span>$link_end</td>
                        <td>$date_completed $job_action_code</td>
                    </tr>
HTML;
        }
        $html .= <<<HTML
                </tbody>
            </table>
HTML;
        return $html;
    }

    public static function process_load_rows($db, $id_rows, $identify_table = "", $quantify_table = "", $cgfp_db = "") {
        $jobs = array();

        if (!$quantify_table)
            $quantify_table = "quantify";
        if ($cgfp_db)
            $cgfp_db .= ".";
        if (!$identify_table)
            $identify_table = "identify";

        $default_cdhit_id = settings_shared::get_default_cdhit_id();
        $default_ref_db = settings_shared::get_default_ref_db();
        $default_id_search = strtolower(settings_shared::get_default_identify_search());
        $default_quantify_search = strtolower(settings_shared::get_default_quantify_search());

        foreach ($id_rows as $id_row) {
            $iparams = global_functions::decode_object($id_row["identify_params"]);

            $comp_result = self::get_completed_date_label($id_row["identify_time_completed"], $id_row["identify_status"]);
            $job_name = $iparams["identify_filename"];
            $comp = $comp_result[1];
            $is_completed = $comp_result[0];

            $id_id = $id_row["identify_id"];
            $key = $id_row["identify_key"];
            $parent_id = $id_row["identify_parent_id"];

            if (!$parent_id) {
                $i_job_info = array("id" => $id_id, "key" => $key, "job_name" => $job_name, "is_completed" => $is_completed,
                    "is_quantify" => false, "date_completed" => $comp, "identify_parent_id" => "");
                
                if (isset($iparams["identify_search_type"]) && $iparams["identify_search_type"] != $default_id_search)
                    $i_job_info["search_type"] = $iparams["identify_search_type"];
                else
                    $i_job_info["search_type"] = "";
                
                if (isset($iparams["identify_ref_db"]) && $iparams["identify_ref_db"] != $default_ref_db)
                    $i_job_info["ref_db"] = $iparams["identify_ref_db"];
                else
                    $i_job_info["ref_db"] = "";
    
                array_push($jobs, $i_job_info);
            }

            if ($parent_id && $id_row["identify_status"] == "NEW") {
                $c_job_info = array("id" => $id_id, "key" => "", "job_name" => "", "is_completed" => false,
                    "is_quantify" => false, "date_completed" => "", "search_type" => "", "ref_db" => "", "identify_parent_id" => $parent_id);
                array_push($jobs, $c_job_info);
                continue;
            } elseif ($parent_id) {
                continue;
            }

            if ($is_completed) {
                $q_sql = "SELECT quantify_id, quantify_identify_id, quantify_time_completed, quantify_status, quantify_params, identify_parent_id, identify_params, identify_key " .
                    "FROM {$cgfp_db}{$quantify_table} JOIN {$cgfp_db}{$identify_table} ON quantify_identify_id = identify_id " .
                    "WHERE (quantify_identify_id = $id_id OR identify_parent_id = $id_id) AND quantify_status != '" . __ARCHIVED__ . "'";
                $q_rows = $db->query($q_sql);

                foreach ($q_rows as $q_row) {
                    $qparams = global_functions::decode_object($q_row["quantify_params"]);

                    $q_comp_result = self::get_completed_date_label($q_row["quantify_time_completed"], $q_row["quantify_status"]);
                    $q_comp = $q_comp_result[1];
                    $q_is_completed = $q_comp_result[0];
                    $q_id = $q_row["quantify_id"];
                    $job_name = isset($qparams["quantify_job_name"]) ? $qparams["quantify_job_name"] : "";
                    $par_id = "";

                    $mg_ids = explode(",", $qparams["quantify_metagenome_ids"]);
                    if ($q_row["identify_parent_id"]) {
                        $iparams = global_functions::decode_object($q_row["identify_params"]);
                        $q_full_job_name = $iparams["identify_filename"];
                        $q_job_name = $q_full_job_name;
                        $the_id_id = $q_row["quantify_identify_id"];
                        $the_key = $q_row["identify_key"];
                        $par_id = $q_row["identify_parent_id"];
                    } else {
                        $the_id_id = $id_id;
                        $the_key = $key;
                        $q_full_job_name = implode(", ", $mg_ids);
                        if ($job_name) {
                            $q_job_name = $job_name;
                        } elseif (count($mg_ids) > 6) {
                            $q_job_name = implode(", ", array_slice($mg_ids, 0, 5)) . " ...";
                        } else {
                            $q_job_name = $q_full_job_name;
                        }
                    }

                    $q_job_info = array("id" => $the_id_id, "key" => $the_key, "quantify_id" => $q_id, "job_name" => $q_job_name,
                        "is_completed" => $q_is_completed, "is_quantify" => true, "date_completed" => $q_comp,
                        "full_job_name" => $q_full_job_name, "identify_parent_id" => $par_id);

                    if (isset($qparams["quantify_search_type"]) && $qparams["quantify_search_type"] != $default_quantify_search)
                        $q_job_info["search_type"] = $qparams["quantify_search_type"];
                    else
                        $q_job_info["search_type"] = "";

                    $q_job_info["ref_db"] = "";
                    array_push($jobs, $q_job_info);
                }
            }
        }

        return $jobs;
    }

    // Candidate for refacotring to centralize
    public static function get_completed_date_label($comp, $status) {
        $isCompleted = false;
        if ($status == __FAILED__ || $status == __RUNNING__ || $status == __CANCELLED__) {
            $comp = $status;
        } elseif (!$comp || substr($comp, 0, 4) == "0000" || $status == __NEW__) {
            $comp = "PENDING";
        } else {
            $comp = global_functions::format_short_date($comp);
            $isCompleted = true;
        }
        return array($isCompleted, $comp);
    }
}

