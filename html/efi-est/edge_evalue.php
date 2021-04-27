<?php


$has_html = true;

if (!defined("MAIN_UI") || !isset($GenerateJob)) {
    require_once(__DIR__."/../../conf/settings_paths.inc.php");
    require_once(__EST_DIR__."/includes/main.inc.php");
    if (!isset($_GET["id"]) || !isset($_GET["key"])) {
        die("Invalid paramters.");
    }
    $id = $_GET["id"];
    $key = $_GET["key"];

    $job = new stepa($db, $id);
    
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

