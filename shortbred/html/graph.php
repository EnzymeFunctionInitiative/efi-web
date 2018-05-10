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

<script>

var parms = new FormData;
parms.append("id", <?php echo $_GET["id"]; ?>);
parms.append("key", "<?php echo $_GET["key"]; ?>");

linspace = function (a,b,n) {
    if(typeof n === "undefined") n = Math.max(Math.round(b-a)+1,1);
    if(n<2) { return n===1?[a]:[]; }
    var i,ret = Array(n);
    n--;
    for(i=n;i>=0;i--) { ret[i] = (i*b+(n-i)*a)/n; }
    return ret;
}

var processData = function(data) {

    var numMetagenomes = data.metagenomes.length;
    var numClusters = data.clusters.length;
//    var numMetagenomes = 80;
//    var numClusters = 400;

    var x = linspace(1, numClusters),
        y = [],
        z = [];
    for (var i = 0; i < numMetagenomes; i++) {
        y.push(data.metagenomes[i]);
    }

    for (var i = 0; i < numMetagenomes; i++) {
        z.push([]);
        for (var j = 0; j < numClusters; j++) {
            z[i][j] = data.clusters[j].abundance[i];
        }
    }

//    console.log(x);
//    console.log(y);
    console.log(z);

    var traces = [{
        x: x,
        y: y,
        z: z,
        type: 'heatmap',
        colorscale: 'Jet',
//        zsmooth: 'best',
//        connectgaps: true,
//        showscale: false,
    }];

    var layout = {
        title: 'Cluster/Metagenome Abundances',
        width: 950,
        height: 700,
    };

    Plotly.newPlot('plot', traces, layout);



    
//    var xz = [];
//    var yz = [];
//    var y1Max = 0;
//    
//    for (var i = 0; i < data.clusters.length; i++) {
//        xz.push(data.clusters[i].number);
//        var row = [];
//        var sum = 0;
//        for (var j = 0; j < numMetagenomes; j++) {
//            var a = data.clusters[i].abundance[j];
//            row.push(a);
//            y1Max = Math.max(y1Max, a);
//            sum += a;
//        }
//        if (sum > 0) {
//            yz.push(row);
//        }
//    }
//
//    console.log(y1Max);
//    
//    var stackObj = d3.stack().keys(data.metagenomes)(yz);
//    
//    
//    //// The xz array has numClusters elements, representing the x-values shared by all series.
//    //// The yz array has numMetagenomes elements, representing the y-values of each of the numMetagenomes series.
//    //// Each yz[i] is an array of numClusters non-negative numbers representing a y-value for xz[i].
//    //// The y01z array has the same structure as yz, but with stacked [y₀, y₁] instead of y.
//    //var xz = d3.range(numClusters),
//    //    yz = d3.range(numMetagenomes).map(function() { return bumps(numClusters); }),
//    //    y01z = d3.stack().keys(d3.range(numMetagenomes))(d3.transpose(yz)),
//    //    yMax = d3.max(yz, function(y) { return d3.max(y); }),
//    //    y1Max = d3.max(y01z, function(y) { return d3.max(y, function(d) { return d[1]; }); });
//    
//    var svg = d3.select("svg"),
//        margin = {top: 40, right: 10, bottom: 20, left: 10},
//        width = +svg.attr("width") - margin.left - margin.right,
//        height = +svg.attr("height") - margin.top - margin.bottom,
//        g = svg.append("g").attr("transform", "translate(" + margin.left + "," + margin.top + ")");
//    
//    var x = d3.scaleBand()
////        .domain(xz)
//        .domain([1, 50])
//        .rangeRound([0, width])
//        .padding(0.08);
//    
//    var y = d3.scaleLinear()
//        .domain([0, y1Max])
//        .range([height, 0]);
//    
//    var color = d3.scaleOrdinal()
//        .domain(d3.range(numMetagenomes))
//        .range(d3.schemeCategory20c);
//    
//    var series = g.selectAll(".series")
//      .data(stackObj)
//      .enter().append("g")
//        .attr("fill", function(d, i) { return color(i); });
//    
//    var rect = series.selectAll("rect")
//      .data(function(d) { return d; })
//      .enter().append("rect")
//        .attr("x", function(d, i) { return x(i); })
//        .attr("y", height)
//        .attr("width", x.bandwidth())
//        .attr("height", 0);
//    
////    rect.transition()
////        .delay(function(d, i) { return i * 10; })
////        .attr("y", function(d) { return y(d[1]); })
////        .attr("height", function(d) { return y(d[0]) - y(d[1]); });
////    
//    g.append("g")
//        .attr("class", "axis axis--x")
//        .attr("transform", "translate(0," + height + ")")
//        .call(d3.axisBottom(x)
//            .tickSize(0)
//            .tickPadding(6));
////    
////    d3.selectAll("input")
////        .on("change", changed);
//    
//    //var timeout = d3.timeout(function() {
//    //  d3.select("input[value=\"grouped\"]")
//    //      .property("checked", true)
//    //      .dispatch("change");
//    //}, 2000);
//
};








//function changed() {
////  timeout.stop();
//  if (this.value === "grouped") transitionGrouped();
//  else transitionStacked();
//}
//
//function transitionGrouped() {
//  y.domain([0, yMax]);
//
//  rect.transition()
//      .duration(500)
//      .delay(function(d, i) { return i * 10; })
//      .attr("x", function(d, i) { return x(i) + x.bandwidth() / numMetagenomes * this.parentNode.__data__.key; })
//      .attr("width", x.bandwidth() / numMetagenomes)
//    .transition()
//      .attr("y", function(d) { return y(d[1] - d[0]); })
//      .attr("height", function(d) { return y(0) - y(d[1] - d[0]); });
//}
//
//function transitionStacked() {
//  y.domain([0, y1Max]);
//
//  rect.transition()
//      .duration(500)
//      .delay(function(d, i) { return i * 10; })
//      .attr("y", function(d) { return y(d[1]); })
//      .attr("height", function(d) { return y(d[0]) - y(d[1]); })
//    .transition()
//      .attr("x", function(d, i) { return x(i); })
//      .attr("width", x.bandwidth());
//}


















doFormPost("get_sbq_data.php", parms, processData);

function doFormPost(formAction, formData, completionHandler) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", formAction, true);
    xhr.send(formData);
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


