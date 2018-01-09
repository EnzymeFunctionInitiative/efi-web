<?php

class scheduler_local extends scheduler {



        public function get_num_queued($username = "") {
                $user_cmd = "";
                if ($username != "") {
                        $user_cmd = "-u " . $username;
                }
                $output;
                $exec = "qstat -t " . $user_cmd . " " . $this->get_queue_name() . " | tail -n +6 | wc -l";
                exec($exec,$output);
                $num_queued = $output[0];
                return $num_queued;
        }


	////////////////Private Functions//////////////////

        private function get_queue_info($queue_parameter) {
                $output;
                $exec = "qstat -Qf " . $this->get_queue_name() . " | grep $queue_parameter | cut -d ' ' -f 7";
                exec($exec,$output);
                return $output[0];


        }


}






?>
