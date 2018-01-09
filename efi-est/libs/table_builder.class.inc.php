<?php

require_once('functions.class.inc.php');

class table_builder {

    private $is_html;
    private $buffer;

    public function __construct($format) {
        $this->is_html = $format != "tab";
    }

    public function add_row($col1, $col2) {
        if ($this->is_html) {
            $this->buffer .= "<tr><td>$col1</td><td>$col2</td></tr>\n";
        }
        else {
            $this->buffer .= "$col1\t$col2\n";
        }
    }

    public function as_string() {
        return $this->buffer;
    }
}

?>
