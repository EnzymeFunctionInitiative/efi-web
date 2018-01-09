<?php

include_once 'functions.class.inc.php';

class efi_statistics 
{

    public static function num_generate_per_month($db, $recentOnly = false) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "MONTHNAME(generate_time_created) as month, ";
        $sql .= "YEAR(generate_time_created) as year, ";
        $sql .= "SUM(IF(generate_type='FAMILIES' AND generate_status='FINISH',1,0)) as num_success_option_b, ";
        $sql .= "SUM(IF(generate_type='FAMILIES' AND generate_status='FAILED' AND NOT generate_sequence_max,1,0)) as num_failed_option_b, ";
        $sql .= "SUM(IF(generate_type='FAMILIES' AND generate_status='FAILED' AND generate_sequence_max,1,0)) as num_failed_seq_option_b, ";
        $sql .= "SUM(IF(generate_type='BLAST' AND generate_status='FINISH',1,0)) as num_success_option_a, ";
        $sql .= "SUM(IF(generate_type='BLAST' AND generate_status='FAILED',1,0)) as num_failed_option_a, ";
        $sql .= "SUM(IF(generate_type='FASTA' AND generate_status='FINISH',1,0)) as num_success_option_c, ";
        $sql .= "SUM(IF(generate_type='FASTA' AND generate_status='FAILED',1,0)) as num_failed_option_c, ";
        $sql .= "SUM(IF(generate_type='FASTA_ID' AND generate_status='FINISH',1,0)) as num_success_option_c_id, ";
        $sql .= "SUM(IF(generate_type='FASTA_ID' AND generate_status='FAILED',1,0)) as num_failed_option_c_id, ";
        $sql .= "SUM(IF(generate_type='ACCESSION' AND generate_status='FINISH',1,0)) as num_success_option_d, ";
        $sql .= "SUM(IF(generate_type='ACCESSION' AND generate_status='FAILED',1,0)) as num_failed_option_d, ";
        $sql .= "SUM(IF(generate_type='COLORSSN' AND generate_status='FINISH',1,0)) as num_success_option_color, ";
        $sql .= "SUM(IF(generate_type='COLORSSN' AND generate_status='FAILED',1,0)) as num_failed_option_color, ";
        $sql .= "SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(generate_time_completed,generate_time_started)))) as total_time ";
        $sql .= "FROM generate ";
        if ($recentOnly)
            $sql .= "WHERE TIMESTAMPDIFF(MONTH,generate_time_created,CURRENT_TIMESTAMP) <= 7 ";
        $sql .= "GROUP BY MONTH(generate_time_created),YEAR(generate_time_created) ORDER BY year,MONTH(generate_time_created)";
        return $db->query($sql);
    }

    public static function num_analysis_per_month($db, $recentOnly = false) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "MONTHNAME(analysis_time_created) as month, ";
        $sql .= "YEAR(analysis_time_created) as year, ";
        $sql .= "SUM(IF(analysis_status='FINISH',1,0)) as num_success, ";
        $sql .= "SUM(IF(analysis_status='FAILED',1,0)) as num_failed, ";
        $sql .= "SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(analysis_time_completed,analysis_time_started)))) as total_time ";
        $sql .= "FROM analysis ";
        if ($recentOnly)
            $sql .= "WHERE TIMESTAMPDIFF(DAY,analysis_time_created,CURRENT_TIMESTAMP) <= 180 ";
        $sql .= "GROUP BY MONTH(analysis_time_created),YEAR(analysis_time_created) ORDER BY year,MONTH(analysis_time_created)";
        return $db->query($sql);
    }

    public static function num_generate_jobs($db) {
        $sql = "SELECT count(*) as count FROM generate";
        $result = $db->query($sql);
        return $result[0]['count'];
    }

    public static function num_analysis_jobs($db) {
        $sql = "SELECT count(*) as count FROM analysis";
        $result = $db->query($sql);
        return $result[0]['count'];
    }

    public static function get_unique_users($db) {
        $sql = "SELECT DISTINCT(generate_email) as email, ";
        $sql .= "MAX(generate_time_created) as last_job_time, ";
        $sql .= "COUNT(1) as num_jobs ";
        $sql .= "FROM generate ";
        $sql .= "GROUP BY generate_email ";
        $sql .= "ORDER BY generate_email ASC";
        return $db->query($sql);
    }

    public static function num_unique_users($db) {
        $result = self::get_unique_users($db);
        return count($result);

    }
    public static function get_jobs($db,$month,$year) {
        $sql = "SELECT generate.generate_email as 'Email', ";
        $sql .= "generate.generate_id as 'EFI-EST ID', ";
        $sql .= "generate.generate_type as 'Option Selected', ";
        $sql .= "generate_status as 'Generate Step Status', ";
        $sql .= "generate_time_started as 'Generate Time Started', ";
        $sql .= "generate_time_completed as 'Generate Time Completed', ";
        $sql .= "generate_params, ";
        $sql .= "analysis.analysis_status as 'Analysis Step Status', ";
        $sql .= "analysis.analysis_min_length as 'Minimum Length', ";
        $sql .= "analysis.analysis_max_length as 'Maximum Length', ";
        $sql .= "analysis.analysis_evalue as 'Alignment Score', ";
        $sql .= "analysis.analysis_name as 'Name', ";
        $sql .= "analysis.analysis_time_started as 'Analysis Time Started', ";
        $sql .= "analysis.analysis_time_completed as 'Analysis Time Completed' ";
        $sql .= "FROM generate ";
        $sql .= "LEFT JOIN analysis ON analysis.analysis_generate_id=generate.generate_id ";
        $sql .= "WHERE MONTH(analysis.analysis_time_completed)='" . $month . "' ";
        $sql .= "AND YEAR(analysis.analysis_time_completed)='" . $year . "' ";
        $sql .= "ORDER BY generate.generate_id ASC";

        $results = $db->query($sql);
        for ($i = 0; $i < count($results); $i++) {
            $res_obj = functions::decode_object($results[$i]['generate_params']);
            $results[$i]['Blast'] = $res_obj['generate_blast'];
            $results[$i]['Families'] = $res_obj['generate_families'];
            $results[$i]['Number of Sequences'] = $res_obj['generate_num_seq'];
        }
        //$sql .= "generate.generate_blast as Blast, ";
        //$sql .= "generate.generate_families as Families, ";
        //$sql .= "generate_num_seq as 'Number of Sequences', ";

        return $results;
    }


    public static function get_generate($db,$month,$year) {
        $sql = "SELECT generate.generate_email as 'Email', ";
        $sql .= "generate.generate_id as 'Generate ID', ";
        $sql .= "generate.generate_type as 'Option Selected', ";
        $sql .= "generate_status as 'Generate Step Status', ";
        $sql .= "generate_time_created as 'Time Submitted', ";
        $sql .= "generate_time_started as 'Time Started', ";
        $sql .= "generate_time_completed as 'Time Completed', ";
        $sql .= "generate_key as 'Key', ";
        $sql .= "generate_status as 'Status', ";
        $sql .= "generate_params ";
        $sql .= "FROM generate ";
        $sql .= "WHERE MONTH(generate.generate_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(generate.generate_time_created)='" . $year . "' ";
        $sql .= "ORDER BY generate.generate_id ASC";
        
        $results = $db->query($sql);
        for ($i = 0; $i < count($results); $i++) {
            $res_obj = functions::decode_object($results[$i]['generate_params']);
            $results[$i]['Blast'] = $res_obj['generate_blast'];
            $results[$i]['Families'] = $res_obj['generate_families'];
            $results[$i]['E-Value'] = $res_obj['generate_evalue'];
            $results[$i]['Number of Sequences'] = $res_obj['generate_num_seq'];
        }
        //$sql .= "generate.generate_blast as Blast, ";
        //$sql .= "generate.generate_families as Families, ";
        //$sql .= "generate.generate_evalue as 'E-Value', ";
        //$sql .= "generate_num_seq as 'Number of Sequences', ";

        return $results;
    }

    public static function get_analysis($db,$month,$year) {
        $sql = "SELECT generate.generate_email as 'Email', ";
        $sql .= "generate.generate_id as 'Generate ID', ";
        $sql .= "generate_key as 'Key', ";
        $sql .= "analysis_id as 'Analysis ID', ";
        $sql .= "analysis_time_created as 'Time Submitted', ";
        $sql .= "analysis.analysis_status as 'Status', ";
        $sql .= "analysis.analysis_min_length as 'Minimum Length', ";
        $sql .= "analysis.analysis_max_length as 'Maximum Length', ";
        $sql .= "analysis.analysis_evalue as 'Alignment Score', ";
        $sql .= "analysis.analysis_name as 'Name', ";
        $sql .= "analysis.analysis_time_started as 'Time Started', ";
        $sql .= "analysis.analysis_time_completed as 'Time Completed' ";
        $sql .= "FROM generate ";
        $sql .= "LEFT JOIN analysis ON analysis.analysis_generate_id=generate.generate_id ";
        $sql .= "WHERE MONTH(analysis.analysis_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(analysis.analysis_time_created)='" . $year . "' ";
        $sql .= "ORDER BY generate.generate_id ASC";
        return $db->query($sql);
    }


    public static function get_generate_daily_jobs($db,$month,$year) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "DATE(generate.generate_time_created) as day ";
        $sql .= "FROM generate ";
        $sql .= "WHERE MONTH(generate.generate_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(generate.generate_time_created)='" . $year . "' ";
        $sql .= "GROUP BY DATE(generate.generate_time_created) ";
        $sql .= "ORDER BY DATE(generate.generate_time_created) ASC";
        $result = $db->query($sql);
        return self::get_day_array($result,'day','count',$month,$year);
    }

    public static function get_analysis_daily_jobs($db,$month,$year) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "DATE(analysis.analysis_time_created) as day ";
        $sql .= "FROM analysis ";
        $sql .= "WHERE MONTH(analysis.analysis_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(analysis.analysis_time_created)='" . $year . "' ";
        $sql .= "GROUP BY DATE(analysis.analysis_time_created) ";
        $sql .= "ORDER BY DATE(analysis.analysis_time_created) ASC";
        $result = $db->query($sql);
        return self::get_day_array($result,'day','count',$month,$year);
    }

    public static function get_day_array($data,$day_column,$data_column,$month,$year) {
        $days = cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $new_data = array();
        for($i=1;$i<=$days;$i++){
            $exists = false;
            if (count($data) > 0) {
                foreach($data as $row) {
                    $day = date("d",strtotime($row[$day_column]));
                    if ($day == $i) {
                        //array_push($new_data,array($day_column=>$i,
                        //                      $data_column=>$row[$data_column]));
                        array_push($new_data,$row);
                        $exists = true;
                        break(1);
                    }
                }
            }
            if (!$exists) {
                $day = $year . "-" . $month . "-" . $i;
                array_push($new_data,array($day_column=>$day,$data_column=>0));
            }
            $exists = false;
        }
        return $new_data;
    }

}


?>
