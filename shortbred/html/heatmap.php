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

var mgColors = [
    "RoyalBlue",
    "BurlyWood",
    "Gray",
    "Turquoise",
    "SeaGreen",
    "Orange",
    "Salmon",
    "Purple",
    "LightSteelBlue",
];

var Id = <?php echo $_GET["id"]; ?>;
var Key = "<?php echo $_GET["key"]; ?>";
var ResType = "<?php echo $_GET["res"]; ?>";

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

    var convertRawValue = function(rawVal) {
        if (!logScale) {
            return rawVal;
        } else {
            var minLog = 4;
            var minLogVal = Math.pow(10, -minLog);
            var val;
            if (rawVal >= minLogVal)
                val = Math.log10(rawVal);
            else
                val = -minLog;
            var posVal = minLog + val;
            return posVal;
        }
    }

    var numMetagenomes = data.metagenomes.length;
    var numClusters = data.clusters.length;

    var min = data.extent.min;
    var max = data.extent.max;

    var useManualExtent = false;
    if (min < 100000 && max > -100000) { // Is valid?
        useManualExtent = true;
        min = convertRawValue(min);
        max = convertRawValue(max);
    }

    var x = [],
        y = linspace(1, numClusters),
        z = [],
        bodysiteMgLabels = [],
        gender = [],
        label = [];
    var regions = [];
    var lastBodysite = "";
    var lastBodysiteIndex = 0;
    for (var i = 0; i < numMetagenomes; i++) {
        var mg = data.metagenomes[i];
        var bodysite = data.site_info.site[mg];
        x.push(mg);
        bodysiteMgLabels.push(bodysite);
        gender.push(data.site_info.gender[mg]);

        if (i == 0)
            lastBodysite = bodysite;
        if (bodysite != lastBodysite) {
            regions.push([lastBodysiteIndex, i, lastBodysite]);
            lastBodysite = bodysite;
            lastBodysiteIndex = i + 1;
        }
    }

    regions.push([lastBodysiteIndex, numMetagenomes-1, bodysiteMgLabels[numMetagenomes-1]]);

    for (var i = 0; i < numClusters; i++) {
        z.push([]);
        label.push([]);
        y[i] = data.clusters[i].number;
        for (var j = 0; j < numMetagenomes; j++) {
            var rawVal = data.clusters[i].abundance[j];
            // <0.000001 = -6, 0.00001 = -5, 0.0001 = -4, 0.001 = -3, 0.01 = -2, 0.1 = -1, 1 = 0
            var posVal = convertRawValue(rawVal);
            z[i].push(posVal);
            label[i].push(x[j] + ", " + y[i] + " = " + rawVal + "<br>" + bodysiteMgLabels[j] + ", " + gender[j]);
        }
    }

    var colors = colors6;

    var colorScale = [];
    for (var i in colors) {
        var colorVal = i / (colors.length-1);
        colorScale.push([colorVal, colors[i]]);
    }

    var traces = [
        {
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
        },
    ];

    if (useManualExtent) {
        traces[0].zmin = min;
        traces[0].zmax = max;
        traces[0].zauto = false;
    }



    if (logScale) {
        var tickVals = [0, 1, 2, 3, 3.90];
        traces[0].colorbar = {
            title: "gene copies per microbial genome",
            titleside: "right",
            tick0: 0,
            tickmode: "array",
            tickvals: tickVals,
            ticktext: ["<1:10K", "1:1K", "1:100", "1:10", "1:1"],
        };
    }

    var shapes = [];
    if (regions.length > 0) {
        for (var i in regions) {
            var region = regions[i];
            var shape = {
                type: 'rect',
                xref: 'x',
                yref: 'paper',
                x0: region[0]-0.5,
                y0: 0,
                x1: region[1]+0.5,
                y1: -0.02,
                fillcolor: mgColors[i % mgColors.length],
                line: { width: 0, },
            };
            shapes.push(shape);
        }
    }

    var layout = {
        title: 'Cluster/Metagenome Abundances',
        width: 950,
        height: 700,
        xaxis: {
            tickangle: -45,
//            title: "Metagenome",
            showticklabels: false,
        },
        yaxis: {
            title: "Cluster Number",
                autorange: "reversed",
                type: "category",
        },
        shapes: shapes,
    };

    Plotly.newPlot('plot', traces, layout);

};



doFormPost();
document.body.addEventListener('keyup', function(e) {
    if (e.keyCode == 13) {
        doFormPost();
    }
});


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
    parms.append("res", ResType);

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


