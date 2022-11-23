<?php
namespace efi;

require_once(__DIR__."/../../init.php");


abstract class job_list_ui {

    protected $db;
    protected $table_name = "";
    private $email = "";
    private $include_failed_jobs = false;
    private $css_hl = "";

    public function __construct($db, $table_name, $email, $include_failed_jobs, $css_highlight_class) {
        $this->db = $db;
        $this->table_name = $table_name;
        $this->email = $email;
        $this->include_failed_jobs = $include_failed_jobs;
        $this->css_hl = $css_highlight_class;
    }

    public function process_load_rows($rows) {
        $jobs = array();
        $index_map = array();

        $the_table = $this->table_name;

        $idx = 0;
        foreach ($rows as $row) {
            $comp_result = self::get_completed_date_label($row["${the_table}_time_completed"], $row["${the_table}_status"]);
            $params = global_functions::decode_object($row["${the_table}_params"]);
            $job_name = $this->get_job_name($row["${the_table}_type"], $params);

            $comp = $comp_result[1];
            $is_completed = $comp_result[0];

            $id = $row["${the_table}_id"];
            $job_info = array("id" => $id, "key" => $row["${the_table}_key"], "job_name" => $job_name, "date_completed" => $comp, "is_completed" => $is_completed, "is_child" => false);
            
            $extra_job_info = $this->get_extra_job_info($row, $params);
            if (is_array($extra_job_info)) {
                $job_info = array_merge($job_info, $extra_job_info);
            }
            array_push($jobs, $job_info);
            $index_map[$id] = $idx;
            $idx++;
        }

        $replace_jobs = $this->post_process_job_list($jobs);
        if (is_array($replace_jobs))
            $jobs = $replace_jobs;

        return $jobs;
    }
    
    public static function get_completed_date_label($comp, $status) {
        $is_completed = false;
        if ($status == "FAILED") {
            $comp = "FAILED";
        } elseif (!$comp || substr($comp, 0, 4) == "0000" || $status == "RUNNING") {
            $comp = $status;
            if ($comp == "NEW")
                $comp = "PENDING";
        } else {
            $comp = date_format(date_create($comp), "n/j h:i A");
            $is_completed = true;
        }
        return array($is_completed, $comp);
    }

    protected abstract function get_extra_job_info($row, $params);
    protected abstract function get_job_name($job_type, $params);
    protected abstract function post_process_job_list($jobs);

    public function output_job_list($jobs, $job_name_col, $is_example = false) {
        $html = <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>$job_name_col</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

        $example_arg = $is_example ? "&x=".$is_example : "";

        $last_bg_color = "#eee";
        for ($i = 0; $i < count($jobs); $i++) {
            $job = $jobs[$i];
            $key = $job["key"];
            $id = $job["id"];
            $name = $job["job_name"];
            $date_completed = $job["date_completed"];
            $data_type = $job["data_type"];
            $is_active = $date_completed == "PENDING" || $date_completed == "RUNNING";
            $request_type = $is_active ? "cancel" : "archive";

            $url = $this->get_url($id, $key, $job);
            $link_start = $is_active ? "" : '<a class="' . $this->css_hl . '" href="' . $url . $example_arg . '">';
            $link_end = $is_active ? "" : "</a>";
            $link_start .= "<span title='$id'>";
            $link_end = "</span>" . $link_end;
            $id_text = "$link_start${id}$link_end";
        
            $name_style = "";
            if ($this->check_for_indent($job)) {
                $id_text = "";
                $name_style = "style=\"padding-left: 50px;\"";
            } else {
                if ($last_bg_color == "#eee")
                    $last_bg_color = "#fff";
                else
                    $last_bg_color = "#eee";
            }

            $action = <<<HTML
<div style="float:right" class="archive-btn" data-type="$data_type" data-id="$id" data-rt="$request_type" data-key="$key" title="Archive Job"><i class="fas fa-trash-alt"></i></div>
HTML;

            $html .= <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$id_text</td>
                        <td $name_style>$link_start${name}$link_end</td>
                        <td>$date_completed $action</td>
                    </tr>
HTML;
        }
        $html .= <<<HTML
                </tbody>
            </table>
HTML;
        return $html;
    }

    protected abstract function get_url($id, $key, $job);
    protected abstract function check_for_indent($job);

}

