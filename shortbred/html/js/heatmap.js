

function HeatmapApp(paramData, progLoaderId) {

    this.progLoaderObj = $(progLoaderId);

    this.mgColors = [
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

    this.colorsBinary = [
        'rgb(255, 255, 255)',
        'rgb(0, 0, 0)',
    ];
    this.colors6 = [
        'rgb(68, 1, 84)',
        'rgb(65, 68, 135)',
        'rgb(42, 120, 142)',
        'rgb(34, 168, 132)',
        'rgb(122, 209, 81)',
        'rgb(253, 231, 37)',
    ];
    this.colors5 = [
        'rgb(68, 1, 84)',
        'rgb(59, 82, 139)',
        'rgb(33, 144, 140)',
        'rgb(93, 201, 99)',
        'rgb(253, 231, 37)',
    ];

    this.parms = paramData;
    
    this.bodySites = [];
}


HeatmapApp.prototype.getBodySites = function() {
    return this.bodySites;
}

HeatmapApp.prototype.getColor = function(site_info, lastBodysiteIndex, mg) {
    var color = "";
    if (typeof site_info.color[mg] !== 'undefined')
        color = site_info.color[mg];
    if (!color)
        return this.mgColors[lastBodysiteIndex % this.mgColors.length];
    else
        return color;
}


HeatmapApp.prototype.linspace = function (a,b,n) {
    if(typeof n === "undefined") n = Math.max(Math.round(b-a)+1,1);
    if(n<2) { return n===1?[a]:[]; }
    var i,ret = Array(n);
    n--;
    for(i=n;i>=0;i--) { ret[i] = (i*b+(n-i)*a)/n; }
    return ret;
}


HeatmapApp.prototype.resetFilter = function() {
    this.doFormPost();
}


HeatmapApp.prototype.doFormPost = function(finishFn) {
    var formAction = "get_sbq_data.php";

    var useMean = $("#mean-cb").prop("checked");
    var hitsOnly = $("#hits-only-cb").prop("checked");

    if (typeof finishFn === "undefined")
        finishFn = function() {};

    var parms = new FormData;
    parms.append("id", this.parms.Id);
    parms.append("key", this.parms.Key);
    parms.append("res", this.parms.ResType);
    if (this.parms.QuantifyId)
        parms.append("quantify-id", this.parms.QuantifyId);
    if (useMean)
        parms.append("use-mean", 1);
    if (hitsOnly)
        parms.append("hits-only", 1);

    var clusterList = $("#cluster-filter").val();
    if (clusterList)
        parms.append("clusters", clusterList);

    var lowerThresh = $("#min").val();
    if (lowerThresh && !isNaN(lowerThresh))
        parms.append("lower_thresh", lowerThresh);

    var upperThresh = $("#max").val();
    if (upperThresh && !isNaN(upperThresh))
        parms.append("upper_thresh", upperThresh);

    var bodysites = [];
    $(".filter-bs-item").each(function(i) {
        if ($(this).prop("checked"))
            bodysites.push($(this).val());
    });
    var bodysitesStr = bodysites.join("|");
    if (bodysitesStr)
        parms.append("bodysites", bodysitesStr);

    this.showProgressLoader();

    var that = this;
    var xhr = new XMLHttpRequest();
    xhr.open("POST", formAction, true);
    xhr.send(parms);
    xhr.onreadystatechange  = function(){
        if (xhr.readyState == 4  ) {
            var jsonObj = JSON.parse(xhr.responseText);
            if (jsonObj.valid) {
                that.processData(jsonObj);
            }
            that.hideProgressLoader();
            finishFn();
        }
    }
}


HeatmapApp.prototype.showProgressLoader = function() {
    this.progLoaderObj.removeClass("hidden-placeholder");
}
HeatmapApp.prototype.hideProgressLoader = function() {
    this.progLoaderObj.addClass("hidden-placeholder");
}


HeatmapApp.prototype.processData = function(data) {

    var hitsOnly = $("#hits-only-cb").prop("checked");
    var useMean = $("#mean-cb").prop("checked"); 
    var logScale = !hitsOnly;


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

    var useManualExtent = true;
    min = convertRawValue(0);
    max = convertRawValue(1);
    //if (min < 100000 && max > -100000) { // Is valid?
    //    useManualExtent = true;
    //    min = convertRawValue(min);
    //    max = convertRawValue(max);
    //}

    var x = [],
        y = this.linspace(1, numClusters),
        z = [],
        bodysiteMgLabels = [],
        gender = [],
        label = [];
    var regions = [];
    var lastBodysite = "";
    var lastBodysiteIndex = 0;
    var lastColor = "";
    for (var i = 0; i < numMetagenomes; i++) {
        var mg = data.metagenomes[i];
        var bodysite = data.site_info.site[mg];
        var regionColor = this.getColor(data.site_info, regions.length, mg);
        x.push(mg);
        bodysiteMgLabels.push(bodysite);
        gender.push(data.site_info.gender[mg]);

        if (i == 0)
            lastBodysite = bodysite;
        if (bodysite != lastBodysite) {
            regions.push([lastBodysiteIndex, i-1, lastBodysite, lastColor]);
            lastBodysite = bodysite;
            lastBodysiteIndex = i;
        }
        lastColor = regionColor;
    }

    regions.push([lastBodysiteIndex, numMetagenomes-1, bodysiteMgLabels[numMetagenomes-1], lastColor]);

    bodySites = {};
    for (var i = 0; i < bodysiteMgLabels.length; i++) {
        if (!(bodysiteMgLabels[i] in bodySites)) {
            this.bodySites.push(bodysiteMgLabels[i]);
            bodySites[bodysiteMgLabels[i]] = 1;
        }
    }

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

    var colorScale = "Jet";
    if (hitsOnly) {
        colorScale = [];
        colorScale.push([0, this.colorsBinary[0]]);
        colorScale.push([1, this.colorsBinary[1]]);
    }
    //var colors = this.colors6;
    //var colorScale = [];
    //for (var i in colors) {
    //    var colorVal = i / (colors.length-1);
    //    colorScale.push([colorVal, colors[i]]);
    //}

    var traces = [
        {
            x: x,
            y: y,
            z: z,
            type: "heatmap",
            colorscale: colorScale,
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
    } else if (hitsOnly) {
        traces[0].showscale = false;
//        traces[0].colorbar = {
//            title: "hits only",
//            titleside: "right",
//            tick0: 0,
//        };
    }

    var shapes = [];
    var labels = [];
    if (regions.length > 0) {
        for (var i in regions) {
            var region = regions[i];
            var x0 = region[0]-0.5;
            var x1 = region[1]+0.5;
            var shape = {
                type: 'rect',
                xref: 'x',
                yref: 'paper',
                x0: x0,
                y0: 0,
                x1: x1,
                y1: -0.02,
                fillcolor: region[3],
                //fillcolor: mgColors[i % mgColors.length],
                line: { width: 0, },
            };
            shapes.push(shape);
            var xMid = (x1 - x0) / 2 + x0;
            var label = region[2].replace(" ", "<br>");
            var anno = {
                x: xMid,
                y: -0.12,
                xref: 'x',
                yref: 'paper',
                text: label,
                showarrow: false,
                valign: "top",
                height: 50,
                font: {
                    color: 'black',
                },
            };
            labels.push(anno);
        }
    }

    var title = "Abundances for Identify " + this.parms.Id + ", Quantify " + this.parms.QuantifyId;

    if (this.parms.FileName || this.parms.SearchType || this.parms.RefDb || this.parms.CdHitSid || this.parms.DiamondSens)
        title += "<br>";
    var hasNewLine = false;
    
    var titleFileName = this.parms.FileName;
    if (titleFileName) {
        titleFileName = titleFileName.replace(/\.xgmml/i, "");
        titleFileName = titleFileName.replace(/\.zip/i, "");
        titleFileName = titleFileName.replace(/_coloredssn/i, "");
        titleFileName = titleFileName.replace(/_full_ssn/i, "");
        titleFileName = titleFileName.replace(/_repnode-(1?\.\d\d)_ssn/i, " $1% ");
        titleFileName = titleFileName.replace(/_/g, " ");
        titleFileName = titleFileName.replace(/^\d+ (\d+) /, "");
        title += titleFileName;
        hasNewLine = true;
    }
    if (this.parms.RefDb && this.parms.RefDb != "UNIREF90") { //TODO: fix hardcoded constant
        if (hasNewLine) title += "; "; hasNewLine = true;
        title += this.parms.RefDb;
    }
    if (this.parms.SearchType && this.parms.SearchType != "DIAMOND") { //TODO: fix hardcoded constant
        if (hasNewLine) title += "; "; hasNewLine = true;
        title += this.parms.SearchType;
    }
    if (this.parms.DiamondSens && this.parms.DiamondSens != "normal") { //TODO: fix hardcoded constant
        if (hasNewLine) title += "; "; hasNewLine = true;
        title += this.parms.DiamondSens;
    }
    if (this.parms.CdHitSid && this.parms.CdHitSid != "85") { //TODO: fix hardcoded constant
        if (hasNewLine) title += "; "; hasNewLine = true;
        title += "CD-HIT " + this.parms.CdHitSid + "%";
    }
    if (hasNewLine) title += "; "; hasNewLine = true;
    if (useMean)
        title += "Mean Method";
    else
        title += "Median Method";


    var exportFileName = "abundance_I" + this.parms.Id + "_Q" + this.parms.QuantifyId;
    if (titleFileName)
        exportFileName += "_" + titleFileName.replace(/[^A-Za-z0-9_\-]+/g, "_");

    var layout = {
        title: title,
        width: 950,
        height: 700,
        xaxis: {
            tickangle: -45,
            showticklabels: false,
        },
        yaxis: {
            title: "Cluster Number",
                autorange: "reversed",
                type: "category",
        },
        shapes: shapes,
        annotations: labels,
    };

    if (hitsOnly)
        layout.margin = {r: 135};

    Plotly.newPlot('plot', traces, layout, {toImageButtonOptions: {filename: exportFileName, width: 1425, height: 1050}});

};



