<?php

require_once('functions.class.inc.php');

class table_builder {

    private $is_html;
    private $buffer;

    public function __construct($format) {
        $this->is_html = $format != "tab";
    }

    public function add_row($col1, $col2, $col3 = false, $col4 = false) {
        if ($this->is_html) {
            $this->buffer .= "<tr><td>$col1</td><td>$col2</td>";
            if ($col3 !== false)
                $this->buffer .= "<td>$col3</td>";
            if ($col4 !== false)
                $this->buffer .= "<td>$col4</td>";
            $this->buffer .= "</tr>";
        }
        else {
            $this->buffer .= "$col1\t$col2";
            if ($col3 !== false)
                $this->buffer .= "\t$col3";
            if ($col4 !== false)
                $this->buffer .= "\t$col4";
        }
        $this->buffer .= "\n";
    }

    public function as_string() {
        return $this->buffer;
    }
}

?>
