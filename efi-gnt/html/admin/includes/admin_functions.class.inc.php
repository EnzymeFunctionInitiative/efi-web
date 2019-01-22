<?php


class admin_functions {

    public static function format_date($date_str) {
        if ($date_str == "NULL" || !$date_str)
            return "";
        $date = date_create($date_str);
        $formatted = date_format($date, "n/j h:i A");
        return $formatted;
    }
}

?>
