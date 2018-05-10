<?php
//require_once("../includes/main.inc.php");
//require_once("../libs/user_auth.class.inc.php");
//require_once("../includes/login_check.inc.php");
//
//$HeaderAdditional = array('<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>');
//
//require_once("inc/header.inc.php");

?>

<!doctype html>
<head>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>

<body>

<div id="plot"></div>

<script>

/*
var data = [
  {
    z: [[1, 20, 30, 50, 1], [20, 1, 60, 80, 30], [30, 60, 1, -10, 20]],
    numNode: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
    numEdge: ['Morning', 'Afternoon', 'Evening'],
    type: 'heatmap'
  }
];

Plotly.newPlot('plot', data);
*/


function makeplot() {
    Plotly.d3.csv("filesize.csv", function(data){ processData(data) } );
};
    
function processData(allRows) {

    //var numNode = numeric.linspace(0, 200000, 10000);
    //var numEdge = numeric.linspace(0, 10000000, 100000);
    //var size = [];
    var numNode = [], numEdge = [], size = [];
    var rawNumNode = [], rawNumEdge = [];

    for (var i=0; i<allRows.length; i++) {
        row = allRows[i];
        var nodes = (Math.trunc(row['NumNodes'] / 1000) + 0.0) * 1000;
        var edges = Math.trunc(row['NumEdges'] / 100000) * 100000;
        if (edges > 10000000)
            continue;
        numNode.push(nodes);
        numEdge.push(edges);
        size.push( row['FileSize'] / (1024*1024) );
        rawNumNode.push(row['NumNodes']);
        rawNumEdge.push(row['NumEdges']);
    }
    console.log(numNode);
    makePlotly(numNode, numEdge, size, rawNumNode, rawNumEdge);
}

function makePlotly(numNode, numEdge, size, rawNumNode, rawNumEdge){
    var traces = [{
        x: numNode, 
        y: numEdge,
        z: size,
        type: 'heatmap',
        colorscale: 'Jet',
        dy: 10000,
        dx: 1000,
        x0: 0,
        y0: 0,
        xtype: 'scaled',
        ytype: 'scaled',
        zsmooth: 'best',
        connectgaps: true,
//        showscale: false,
    }, {
        x: rawNumNode,
        y: rawNumEdge,
        type: 'scatter',
        mode: 'markers',
        marker: {
            color: 'black',
            size: 3,
        },
    }];

    Plotly.newPlot('plot', traces, 
        {title: 'Node/Edge vs File Size'});
};
makeplot();

</script>

</body>
</html>

