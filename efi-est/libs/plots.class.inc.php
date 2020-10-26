<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__BASE_DIR__."/libs/global_settings.class.inc.php");
require_once(__DIR__."/functions.class.inc.php");

class plots {

    public static function get_plot($job, $plot_id) {
        if ($plot_id == plot_edge_evalue::PLOT_ID)
            return new plot_edge_evalue($job);
        else
            return NULL;
    }
}

abstract class plot {

    abstract function has_data();
    abstract function get_data();
    abstract function render_data(); // to JSON
    abstract function render_plotly_config(); // JS code
    abstract function get_trace_var();
    abstract function get_layout_var();

    public static function get_var($id, $var) { return "${id}_$var"; }
}

class plot_edge_evalue extends plot {

    const PLOT_ID = "edge_evalue";

    private $evalue_data_file = "evalue.tab";
    private $job_obj;

    function __construct($job_obj) {
        $this->job_obj = $job_obj;
    }

    public function get_trace_var() {
        return self::get_var(self::PLOT_ID, "traces");
    }

    public function get_layout_var() {
        return self::get_var(self::PLOT_ID, "layout");
    }

    public function get_data() {
        $results_path = functions::get_results_dir() . "/" . $this->job_obj->get_output_dir();
        $file_path = $results_path . "/" . $this->evalue_data_file;
        if (!file_exists($file_path)) {
            return array();
        }

        $data = array();

        $fh = fopen($file_path, "r");
        while (!feof($fh)) {
            $line = fgets($fh, 1000);
            if (!$line)
                continue;
            $parts = str_getcsv($line, "\t");
            $data[$parts[0]] = $parts[2];
        }
        fclose($fh);

        return $data;
    }

    public function has_data() {
        $results_path = functions::get_results_dir() . "/" . $this->job_obj->get_output_dir();
        $file_path = $results_path . "/" . $this->evalue_data_file;
        return file_exists($file_path);
    }

    public function render_data() {
        $ev_data = $this->get_data();

        $check_size = true;
        if (count($ev_data) < 30)
            $check_size = false;

        $xvar = self::get_var(self::PLOT_ID, "x");
        $yvar = self::get_var(self::PLOT_ID, "y");
        $js = "var $xvar = [";
        foreach ($ev_data as $ev => $edge_sum) {
            if (!$check_size || $edge_sum > 100)
                $js .= "$ev,";
        }
        $js .= "];\n";
        $js .= "var $yvar = [";
        foreach ($ev_data as $ev => $edge_sum) {
            if (!$check_size || $edge_sum > 100)
                $js .= "$edge_sum,";
        }
        $js .= "];\n";

        $tracevar = $this->get_trace_var();
        $js .= <<<JS
var $tracevar = [{
    x: $xvar, 
    y: $yvar,
    type: 'scatter',
}];
JS;
        return $js;
    }

    public function render_plotly_config() {
        $tracevar = $this->get_trace_var();
        $layoutvar = $this->get_layout_var();
        $js = <<<JS
var $layoutvar = {
    title: 'Edge Count vs Alignment Score',
    xaxis: { title: 'Alignment Score', },
    yaxis: { title: 'Number of Edges', },
    width: 800,
//    height: 500,
};
JS;
        return $js;
    }
}

?>
