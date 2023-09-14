<?php
namespace efi\cgfp;

use \efi\cgfp\functions;


class cgfp_statistics {

    public static function get_identify_daily_jobs($db, $month, $year) {
        return self::get_table_daily_jobs($db, "identify", $month, $year);
    }

    public static function get_quantify_daily_jobs($db, $month, $year) {
        return self::get_table_daily_jobs($db, "quantify", $month, $year);
    }

    private static function get_table_daily_jobs($db, $table, $month, $year) {
        $sql = "SELECT count(1) as count, ";
        $sql .= "DATE({$table}.{$table}_time_created) as day ";
        $sql .= "FROM {$table} ";
        $sql .= "WHERE MONTH({$table}.{$table}_time_created)='$month' ";
        $sql .= "AND YEAR({$table}.{$table}_time_created)='$year' ";
        $sql .= "GROUP BY DATE({$table}.{$table}_time_created) ";
        $sql .= "ORDER BY DATE({$table}.{$table}_time_created) ASC";
        $result = $db->query($sql);
        return self::get_day_array($result, 'day', 'count', $month, $year);
    }

    public static function get_day_array($data, $day_column, $data_column, $month, $year) {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $new_data = array();
        for($i=1;$i<=$days;$i++){
            $exists = false;
            if (count($data) > 0) {
                foreach($data as $row) {
                    $day = date("d", strtotime($row[$day_column]));
                    if ($day == $i) {
                        array_push($new_data, $row);
                        $exists = true;
                        break(1);
                    }
                }
            }
            if (!$exists) {
                $day = $year . "-" . $month . "-" . $i;
                array_push($new_data, array($day_column=>$day, $data_column=>0));
            }
            $exists = false;
        }
        return $new_data;
    }

    private static function format_date($date_str) {
        if ($date_str == "NULL" || !$date_str)
            return "";
        $date = date_create($date_str);
        $formatted = date_format($date, "n/j h:i A");
        return $formatted;
    }
}


