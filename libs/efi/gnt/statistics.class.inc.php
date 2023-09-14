<?php
namespace efi\gnt;


class statistics 
{

    public static function num_per_month($db, $table = "gnn", $extra_where = "") {
        $sql = "SELECT count(1) as count, ";
        $sql .= "MONTHNAME({$table}_time_created) as month, ";
        $sql .= "YEAR({$table}_time_created) as year ";
        $sql .= "FROM $table ";
        if ($extra_where)
            $sql .= " WHERE $extra_where ";
        $sql .= "GROUP BY MONTH({$table}_time_created),YEAR({$table}_time_created) ORDER BY year,MONTH({$table}_time_created)";
        return $db->query($sql);
    }

    public static function num_per_month_aggregated($db) {
        $gnn = self::num_per_month($db, "gnn");
        $extra = "(diagram_type = 'DIRECT' OR diagram_type = 'DIRECT_ZIP')";
        $direct = self::num_per_month($db, "diagram", $extra);
        $extra = "diagram_type = 'BLAST'";
        $blast = self::num_per_month($db, "diagram", $extra);
        $extra = "diagram_type = 'ID_LOOKUP'";
        $id_lookup = self::num_per_month($db, "diagram", $extra);
        $extra = "diagram_type = 'FASTA'";
        $fasta = self::num_per_month($db, "diagram", $extra);

        $array_to_hash = function ($array) {
            $hash = array();
            for ($i = 0; $i < count($array); $i++) {
                $data = $array[$i];
                $key = $data["year"] . $data["month"];
                $hash[$key] = array("count" => $data["count"], "month" => $data["month"], "year" => $data["year"]);
            }
            return $hash;
        };

        $direct = $array_to_hash($direct);
        $blast = $array_to_hash($blast);
        $id_lookup = $array_to_hash($id_lookup);
        $fasta = $array_to_hash($fasta);

        $alldata = array();
        for ($i = 0; $i < count($gnn); $i++) {
            $g = $gnn[$i];
            $row = array("year" => $g["year"], "month" => $g["month"], "gnn" => $g["count"]);
            $key = $g["year"] . $g["month"];
            $row["direct"] = isset($direct[$key]) ? $direct[$key]["count"] : 0;
            $row["blast"] = isset($blast[$key]) ? $blast[$key]["count"] : 0;
            $row["id_lookup"] = isset($id_lookup[$key]) ? $id_lookup[$key]["count"] : 0;
            $row["fasta"] = isset($fasta[$key]) ? $fasta[$key]["count"] : 0;
            $row["total"] = $row["gnn"] + $row["direct"] + $row["blast"] + $row["id_lookup"] + $row["fasta"];
            array_push($alldata, $row);
        }

        return $alldata;
    }

    public static function get_unique_users($db) {
        $sql = "SELECT DISTINCT(gnn_email) as email, ";
        $sql .= "MAX(gnn_time_created) as last_job_time, ";
        $sql .= "COUNT(1) as num_jobs ";
        $sql .= "FROM gnn ";
        $sql .= "GROUP BY gnn_email ";
        $sql .= "ORDER BY gnn_email ASC";
        return $db->query($sql);
    }

    public static function num_unique_users($db) {
        $result = self::get_unique_users($db);
        return count($result);
    }

    public static function num_jobs($db) {
        $sql = "SELECT count(*) as count from gnn";
        $result = $db->query($sql);
        return $result[0]['count'];
    }

