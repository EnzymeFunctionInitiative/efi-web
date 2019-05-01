<?php

require_once('functions.class.inc.php');

class table_builder {

    private $is_html;
    private $buffer;

    public function __construct($format) {
        $this->is_html = $format != "tab";
    }

    public function is_html() {
        return $this->is_html;
    }

    public function add_row_with_class($col1, $col2, $css_row_class) {
        $this->add_row($col1, $col2, false, false, $css_row_class);
    }

    public function add_row_with_html($col1, $col2, $css_row_class = "") { // Strips the HTML tags when in text mode
        if (!$this->is_html) {
            $col1 = preg_replace('/<[^>]+>/', "", $col1);
            $col2 = preg_replace('/<[^>]+>/', "", $col2);
        }
        $this->add_row($col1, $col2, false, false, $css_row_class);
    }

    // Add arbitrary HTML
    public function add_html($html) {
        if ($this->is_html) {
            $this->buffer .= $html;
        }
    }

    public function add_row($col1, $col2, $col3 = false, $col4 = false, $css_row_class = "") {
        if ($this->is_html) {
            if ($css_row_class)
                $css_row_class = "class=\"$css_row_class\"";
            $this->buffer .= "<tr $css_row_class><td>$col1</td><td>$col2</td>";
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

    public function add_row_html_only($col1, $col2) {
        if ($this->is_html) {
            $this->buffer .= "<tr><td>$col1</td><td>$col2</td>";
            $this->buffer .= "</tr>";
        }
        $this->buffer .= "\n";
    }

    public function as_string() {
        return $this->buffer;
    }
}

?>
