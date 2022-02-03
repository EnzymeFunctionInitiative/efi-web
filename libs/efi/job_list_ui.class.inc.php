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
            $comp = $row["${the_table}_time_completed"];
            if (substr($comp, 0, 4) == "0000") {
                $comp = $row["${the_table}_status"]; // "RUNNING";
                if ($comp == "NEW")
                    $comp = "PENDING";
            } else {
                $comp = date_format(date_create($comp), "n/j h:i A");
            }
            $params = global_functions::decode_object($row["${the_table}_params"]);
            $filename = pathinfo($params["filename"], PATHINFO_BASENAME);
            $job_name = $this->get_job_name($filename, $params);

            $id = $row["${the_table}_id"];
            $job_info = array("id" => $id, "key" => $row["${the_table}_key"], "filename" => $job_name, "completed" => $comp, "is_child" => false);
            
            $extra_job_info = $this->get_job_info($row, $params);
            if (is_array($extra_job_info)) {
                $job_info = array_merge($job_info, $extra_job_info);
            } else {
                array_push($jobs, $job_info);
                $index_map[$id] = $idx;
                $idx++;
            }
        }

        $replace_jobs = $this->post_process_job_list($jobs);
        if (is_array($replace_jobs))
            $jobs = $replace_jobs;

        return $jobs;
    }

    protected abstract function get_extra_job_info($row, $params);
    protected abstract function get_job_name($filename, $params);
    protected abstract function post_process_job_list($jobs);

    public function output_job_list($jobs, $is_example = false) {
        $html = <<<HTML
            <table class="pretty-nested" style="table-layout:fixed">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
HTML;

        $example_arg = $is_example ? "&x=1" : "";

        $last_bg_color = "#eee";
        for ($i = 0; $i < count($jobs); $i++) {
            $key = $jobs[$i]["key"];
            $id = $jobs[$i]["id"];
            $name = $jobs[$i]["filename"];
            $date_completed = $jobs[$i]["completed"];
            $is_active = $date_completed == "PENDING" || $date_completed == "RUNNING";

            $url = $this->get_url($id, $key);
            $link_start = $is_active ? "" : '<a class="' . $this->css_hl . '" href="' . $url . $example_arg . '">';
            $link_end = $is_active ? "" : "</a>";
            $link_start .= "<span title='$id'>";
            $link_end = "</span>" . $link_end;
            $id_text = "$link_start${id}$link_end";
        
            $name_style = "";
            if ($this->check_for_indent($jobs[$i])) {
                $id_text = "";
                $name_style = "style=\"padding-left: 50px;\"";
            } else {
                if ($last_bg_color == "#eee")
                    $last_bg_color = "#fff";
                else
                    $last_bg_color = "#eee";
            }
        
            $html .= <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$id_text</td>
                        <td $name_style>$link_start${name}$link_end</td>
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

    protected abstract function get_url($id, $key, $job);
    protected abstract function check_for_indent($job);

}

