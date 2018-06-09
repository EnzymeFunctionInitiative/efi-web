<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>

form {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  position: absolute;
  left: 10px;
  top: 10px;
}

label {
  display: block;
}

</style>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>
<body>
<!--
<form>
  <label><input type="radio" name="mode" value="grouped"> Grouped</label>
  <label><input type="radio" name="mode" value="stacked" checked> Stacked</label>
</form>
-->
<!--<svg width="960" height="500"></svg>-->
<!--<script src="https://d3js.org/d3.v4.min.js"></script>-->

<div id="plot"></div>

<div style="margin-top: 20px;">
Show specific cluster numbers: <input type="text" id="cluster-filter" />
<button type="button" id="filter-btn" onclick='doFormPost()'>Apply Filter</button>
<button type="button" id="filter-btn" onclick='resetFilter()'>Reset Filter</button>
</div>



<script>

var colors6 = [
    'rgb(68, 1, 84)',
    'rgb(65, 68, 135)',
    'rgb(42, 120, 142)',
    'rgb(34, 168, 132)',
    'rgb(122, 209, 81)',
    'rgb(253, 231, 37)',
];
var colors5 = [
    'rgb(68, 1, 84)',
    'rgb(59, 82, 139)',
    'rgb(33, 144, 140)',
    'rgb(93, 201, 99)',
    'rgb(253, 231, 37)',
];

var Id = <?php echo $_GET["id"]; ?>;
var Key = "<?php echo $_GET["key"]; ?>";

linspace = function (a,b,n) {
    if(typeof n === "undefined") n = Math.max(Math.round(b-a)+1,1);
    if(n<2) { return n===1?[a]:[]; }
    var i,ret = Array(n);
    n--;
    for(i=n;i>=0;i--) { ret[i] = (i*b+(n-i)*a)/n; }
    return ret;
}

var processData = function(data) {

    var logScale = true;
    var minLog = 4;
    var minLogVal = Math.pow(10, -minLog);

    var numMetagenomes = data.metagenomes.length;
    var numClusters = data.clusters.length;

    var x = [],
        y = linspace(1, numClusters),
        z = [],
        bodysite = [],
        gender = [],
        label = [];
    for (var i = 0; i < numMetagenomes; i++) {
        x.push(data.metagenomes[i]);
        bodysite.push(data.site_info.site[data.metagenomes[i]]);
        gender.push(data.site_info.gender[data.metagenomes[i]]);
    }

    for (var i = 0; i < numClusters; i++) {
        z.push([]);
        label.push([]);
        y[i] = data.clusters[i].number;
        for (var j = 0; j < numMetagenomes; j++) {
            var rawVal = data.clusters[i].abundance[j];
            // <0.000001 = -6, 0.00001 = -5, 0.0001 = -4, 0.001 = -3, 0.01 = -2, 0.1 = -1, 1 = 0
            if (logScale) {
                var val;
                if (rawVal >= minLogVal)
                    val = Math.log10(rawVal);
                else
                    val = -minLog;
//                if (val < -minLog)
//                    console.log("" + j + " " + i + " " + rawVal + " " + val);
                var posVal = minLog + val;
                z[i].push(posVal);
            } else {
                z[i][j] = rawVal;
            }
            label[i].push(x[j] + ", " + y[i] + " = " + rawVal + "<br>" + bodysite[j] + ", " + gender[j]);
        }
    }

    var colors = colors6;

    var colorScale = [];
    for (var i in colors) {
        var colorVal = i / (colors.length-1);
        colorScale.push([colorVal, colors[i]]);
    }

    var traces = [{
        x: x,
        y: y,
        z: z,
        type: 'heatmap',
        colorscale: 'Jet',
        //colorscale: colorScale,
        text: label,
        hoverinfo: "text",
        legend: {
            orientation: "h",
        },
    }];

    if (logScale) {
        var tickVals = [0, 1, 2, 3, 3.90];
        traces[0].colorbar = {
            tick0: 0,
            tickmode: "array",
            tickvals: tickVals,
            ticktext: ["<1:10K", "1:1K", "1:100", "1:10", "1:1"],
        };
    }

    var layout = {
        title: 'Cluster/Metagenome Abundances',
        width: 950,
        height: 700,
        xaxis: {
            tickangle: -45,
            title: "Metagenome",
        },
        yaxis: {
            title: "Cluster Number",
                autorange: "reversed",
                type: "category",
        },
    };

    Plotly.newPlot('plot', traces, layout);

};



doFormPost();


function resetFilter() {

    var clusterField = document.getElementById("cluster-filter");
    clusterField.value = "";

    doFormPost();
}

function doFormPost() {

    var formAction = "get_sbq_data.php";
    var completionHandler = processData;

    var parms = new FormData;
    parms.append("id", Id);
    parms.append("key", Key);

    var clusterList = document.getElementById("cluster-filter").value;
    if (clusterList) {
        console.log("Clusters: " + clusterList);
        parms.append("clusters", clusterList);
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", formAction, true);
    xhr.send(parms);
    xhr.onreadystatechange  = function(){
        if (xhr.readyState == 4  ) {
            var jsonObj = JSON.parse(xhr.responseText);
            if (jsonObj.valid) {
                completionHandler(jsonObj);
            }
        }
    }
}
</script>

</body>
</html>


