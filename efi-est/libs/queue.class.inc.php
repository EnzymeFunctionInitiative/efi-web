<?php
require_once 'functions.class.inc.php';

class queue {

    ////////////////Private Variables//////////

    private $queue_name;
    private $is_slurm;

    ///////////////Public Functions///////////

    public function __construct($queue_name = 'default') {
        $this->queue_name = $queue_name;
        if (strtolower(functions::get_cluster_scheduler()) == "slurm") {
            $this->is_slurm = true;
        } else {
            $this->is_slurm = false;
        }
    }

    public function __destruct() {
    }

    public function get_queue_name() { 
        return $this->queue_name;
    }

    public function get_max_queuable() { 
        if ($this->is_slurm) {
            return functions::get_max_queuable_jobs();
        } else {
            return $this->get_queue_info('max_queuable');
        }
    }

    public function get_max_user_queuable() {
        if ($this->is_slurm) {
            return functions::get_max_user_queuable_jobs();
        } else {
            return $this->get_queue_info('max_user_queuable');
        }
    }

    public function get_num_queued($username = "") {
        $user_cmd = "";
        $queue_name = $this->get_queue_name();
        if ($username != "") {
            $user_cmd = "-u " . $username;
        }

        $output = "";
        $exec = "";
        if ($this->is_slurm) {
            $exec = "squeue -hr $user_cmd -p $queue_name | wc -l";
        } else {
            $exec = "qstat -t $user_cmd $queue_name | tail -n +6 | wc -l";
        }
        exec($exec,$output);
        $num_queued = $output[0];
        return $num_queued;
    }

    ////////////////Private Functions//////////////////

    private function get_queue_info($queue_parameter) {
        $queue_name = $this->get_queue_name();
        
        $output = "";
        if ($this->is_slurm) {
            // This doesn't work properly
            $exec = "sinfo -p $queue_name | grep $queue_parameter | cut -d ' ' -f 7";
        } else {
            $exec = "qstat -Qf $queue_name | grep $queue_parameter | cut -d ' ' -f 7";
        }

        exec($exec,$output);
        return $output[0];
    }
}
