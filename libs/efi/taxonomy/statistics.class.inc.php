<?php
namespace efi\taxonomy;

require_once(__DIR__."/../../../init.php");

use \efi\global_functions;


class statistics {
    public static function num_taxonomy_per_month($db, $recent_only = false) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "MONTHNAME(generate_time_created) as month, ";
        $sql .= "YEAR(generate_time_created) as year, ";
        $sql .= "SUM(IF(generate_status='FINISH',1,0)) as num_success, ";
        $sql .= "SUM(IF(generate_status='FAILED',1,0)) as num_failed, ";
        $sql .= "SUM(TIME_TO_SEC(TIMEDIFF(IF(generate_time_completed>'0000-00-00 00:00:00',generate_time_completed,generate_time_started),generate_time_started))) as total_time ";
        $sql .= "FROM generate WHERE generate_is_tax_job = 1 ";
        if ($recent_only)
            $sql .= "AND TIMESTAMPDIFF(DAY,generate_time_created,CURRENT_TIMESTAMP) <= 180 ";
        $sql .= "GROUP BY MONTH(generate_time_created),YEAR(generate_time_created) ORDER BY year,MONTH(generate_time_created)";
        $results = $db->query($sql);

        for ($i = 0; $i < count($results); $i++) {
            $results[$i]['VALUES'] = array();
        }

        return $results;
    }

    public static function get_jobs($db, $month, $year) {
        $sql = "SELECT generate.generate_email as 'Email', ";
        $sql .= "generate.generate_id as 'Job ID', ";
        $sql .= "generate.generate_type as 'Job Type', ";
        $sql .= "generate_status as 'Status', ";
        $sql .= "generate_time_created as 'Time Submitted', ";
        $sql .= "generate_time_started as 'Time Started', ";
        $sql .= "generate_time_completed as 'Time Completed', ";
        $sql .= "generate_key as 'Key' ";
        $sql .= "FROM generate ";
        $sql .= "WHERE MONTH(generate.generate_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(generate.generate_time_created)='" . $year . "' ";
        $sql .= "AND generate_is_tax_job = 1 ";
        $sql .= "ORDER BY generate.generate_id ASC";
        
        $results = $db->query($sql);
        for ($i = 0; $i < count($results); $i++) {
            $results[$i]['Time Started'] = global_functions::format_short_date($results[$i]['Time Started']);
            $results[$i]['Time Completed'] = global_functions::format_short_date($results[$i]['Time Completed']);
            $results[$i]['Time Submitted'] = global_functions::format_short_date($results[$i]['Time Submitted']);
        }

        return $results;
    }

    public static function get_daily_jobs($db, $month, $year) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "DATE(generate.generate_time_created) as day ";
        $sql .= "FROM generate ";
        $sql .= "WHERE MONTH(generate.generate_time_created)='" . $month . "' ";
        $sql .= "AND YEAR(generate.generate_time_created)='" . $year . "' ";
        $sql .= "AND generate_is_tax_job = 1 ";
        $sql .= "GROUP BY DATE(generate.generate_time_created) ";
        $sql .= "ORDER BY DATE(generate.generate_time_created) ASC";
        $result = $db->query($sql);
        return global_functions::get_day_array($result, 'day', 'count', $month, $year);
    }

}


