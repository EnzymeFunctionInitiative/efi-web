<?php
namespace efi\taxonomy;

/*
$file = $argv[1];
$output_file = $argv[2];
$find_node = $argv[3];


$access = new access_json($file);

if (isset($find_node)) {
    $tree = $access->get_sub_tree($find_node);
    if (is_array($tree))
        $access->write_sub_tree($tree, $output_file);
} else {
    $access->write_tree($output_file);
}
 */


class access_json {

    public function __construct($file, $output_pretty = false) {
        $this->file = $file;
        $this->pretty = $output_pretty;
        $this->load($file);
    }

    private function load($file) {
        $json = file_get_contents($file);
        $this->data = json_decode($json, true);
    }

    public function get_sub_tree($node_id) {
        $tree = $this->get_sub_tree_find($this->data["data"], $node_id);
        return $tree;
    }

    private function get_sub_tree_find($tree, $node_id) {
        if (isset($tree["id"]) && $tree["id"] == $node_id) {
            return $tree;
        } else {
            if (isset($tree["children"])) {
                for ($i = 0; $i < count($tree["children"]); $i++) {
                    $retval = $this->get_sub_tree_find($tree["children"][$i], $node_id);
                    if (is_array($retval))
                        return $retval;
                }
            }
        }
        return false;
    }

    public function apply_function($node_fn) {
        $this->traverse_tree($this->data["data"], $node_fn);
    }
    private function traverse_tree(&$node, $node_fn) {
        $node_fn($node);
        if (isset($node["children"])) {
            for ($i = 0; $i < count($node["children"]); $i++) {
                $this->traverse_tree($node["children"][$i], $node_fn);
            }
        }
    }

    public function write_sub_tree($tree, $output_file) {
        $format_arg = $this->pretty ? JSON_PRETTY_PRINT : 0;
        $json = json_encode(array("data" => $tree), $format_arg);
        file_put_contents($output_file, $json);
    }

    public function write_tree($output_file) {
        $format_arg = $this->pretty ? JSON_PRETTY_PRINT : 0;
        $json = json_encode($this->data, $format_arg);
        file_put_contents($output_file, $json);
    }

    private static function get_time() {
        $milliseconds = round(microtime(true) * 1000);
        $seconds = $milliseconds / 1000;
        return $seconds;
    }
}


