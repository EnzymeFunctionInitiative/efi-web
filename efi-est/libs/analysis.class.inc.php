<?php

require_once('Mail.php');
require_once('Mail/mime.php');

class analysis {

    ////////////////Private Variables//////////

    private $db; //mysql database object
    private $id;
    private $status;
    private $evalue;
    private $name;
    private $minimum;
    private $maximum;
    private $time_created;
    private $pbs_number;
    private $blast_id;
    private $generate_id;
    private $filter;
    private $time_started;
    private $time_completed;
    private $finish_file = "stats.tab.completed";
    private $sequence_file = "sequences.fa";
    private $stats_file = "stats.tab";
    protected $output_dir = "output";
    private $num_pbs_jobs = 16;
    private $filter_sequences;
    private $eol = PHP_EOL;
    private $db_version;
    private $beta;
    private $length_overlap;
    private $custom_clustering;
    private $custom_filename = "custom_cluster.txt";
    private $parent_id = 0; // The parent ID of the generate job that this analysis job is associated with
    private $cdhit_opt = "";
    private $job_type = "";
    protected $is_sticky = false;

    ///////////////Public Functions///////////

    public function __construct($db,$id = 0) {
        $this->db = $db;

        if ($id) {
            $this->load_analysis($id);
        }

        $this->beta = functions::get_release_status();
    }

    public function __destruct() {
    }

    public function get_status() { return $this->status; }
    public function get_id() { return $this->id; }
    public function get_generate_id() { return $this->generate_id; }
    public function get_evalue() { return $this->evalue; }
    public function get_min_length() { return $this->minimum; }
    public function get_max_length() { return $this->maximum; }
    public function get_time_created() { return $this->time_created; }
    public function get_pbs_number() { return $this->pbs_number; }
    public function get_name() { return $this->name; }
    public function get_filter() { return $this->filter; }
    public function get_time_started() { return $this->time_started; }
    public function get_time_completed() { return $this->time_completed; }
    public function get_unixtime_completed() { return strtotime($this->time_completed); }
    public function get_finish_file() { return $this->finish_file; }
    public function get_filter_sequences() { return $filter_sequences; }
    public function get_sequence_file() {
        return $this->sequence_file;
    }
    public function get_time_completed_formatted() {
        return functions::format_datetime(functions::parse_datetime($this->time_completed));
    }
    public function get_db_version() { return $this->db_version; }

    public function get_output_dir() {
        return $this->get_generate_id() . "/" . $this->output_dir;
    }
    public function get_cdhit_method_nice() {
        if ($this->cdhit_opt == "sb")
            return "ShortBRED";
        elseif ($this->cdhit_opt == "est+")
            return "EST/High Accuracy";
        else
            return "EST";
    }

