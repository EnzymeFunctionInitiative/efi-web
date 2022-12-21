<?php
require_once(__DIR__."/../../init.php");

use \efi\est\stepa;
use \efi\training\example_config;
use \efi\sanitize;

$has_html = true;

if (!defined("MAIN_UI") || !isset($GenerateJob)) {

    $is_example = example_config::is_example();

    $id = sanitize::validate_id("id", sanitize::GET);
    $key = sanitize::validate_key("key", sanitize::GET);
    if ($id === false || $key === false) {
        die("Invalid paramters.");
    }

    $job = new stepa($db, $id, $is_example);
    
    if ($job->get_key() != $key) {
        die("Invalid key.");
    }

    $has_html = false;
} else {
    $job = $GenerateJob;
}

$ev_data = $job->get_evalue_data();

if (!$has_html) {
?>

<!doctype html>
<head>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>

<body>
<?php } ?>

<div id="plot"></div>

<script>

var x = [
<?php
foreach ($ev_data as $ev => $edge_sum) {
    if ($edge_sum > 100)
        echo "            $ev,\n";
}
?>
    ],
    y = [
<?php
foreach ($ev_data as $ev => $edge_sum) {
    if ($edge_sum > 100)
        echo "$edge_sum,\n";
}
?>
    ];


var traces = [{
    x: x, 
    y: y,
    type: 'scatter',
}];

var layout = {
    title: 'Edge Count vs Alignment Score',
    xaxis: { title: 'Alignment Score', },
    yaxis: { title: 'Number of Edges', },
//    width: 700,
//    height: 500,
};

Plotly.newPlot('plot', traces, layout);

</script>


<?php if (!$has_html) { ?>
</body>
</html>
<?php } ?>

