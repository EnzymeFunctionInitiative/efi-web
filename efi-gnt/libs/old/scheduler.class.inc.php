<?php

class scheduler {


        ////////////////Private Variables//////////

        protected $queue_name;
        
        ///////////////Public Functions///////////

        public function __construct($queue_name = 'default') {
                $this->queue_name = $queue_name;

        }

        public function __destruct() {
        

        }

        public function get_queue_name() { 
                return $this->queue_name;

        }
        public function get_max_queuable() { 
                return $this->get_queue_info('max_queuable');


        }
        public function get_max_user_queuable() {
                return $this->get_queue_info('max_user_queuable');

        }

        public function get_num_queued($username = "") {
        
	}

        public function check_pbs_running() {


        }

        ////////////////Private Functions//////////////////

        private function get_queue_info($queue_parameter) {

        }


        private function available_pbs_slots() {
                $queue = new queue(functions::get_generate_queue());
                $num_queued = $queue->get_num_queued();
                $max_queuable = $queue->get_max_queuable();
                $num_user_queued = $queue->get_num_queued(functions::get_cluster_user());
                $max_user_queuable = $queue-> get_max_user_queuable();

                $result = false;
                if ($max_queuable - $num_queued < $this->num_pbs_jobs) {
                        $result = false;
                        $msg = "ERROR: Queue " . functions::get_generate_queue() . " is full.  Number in the queue: " . $num_queued;
                }
                elseif ($max_user_queuable - $num_user_queued < $this->num_pbs_jobs) {
                        $result = false;
                        $msg = "ERROR: Number of Queued Jobs for user " . functions::get_cluster_user() . " is full.  Number in the queue: " . $num_user_queued;
                }
                else {
                        $result = true;
                        $msg = "Number of queued jobs in queue " . functions::get_generate_queue() . ": " . $num_queued . ", Number of queued user jobs: " . $num_user_queued;
                }
                functions::log_message($msg);
                return $result;
        }



}
?>