    public function set_num_sequences_post_filter() {
        $num_seq = $this->get_num_sequences_post_filter();

        $sql = "UPDATE analysis ";
        $sql .= "SET analysis_filter_sequences='" . $num_seq . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . " LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->filter_sequences = $num_seq;
            return true;
        }
        return false;
    }		

    public function get_num_sequences_post_filter() {
        $root_dir = functions::get_results_dir();
        $directory = $root_dir . "/" . $this->get_output_dir() . "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $this->get_sequence_file();
        $num_seq = 0;
        if (file_exists($full_path)) {

            $exec = "cat " . $full_path . " | grep '>' | wc -l";
            $output = exec($exec);
            $output = trim(rtrim($output));
            list($num_seq,) = explode(" ",$output);

        }
        return $num_seq;
    }

    public function create($generate_id, $evalue, $name, $minimum, $maximum, $customFile = "", $cdhitOpt = "") {
        $errors = false;
        $message = "";		
        $filter = "eval";

        $hasCustomClustering = 0;
        if ($customFile && file_exists($customFile)) {
            $uploadDir = functions::get_uploads_dir();
            $destPath = "$uploadDir/custom_cluster_$generate_id.txt";
            move_uploaded_file($customFile, $destPath);
            $hasCustomClustering = 1;
        }

        if (!$this->verify_length($minimum,$maximum)) {
            $message = "<br><b>Please verify the minimum and maximum lengths.</b>";
            $errors = true;
        }
        
        $name = $this->verify_network_name($name);
        if ($name === false) {
            $message .= "<br><b>Please verify the network name.</b>";
            $message .= "<br><b>It can contain only letters, numbers, dash, and underscore.</b>";
            $errors = true;
        }
        
        if (!$hasCustomClustering && !$this->verify_evalue($evalue)) {
            $message .= "<br><b>Please verify the alignment score.</b>";
            $errors = true;	
        }

        if (!$errors) {
            $insert_array = array('analysis_generate_id'=>$generate_id,
                'analysis_evalue'=>$evalue,
                'analysis_name'=>$name,
                'analysis_min_length'=>$minimum,
                'analysis_max_length'=>$maximum,
                'analysis_filter'=>$filter,
                'analysis_custom_cluster'=>$hasCustomClustering,
            );
            if ($cdhitOpt)
                $insert_array['analysis_cdhit_opt'] = $cdhitOpt;

            $result = $this->db->build_insert("analysis",$insert_array);
            if ($result) {
                return array('RESULT'=>true,'id'=>$result);
            } else {
                $message = "Unknown database error";
            }
        }
        return array('RESULT'=>!$errors,'MESSAGE'=>$message);
    }

    public function set_pbs_number($pbs_number) {
        $sql = "UPDATE analysis SET analysis_pbs_number='" . $pbs_number . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "'";
        $this->db->non_select_query($sql);
        $this->pbs_number = $pbs_number;
    }

    public function set_time_started() {
        $current_time = date("Y-m-d H:i:s",time());
        $sql = "UPDATE analysis SET analysis_time_started='" . $current_time . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->time_started = $current_time;
    }

    public function set_time_completed() {
        $current_time = date("Y-m-d H:i:s",time());
        $sql = "UPDATE analysis SET analysis_time_completed='" . $current_time . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "' LIMIT 1";
        $this->db->non_select_query($sql);
        $this->time_completed = $current_time;
    }

    public function check_pbs_running() {
        $sched = strtolower(functions::get_cluster_scheduler());
        $jobNum = $this->get_pbs_number();
        $output = "";
        $exit_status = "";
        $exec = "";
        if ($sched == "slurm")
            $exec = "squeue --job $jobNum 2> /dev/null | grep $jobNum";
        else
            $exec = "qstat $jobNum 2> /dev/null | grep $jobNum";
        exec($exec,$output,$exit_status);
        if (count($output) == 1) {
            return true;
        }
        else {
            return false;
        }
    }

    public function check_finish_file() {
        $directory = functions::get_results_dir() . "/".  $this->get_output_dir();
        $directory .= "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $this->get_finish_file();
        return file_exists($full_path);
    }

    public function set_status($status) {

        $sql = "UPDATE analysis ";
        $sql .= "SET analysis_status='" . $status . "' ";
        $sql .= "WHERE analysis_id='" . $this->get_id() . "' LIMIT 1";
        $result = $this->db->non_select_query($sql);
        if ($result) {
            $this->status = $status;
        }
    }

    public function get_network_stats() {
        $results_dir = functions::get_results_dir();
        $file = $results_dir . "/" . $this->get_output_dir();
        $file .= "/" . $this->get_network_dir() . "/" . $this->stats_file;
        $file_handle = @fopen($file,"r") or die("Error opening " . $this->stats_file . "\n");
        $i = 0; 
        $stats_array = array();
        $keys = array('File','Nodes','Edges','Size');
        $row = 0;
        while (($data = fgetcsv($file_handle,0,"\t")) !== FALSE) {

            $stats_row = array();
            if ($row == 1) {
                $result = array_splice($data,1,1);
                $stats_row = array_combine($keys, $data);
            } elseif ($row > 1) {
                $stats_row = array_combine($keys, $data);
            }

            if (count($stats_row)) {
                $raw_file = $stats_row["File"];
                $percent_identity = substr($raw_file, strrpos($raw_file, "-") + 1);
                $percent_identity = substr($percent_identity, 0, strrpos($percent_identity, "_"));
                $percent_identity = str_replace(".", "", $percent_identity);

                $rel_path = $this->get_output_dir() . "/" . $this->get_network_dir() . "/" . $raw_file;
                $stats_row["RelPath"] = $rel_path;
                $stats_row["PctId"] = $row == 1 ? "full" : $percent_identity;

                array_push($stats_array, $stats_row);
            }

            $row++;
        }
        fclose($file_handle);
        return $stats_array;
    }


    public function download_network($file) {
        $root_dir = functions::get_results_dir();	
        $directory = $root_dir . "/" . $this->get_output_dir() . "/" . $this->get_network_dir();
        $full_path = $directory . "/" . $file;
        if (file_exists($full_path)) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application-download');
            //header('COntent-Type: text/xml');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Length: ' . filesize($full_path));
            ob_clean();
            readfile($full_path);
        }
        else {
            return false;
        }
    }

    public function get_stats_full_path() {
        $path = functions::get_web_root() . "/results/" . $this->get_output_dir() . "/" . $this->get_network_dir() . "/" . $this->stats_file;
        return $path;
    }

    public function email_complete() {

        $stepa = new stepa($this->db,$this->get_generate_id());	
        $subject = $this->beta . "EFI-EST - Your SSN has now been completed and is available for download";
        $from = "EFI-EST <" .functions::get_admin_email() . ">";
        $to = $stepa->get_email();

        $web_root = functions::get_web_root();
        $url = $web_root . "/stepe.php";
        $full_url = $url . "?" . http_build_query(array('id'=>$this->get_generate_id(),
            'key'=>$stepa->get_key(),'analysis_id'=>$this->get_id()));
        $gnt_url = functions::get_gnt_web_root();

        $plain_email = "";
        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-EST." . $this->eol;

        //plain text email
        $plain_email .= "Your EFI-EST SSN has been generated and is available for download." . $this->eol . $this->eol;
        $plain_email .= "To access the results, please go to THE_URL" . $this->eol;
        $plain_email .= "This data will only be retained for " . functions::get_retention_days() . " days." . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_stepa_job_info() . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;

        $plain_email .= "The coloring utility recently developed will help downstream analysis of your SSN. Try it! ";
        $plain_email .= "It can be found at the bottom of the $web_root/stepa.php#colorssn page." . $this->eol . $this->eol;
        $plain_email .= "Have you tried exploring Genome Neighborhood Networks (GNTs) from your favorite SSNs? ";
        $plain_email .= "GNT_URL" . $this->eol . $this->eol;

        $plain_email .= "Cite us:" . $this->eol . $this->eol;
        $plain_email .= "John A. Gerlt, Jason T. Bouvier, Daniel B. Davidson, Heidi J. Imker, Boris Sadkhin, David R. ";
        $plain_email .= "Slater, Katie L. Whalen, Enzyme Function Initiative-Enzyme Similarity Tool (EFI-EST): A web tool ";
        $plain_email .= "for generating protein sequence similarity networks, Biochimica et Biophysica Acta (BBA) - Proteins ";
        $plain_email .= "and Proteomics, Volume 1854, Issue 8, 2015, Pages 1019-1037, ISSN 1570-9639, ";
        $plain_email .= "DX_URL ";
        $plain_email .= "(SCI_URL)" . $this->eol . $this->eol;
        $plain_email .= functions::get_email_footer() . $this->eol;

        $dx_url = "http://dx.doi.org/10.1016/j.bbapap.2015.04.015";
        $sci_url = "http://www.sciencedirect.com/science/article/pii/S1570963915001120";

        $html_email = nl2br($plain_email, false);
        $plain_email = str_replace("THE_URL", $full_url, $plain_email);
        $plain_email = str_replace("GNT_URL", $gnt_url, $plain_email);
        $plain_email = str_replace("DX_URL", $dx_url, $plain_email);
        $plain_email = str_replace("SCI_URL", $sci_url, $plain_email);
        $html_email = str_replace("THE_URL", "<a href=\"" . htmlentities($full_url) . "\">" . $full_url . "</a>", $html_email);
        $html_email = str_replace("GNT_URL", "<a href=\"" . htmlentities($gnt_url) . "\">" . $gnt_url . "</a>", $html_email);
        $html_email = str_replace("DX_URL", "<a href=\"" . htmlentities($dx_url) . "\">" . $dx_url. "</a>", $html_email);
        $html_email = str_replace("SCI_URL", "<a href=\"" . htmlentities($sci_url) . "\">" . $sci_url. "</a>", $html_email);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    public function email_failed() { //$from_email,$web_root,$footer) {
        $web_root = "";
        $footer = "";

        $generate = new stepa($this->db,$this->get_generate_id());
        $subject = $this->beta . "EFI-EST - SSN finalization failed";
        $to = $generate->get_email();
        $from = "EFI-EST <" .functions::get_admin_email() . ">";
        //$url = $web_root . "/stepa.php";

        $plain_email = "";
        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-EST." . $this->eol;

        $plain_email .= "The SSN finalization failed. Please contact us with the EFI-EST Job ID to determine ";
        $plain_email .= "why this occurred." . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= functions::get_email_footer() . $this->eol;
        
        $html_email = nl2br($plain_email, false);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    public function email_started() {
        $stepa = new stepa($this->db,$this->get_generate_id());
        $subject = $this->beta . "EFI-EST - Your SSN is being finalized";
        $from = "EFI-EST <" .functions::get_admin_email() . ">";
        $to = $stepa->get_email();

        $full_url = functions::get_web_root() . "/" . functions::get_job_status_script();
        $full_url .= "?" . http_build_query(array('id'=>$this->get_generate_id(),
            'key'=>$stepa->get_key(),'analysis_id'=>$this->get_id()));

        $plain_email = "";
        if ($this->beta) $plain_email = "Thank you for using the beta site of EFI-EST." . $this->eol;

        //plain text email
        $plain_email .= "Your SSN is being finalized." . $this->eol;
        $plain_email .= "You will receive an email once it is completed." . $this->eol . $this->eol;
        $plain_email .= "Submission Summary:" . $this->eol . $this->eol;
        $plain_email .= $this->get_stepa_job_info();
        $plain_email .= $this->get_job_info() . $this->eol . $this->eol;
        $plain_email .= "To check on the status of this job go to THE_STATUS_URL" . $this->eol . $this->eol;
        $plain_email .= "If no new email is received after 48 hours, please contact us and mention the EFI-EST ";
        $plain_email .= "Job ID that corresponds to this email." . $this->eol . $this->eol;
        $plain_email .= functions::get_email_footer() . $this->eol;

        $html_email = nl2br($plain_email, false);
        $plain_email = str_replace("THE_STATUS_URL", $full_url, $plain_email);
        $html_email = str_replace("THE_STATUS_URL", "<a href=\"" . htmlentities($full_url) . "\">" . $full_url . "</a>", $html_email);

        $message = new Mail_mime(array("eol"=>$this->eol));
        $message->setTXTBody($plain_email);
        $message->setHTMLBody($html_email);
        $body = $message->get();
        $extraheaders = array("From"=>$from,
            "Subject"=>$subject
        );
        $headers = $message->headers($extraheaders);

        $mail = Mail::factory("mail");
        $mail->send($to,$headers,$body);
    }

    public function run_job($is_debug = false) {
        if ($this->available_pbs_slots()) {

            $sched = functions::get_cluster_scheduler();

            //Setup Directories
            $job_dir = functions::get_results_dir() . "/" . $this->get_generate_id();
            $relative_output_dir = "output";
            $network_dir = $this->get_network_dir();
            $gen_output_dir = "$job_dir/$relative_output_dir";
            $full_output_dir = "$gen_output_dir/$network_dir";

            $parent_dir_opt = "";
            $parent_id = $this->parent_id;
            if ($parent_id > 0) {
                $parent_dir = functions::get_results_dir() . "/$parent_id/$relative_output_dir";
                if (!@file_exists($gen_output_dir)) {
                    mkdir($gen_output_dir, 0755, true); // This job is a parent
                }
                $parent_dir_opt = "-parent-id $parent_id -parent-dir $parent_dir";
            }

            if (@file_exists($full_output_dir)) {
                functions::rrmdir($full_output_dir);
            }
            if ($this->custom_clustering) {
                mkdir($full_output_dir, 0777, true); // recursively create
                $start_path = functions::get_uploads_dir() . "/custom_cluster_" . $this->get_generate_id() . ".txt";
                $end_path = "$full_output_dir/" . $this->custom_filename;
                copy($start_path, $end_path);
            }

            $cdhit_opt = "";
            if ($this->cdhit_opt) {
                if ($this->cdhit_opt == "sb" || $this->cdhit_opt == "est+")
                    $cdhit_opt = $this->cdhit_opt;
            }

            $current_dir = getcwd();
            if (file_exists($job_dir)) {
                chdir($job_dir);
                $exec = "source /etc/profile\n";
                $exec .= "module load " . functions::get_efi_module() . "\n";
                $exec .= "module load " . functions::get_efidb_module() . "\n";
                $exec .= "analyzedata.pl ";
                $exec .= "-maxlen " . $this->get_max_length() . " ";
                $exec .= "-minlen " . $this->get_min_length() . " ";
                $exec .= "-filter " . $this->get_filter() . " ";
                $exec .= "-title " . $this->get_name() . " ";
                $exec .= "-minval " . $this->get_evalue() . " ";
                $exec .= "-maxfull " . functions::get_maximum_number_ssn_nodes() . " ";
                $exec .= "-tmp " . $relative_output_dir . " ";
                $exec .= "-job-id " . $this->get_generate_id() . " ";
                $exec .= "-queue " . functions::get_analyse_queue() . " ";
                if ($this->custom_clustering) {
                    $exec .= " -custom-cluster-file " . $this->custom_filename;
                    $exec .= " -custom-cluster-dir " . $network_dir;
                }
                if ($cdhit_opt)
                    $exec .= " -cdhit-opt $cdhit_opt";
                if ($this->length_overlap)
                    $exec .= "-lengthdif " . $this->length_overlap . " ";
                if ($sched)
                    $exec .= " -scheduler " . $sched . " ";
                if ($parent_id > 0)
                    $exec .= " " . $parent_dir_opt;
                if ($this->job_type == "FASTA_ID" || $this->job_type == "FASTA")
                    $exec .= "-include-sequences ";

                $exec .= " 2>&1 ";

                $exit_status = 1;
                $output_array = array();

                functions::log_message($exec);
                $output = exec($exec,$output_array,$exit_status);

                chdir($current_dir);

                $output = trim(rtrim($output));
                if ($sched == "slurm")
                    $pbs_job_number = $output;
                else
                    $pbs_job_number = substr($output,0,strpos($output,"."));
                if ($pbs_job_number && !$exit_status) {
                    $this->set_pbs_number($pbs_job_number);
                    $this->set_time_started();
                    $this->set_status(__RUNNING__);
                    $this->email_started();
                    return array('RESULT'=>true,
                        'PBS_NUMBER'=>$pbs_job_number,
                        'EXIT_STATUS'=>$exit_status,
                        'MESSAGE'=>'Job Successfully Submitted');
                }
                else {
                    return array('RESULT'=>false,
                        'EXIT_STATUS'=>$exit_status,
                        'MESSAGE'=>$output_array[7]);
                }

            }
            else {
                return array('RESULT'=>false,'EXIT_STATUS'=>1,'MESSAGE'=>'Directory ' . $job_dir . ' does not exist');
            }

        }
        else {
            return array('RESULT'=>false,'EXIT_STATUS'=>1,'MESSAGE'=>'Queue is full');
        }
    }

    ///////////////Private Functions///////////


    private function load_analysis($id) {
        $sql = "SELECT * FROM analysis INNER JOIN generate ON analysis_generate_id = generate_id WHERE analysis_id='" . $id . "' ";
        $sql .= "LIMIT 1";
        $result = $this->db->query($sql);
        if ($result) {
            $this->id = $id;
            $this->generate_id = $result[0]['analysis_generate_id'];
            $this->pbs_number = $result[0]['analysis_pbs_number'];
            $this->evalue = $result[0]['analysis_evalue'];
            $this->name = $result[0]['analysis_name'];
            $this->minimum = $result[0]['analysis_min_length'];
            $this->maximum = $result[0]['analysis_max_length'];
            $this->time_created = $result[0]['analysis_time_created'];
            $this->status = $result[0]['analysis_status'];
            $this->filter = $result[0]['analysis_filter'];
            $this->time_started = $result[0]['analysis_time_started'];
            $this->time_completed = $result[0]['analysis_time_completed'];
            $this->filter_sequences = $result[0]['analysis_filter_sequences'];
            $this->db_version = functions::decode_db_version($result[0]['generate_db_version']);
            $this->parent_id = $result[0]['generate_parent_id'];
            $this->job_type = $result[0]['generate_type'];
            $this->cdhit_opt = array_key_exists('analysis_cdhit_opt', $result[0]) ? $result[0]['analysis_cdhit_opt'] : "";
            $has_custom = array_key_exists('analysis_custom_cluster', $result[0]) &&
                            $result[0]['analysis_custom_cluster'] == 1;
            $this->custom_clustering = $has_custom;
            $email = $result[0]['generate_email'];
            $this->is_sticky = functions::is_job_sticky($this->db, $this->generate_id, $email);
            //TODO: fix this. the field doesn't come from a database column anymore; it comes from the generate_params
            // field which is a JSON structure. that would mean it would need to be decoded to get the value. this
            // feature isn't used anymore.
            //$this->length_overlap = $result[0]['generate_length_overlap'];
        }
    }

    public function get_network_dir() {
        $path = "";
        if ($this->custom_clustering) {
            $path = "custom_cluster_" . $this->get_id();
        } else {
            $path = $this->get_filter() . "-" . $this->get_evalue();
            $path .= "-" . $this->get_min_length() . "-" . $this->get_max_length();
            if ($this->cdhit_opt == "sb" || $this->cdhit_opt == "est+")
                $path .= "-" . $this->cdhit_opt;
        }
        return $path;
    }

    private function verify_length($min,$max) {

        $result = true;
        if (!$this->is_integer($min)) {
            $result = false;
        }
        elseif (!$this->is_integer($max)) {
            $result = false;
        }
        elseif ($min >= $max) {
            $result = false;
        }
        return $result;
    }

    private function verify_network_name($name) {
        $result = true;
        if ($name == "") {
            $result = false;
        } else {
            $result = preg_replace('/[^A-Za-z0-9_-]/', '_', $name);
        }
        return $result;
    }

    private function verify_evalue($evalue) {
        $result = true;
        if ($evalue == "") {
            $result = false;
        }
        elseif (!$this->is_integer($evalue)) {
            $result = false;
        }
        return $result;
    }

    private function is_integer($value) {
        if (preg_match('/^\d+$/',$value)) {
            return true;
        }
        return false;
    }

    private function available_pbs_slots() {
        $queue = new queue(functions::get_analyse_queue());
        $num_queued = $queue->get_num_queued();
        $max_queuable = $queue->get_max_queuable();
        $num_user_queued = $queue->get_num_queued(functions::get_cluster_user());
        $max_user_queuable = $queue-> get_max_user_queuable();

        $result = false;
        if ($max_queuable - $num_queued < $this->num_pbs_jobs) {
            $result = false;
            $msg = "Analysis ID: " . $this->get_id() . " - ERROR: Queue " . functions::get_analyse_queue() . " is full.  Number in the queue: " . $num_queued;
        }
        elseif ($max_user_queuable - $num_user_queued < $this->num_pbs_jobs) {
            $result = false;
            $msg = "Analysis ID: " . $this->get_id() . " - ERROR: Number of Queued Jobs for user " . functions::get_cluster_user() . " is full.  Number in the queue: " . $num_user_queued;
        }
        else {
            $result = true;
            $msg = "Analysis ID: " . $this->get_id() . " - Number of queued jobs in queue " . functions::get_analyse_queue() . ": " . $num_queued . ", Number of queued user jobs: " . $num_user_queued;
        }
        functions::log_message($msg);
        return $result;
    }

    private function get_job_info() {
        $message = "Job ID: " . $this->get_id() . "\r\n";
        $message .= "Minimum Length: " . $this->get_min_length() . "\r\n";
        $message .= "Maximum Length: " . $this->get_max_length() . "\r\n";
        $message .= "Alignment Score: " . $this->get_evalue() . "\r\n";
        $message .= "Network Name: " . $this->get_name() . "\r\n";
        return $message;
    }

    private function get_stepa_job_info() {
        $stepa = new stepa($this->db,$this->get_generate_id());
        $job_type = $stepa->get_type();

        switch ($job_type) {
            case "BLAST":
                $stepa = new blast($this->db,$this->get_generate_id());
                break;
    
            case "FAMILIES": 
                $stepa = new generate($this->db,$this->get_generate_id());
                break;			
    
            case "FASTA":
                $stepa = new fasta($this->db,$this->get_generate_id());
                break;
    
            case "ACCESSION":
                $stepa = new accession($this->db, $this->get_generate_id());
                break;
    
            case "FASTA_ID":
                $stepa = new fasta($this->db,$this->get_generate_id(), "E");
                break;
        }

        $message = $stepa->get_job_info();
        return $message;
    }
    
    public function is_expired() {
        if (!$this->is_sticky && time() > $this->get_unixtime_completed() + functions::get_retention_secs()) {
            return true;
        } else {
            return false;
        }
    }
}

?>
