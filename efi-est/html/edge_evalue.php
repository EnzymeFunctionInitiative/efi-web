<?php
require_once("../includes/main.inc.php");
//require_once("../libs/user_auth.class.inc.php");
//require_once("../includes/login_check.inc.php");
//
//$HeaderAdditional = array('<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>');
//
//require_once("inc/header.inc.php");

if (!isset($_GET["id"]) || !isset($_GET["key"])) {
    die("Invalid paramters.");
}

$id = $_GET["id"];
$key = $_GET["key"];

$job = new stepa($db, $id);

if ($job->get_key() != $key) {
    die("Invalid key.");
}


$ev_data = $job->get_evalue_data();

?>

<!doctype html>
<head>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>

<body>

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

</body>
</html>

