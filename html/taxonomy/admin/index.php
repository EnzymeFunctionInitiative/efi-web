<?php
require_once(__DIR__."/../../../init.php");

use \efi\taxonomy\statistics;

require_once(__DIR__."/../../shared/est_taxonomy/admin/admin_header.inc.php");
require_once(__DIR__."/../../shared/est_taxonomy/admin/shared.inc.php");

$graph_type = "taxonomy";

$rows = statistics::num_taxonomy_per_month($db);
$table_html = get_stats_table_html($rows, 2);

$headers = array();
show_stats_table($table_html, $headers);

show_report_code();
show_stats_nav();
show_graphs($graph_type);

show_nav_js($graph_type);


require_once(__DIR__."/../../shared/est_taxonomy/admin/admin_footer.inc.php");