    public static function get_jobs($db, $month, $year, $job_type) {
        $id_field = "GNT ID";
        if ($job_type)
            $id_field = "ID";
        if ($job_type == "diagram") {
            $sql = "SELECT diagram.diagram_email as 'Email', ";
            $sql .= "diagram.diagram_id as '$id_field', ";
            $sql .= "diagram.diagram_key as 'Key', ";
            $sql .= "diagram.diagram_time_created as 'Time Created', ";
            $sql .= "diagram.diagram_time_started as 'Time Started', ";
            $sql .= "diagram.diagram_time_completed as 'Time Completed', ";
            $sql .= "diagram.diagram_params as params, ";
            $sql .= "diagram.diagram_results as results, ";
            $sql .= "diagram.diagram_type as type, ";
            $sql .= "diagram.diagram_title as 'Title', ";
            $sql .= "diagram.diagram_status as 'Status', ";
            $sql .= "diagram.diagram_pbs_number as 'PBS Number' ";
            $sql .= "FROM diagram ";
            $sql .= "WHERE MONTH(diagram.diagram_time_created)='" . $month . "' ";
            $sql .= "AND YEAR(diagram.diagram_time_created)='" . $year . "' ";
            $sql .= "ORDER BY diagram.diagram_id ASC";
        } else {
            $sql = "SELECT gnn.gnn_email as 'Email', ";
            $sql .= "gnn.gnn_id as '$id_field', ";
            $sql .= "gnn.gnn_key as 'Key', ";
            $sql .= "gnn.gnn_time_created as 'Time Created', ";
            $sql .= "gnn.gnn_time_started as 'Time Started', ";
            $sql .= "gnn.gnn_time_completed as 'Time Completed', ";
            $sql .= "gnn.gnn_status as 'Status', ";
            $sql .= "gnn.gnn_pbs_number as 'PBS Number', ";
            $sql .= "gnn.gnn_params as params, ";
            $sql .= "gnn.gnn_results as results ";
            $sql .= "FROM gnn ";
            $sql .= "WHERE MONTH(gnn.gnn_time_created)='" . $month . "' ";
            $sql .= "AND YEAR(gnn.gnn_time_created)='" . $year . "' ";
            $sql .= "ORDER BY gnn.gnn_id ASC";
        }
        return $db->query($sql);
    }

    public static function get_daily_jobs($db, $month, $year, $table = "gnn", $extra_where = "") {
        $sql = "SELECT count(1) as count, ";
        $sql .= "DATE({$table}_time_created) as day ";
        $sql .= "FROM $table ";
        $sql .= "WHERE MONTH({$table}_time_created)='" . $month . "' ";
        $sql .= "AND YEAR({$table}_time_created)='" . $year . "' ";
        if ($extra_where)
            $sql .= " AND $extra_where ";
        $sql .= "GROUP BY DATE({$table}_time_created) ";
        $sql .= "ORDER BY DATE({$table}_time_created) ASC";
        $result = $db->query($sql);
        return self::get_day_array($result, 'day', 'count', $month, $year);
    }

    public static function get_daily_jobs_aggregated($db, $month, $year) {
        $gnn = self::get_daily_jobs($db, $month, $year, "gnn");
        $extra = "(diagram_type = 'DIRECT' OR diagram_type = 'DIRECT_ZIP')";
        $direct = self::get_daily_jobs($db, $month, $year, "diagram", $extra);
        $extra = "diagram_type = 'BLAST'";
        $blast = self::get_daily_jobs($db, $month, $year, "diagram", $extra);
        $extra = "diagram_type = 'ID_LOOKUP'";
        $id_lookup = self::get_daily_jobs($db, $month, $year, "diagram", $extra);
        $extra = "diagram_type = 'FASTA'";
        $fasta = self::get_daily_jobs($db, $month, $year, "diagram", $extra);

        $array_to_hash = function ($array) {
            $hash = array();
            for ($i = 0; $i < count($array); $i++) {
                $data = $array[$i];
                $key = $data["day"];
                $hash[$key] = array("count" => $data["count"], "day" => $data["day"]);
            }
            return $hash;
        };

        $direct = $array_to_hash($direct);
        $blast = $array_to_hash($blast);
        $id_lookup = $array_to_hash($id_lookup);
        $fasta = $array_to_hash($fasta);

        $alldata = array();
        for ($i = 0; $i < count($gnn); $i++) {
            $g = $gnn[$i];
            $row = array("day" => $g["day"]);
            $key = $g["day"];
            $count_direct = isset($direct[$key]) ? $direct[$key]["count"] : 0;
            $count_blast = isset($blast[$key]) ? $blast[$key]["count"] : 0;
            $count_id_lookup = isset($id_lookup[$key]) ? $id_lookup[$key]["count"] : 0;
            $count_fasta = isset($fasta[$key]) ? $fasta[$key]["count"] : 0;
            $row["count"] = $g["count"] + $count_direct + $count_blast + $count_id_lookup + $count_fasta;
            array_push($alldata, $row);
        }

        return $alldata;
    }

    public static function get_day_array($data, $day_column, $data_column, $month, $year) {
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $new_data = array();
        for($i = 1; $i <= $days; $i++) {
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
                $day = $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                array_push($new_data, array($day_column => $day, $data_column => 0));
            }
            $exists = false;
        }
        return $new_data;
    }

}


