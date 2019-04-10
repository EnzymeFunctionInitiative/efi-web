

class QuantifyResults {
    constructor(paramData, progLoaderId) {
        this.params = paramData;
        this.progLoaderObj = $(progLoaderId);
        this.formAction = "get_sbq_data.php";
        
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
        
        var logScale = true;
    
        if (logScale) {
            this.convertRawValueFn = function(rawVal) {
                var minLog = 4;
                var minLogVal = Math.pow(10, -minLog);
                var val;
                if (rawVal >= minLogVal)
                    val = Math.log10(rawVal);
                else
                    val = -minLog;
                var posVal = minLog + val;
                return posVal;
            };
        } else {
            this.convertRawValueFn = function(rawVal) {
                return rawVal;
            };
        }
    }

    convertRawValue(rawValue) {
        return this.convertRawValueFn(rawValue);
    }

    doFormPost(params, responseCallback) {
        if (this.params.Example) {
            params.append("example", 1);
        } else {
            params.append("id", this.params.Id);
            params.append("key", this.params.Key);
            if (this.params.QuantifyId)
                params.append("quantify-id", this.params.QuantifyId);
        }

        var that = this;
        this.showProgressLoader();
    
        params.append("res", this.params.ResType);
        var xhr = new XMLHttpRequest();
        xhr.open("POST", this.formAction, true);
        xhr.send(params);
        xhr.onreadystatechange  = function(){
            if (xhr.readyState == 4  ) {
                var jsonObj = JSON.parse(xhr.responseText);
                responseCallback(jsonObj);
                that.hideProgressLoader();
            }
        }
    }

    getColor(site_info, lastBodysiteIndex, mg) {
        var color = "";
        if (typeof site_info.color[mg] !== 'undefined')
            color = site_info.color[mg];
        if (!color)
            return this.mgColors[lastBodysiteIndex % this.mgColors.length];
        else
            return color;
    }

    getMetagenomeIndexBodySiteMap(metagenomes, siteData) {
        var map = {};
        for (var i = 0; i < metagenomes.length; i++) {
            var mg = metagenomes[i];
            if (mg in siteData.site) {
                map[i] = siteData.site[mg];
            } else {
                map[i] = "Unknown";
            }
        }
        return map;
    }

    // Map body site type to color and order
    getBodySiteInfo(metagenomes, siteData) {
        var order = {};
        var color = {};

        for (var i = 0; i < metagenomes.length; i++) {
            var mg = metagenomes[i];
            var site = siteData.site[mg];
            if (!(site in order)) {
                order[site] = siteData.order[mg];
                color[site] = siteData.color[mg];
            }
        }

        return {
            order: order,
            color: color
        };
    }
    
    showProgressLoader() {
        this.progLoaderObj.removeClass("hidden");
    }
    hideProgressLoader() {
        this.progLoaderObj.addClass("hidden");
    }
}


class BoxplotApp extends QuantifyResults {
    constructor(paramData, progressLoaderId) {
        super(paramData, progressLoaderId);
        this.containerId = "#master-plot-container";
    }

    getDataForCluster(clusterData, metagenomeMap, siteInfo) {
        var grouped = {};
        var sites = [];

        for (var i = 0; i < clusterData.abundance.length; i++) {
            var bodySite = metagenomeMap[i];
            if (!(bodySite in grouped)) {
                grouped[bodySite] = this.createBoxPlotTrace(bodySite, siteInfo.color[bodySite]);
                sites.push(bodySite);
            }
            var logValue = this.convertRawValue(clusterData.abundance[i]);
            grouped[bodySite].y.push(logValue);
        }

        sites.sort(function(a, b) {
            return siteInfo.order[a] - siteInfo.order[b];
        });

        var ordered = [];
        for (var i = 0; i < sites.length; i++) {
            var bodySite = sites[i];
            ordered.push(grouped[bodySite]);
        }

        return ordered;
    }

    createBoxPlotTrace(bodySite, color) {
        var fmtSite = bodySite.replace(" ", "<br>");
        return {
            y: [],
            type: "box",
            name: fmtSite,
            opacity: 1,
            marker: {
                size: 5,
                color: color,
            },
            fillcolor: color,
//            boxpoints: 'all',
            jitter: 0.5,
            whiskerwidth: 0.2,
            line: {
                width: 1,
                color: "#555",
            },
        };
    }

    clearPlotContainer() {
        $(this.containerId).empty();
    }

    processData(rawData) {
        var mgMap = super.getMetagenomeIndexBodySiteMap(rawData.metagenomes, rawData.site_info);
        var siteInfo = super.getBodySiteInfo(rawData.metagenomes, rawData.site_info);

        this.clearPlotContainer();
        var plotContainer = $(this.containerId);

        // Add a boxplot for each clusters
        for (var ci = 0; ci < rawData.clusters.length; ci++) {
            // Returns a dictionary that maps bodysite to abundance.
            var groupedData = this.getDataForCluster(rawData.clusters[ci], mgMap, siteInfo);
            var plotName = "Cluster " + rawData.clusters[ci].number;

            var layout = {
                width: 450,
                height: 450,
                title: plotName,
                showlegend: false,
                margin: {
                    l: 70,
                    t: 50,
                    r: 30,
                    b: 50,
                },
                xaxis: {
                    ticklen: 0,
                    tickfont: {
                        size: 10,
                    },
                },
                yaxis: {
                    title: {
                        text: "Copies per microbial genome",
                    },
                    ticksuffix: "  ",

                },
                shapes: [
                    {
                        type: 'line',
                        xref: 'paper',
                        yref: 'paper',
                        x0: 0,
                        y0: 0.05,
                        x1: 0,
                        y1: 1,
                        line: {
                            color: "#000",
                            width: 0.5,
                        },
                    },
                ],
                annotations: [
                    {
                        xref: 'paper',
                        yref: 'paper',
                        x: -0.17,
                        y: 0.5,
                        xanchor: 'left',
                        yanchor: 'center',
                        text: "Copies per microbial genome",
                        showarrow: false,
                        textangle: 270,
                    },
                ],
            };

            var divId = "boxplot-" + rawData.clusters[ci].number;
            plotContainer.append('<div id="' + divId + '"></div>');
            Plotly.newPlot(divId, groupedData, layout);
        }
    }

    search(clusterQuery) {
        var params = new FormData;
        params.append("clusters", clusterQuery);
    
        var that = this;
        super.doFormPost(params, function(jsonObj) {
            if (jsonObj.valid) {
                that.processData(jsonObj);
            }
        });
    }
}


class HeatmapApp extends QuantifyResults {
    constructor(paramData, progLoaderId, useBoxplots) {
        super(paramData, progLoaderId);

        if (typeof this.params.Width === 'undefined')
            this.Width = 950;
        else
            this.Width = this.params.Width;
        if (typeof this.params.Height === 'undefined')
            this.Height = 700;
        else
            this.Height = this.params.Height;
        
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
    
        this.bodySites = [];
    
        this.useBoxplots = useBoxplots;
    }


    getBodySites() {
        return this.bodySites;
    }

    linspace(a,b,n) {
        if(typeof n === "undefined") n = Math.max(Math.round(b-a)+1,1);
        if(n<2) { return n===1?[a]:[]; }
        var i,ret = Array(n);
        n--;
        for(i=n;i>=0;i--) { ret[i] = (i*b+(n-i)*a)/n; }
        return ret;
    }
    
    
    resetFilter() {
        this.doFormPost();
    }
    
    
    doFormPost(finishFn) {
        var useMean = $("#mean-cb").prop("checked");
        var hitsOnly = $("#hits-only-cb").prop("checked");
    
        if (typeof finishFn === "undefined")
            finishFn = function() {};
    
        var params = new FormData;
        
        if (useMean)
            params.append("use-mean", 1);
        if (hitsOnly)
            params.append("hits-only", 1);
    
        var clusterList = $("#cluster-filter").val();
        if (clusterList)
            params.append("clusters", clusterList);
    
        var lowerThresh = $("#min").val();
        if (lowerThresh && !isNaN(lowerThresh))
            params.append("lower_thresh", lowerThresh);
    
        var upperThresh = $("#max").val();
        if (upperThresh && !isNaN(upperThresh))
            params.append("upper_thresh", upperThresh);
    
        var bodysites = [];
        $(".filter-bs-item").each(function(i) {
            if ($(this).prop("checked"))
                bodysites.push($(this).val());
        });
        var bodysitesStr = bodysites.join("|");
        if (bodysitesStr)
            params.append("bodysites", bodysitesStr);
    
        var that = this;
        super.doFormPost(params, function(jsonObj) {
            if (jsonObj.valid) {
                that.processData(jsonObj);
            }
            finishFn();
        });
    }
    
    
    processData(data) {
    
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
            label = [],
            bodySiteMap = [];
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
            bodySiteMap[mg] = bodysite;
        }
    
        regions.push([lastBodysiteIndex, numMetagenomes-1, bodysiteMgLabels[numMetagenomes-1], lastColor]);
    
        this.bodySites = [];
        for (var i = 0; i < bodysiteMgLabels.length; i++) {
            if (!(bodysiteMgLabels[i] in this.bodySites)) {
                this.bodySites.push(bodysiteMgLabels[i]);
                this.bodySites[bodysiteMgLabels[i]] = 1;
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
    
        var title = "Abundances";
        if (!this.params.Example)
            title += " for Identify " + this.params.Id + ", Quantify " + this.params.QuantifyId;
    
        if (this.params.FileName || this.params.SearchType || this.params.RefDb || this.params.CdHitSid || this.params.DiamondSens)
            title += "<br>";
        var hasNewLine = false;
        
        var titleFileName = this.params.FileName;
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
        if (this.params.RefDb && this.params.RefDb != "UNIREF90") { //TODO: fix hardcoded constant
            if (hasNewLine) title += "; "; hasNewLine = true;
            title += this.params.RefDb;
        }
        if (this.params.SearchType && this.params.SearchType != "DIAMOND") { //TODO: fix hardcoded constant
            if (hasNewLine) title += "; "; hasNewLine = true;
            title += this.params.SearchType;
        }
        if (this.params.DiamondSens && this.params.DiamondSens != "normal") { //TODO: fix hardcoded constant
            if (hasNewLine) title += "; "; hasNewLine = true;
            title += this.params.DiamondSens;
        }
        if (this.params.CdHitSid && this.params.CdHitSid != "85") { //TODO: fix hardcoded constant
            if (hasNewLine) title += "; "; hasNewLine = true;
            title += "CD-HIT " + this.params.CdHitSid + "%";
        }
        if (hasNewLine) title += "; "; hasNewLine = true;
        if (useMean)
            title += "Mean Method";
        else
            title += "Median Method";
    
    
        var exportFileName = "abundance_I" + this.params.Id + "_Q" + this.params.QuantifyId;
        if (titleFileName)
            exportFileName += "_" + titleFileName.replace(/[^A-Za-z0-9_\-]+/g, "_");
    
        var layout = {
            title: title,
            width: this.Width,
            height: this.Height,
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
    
        if (this.useBoxplots) {
            var plotDiv = document.getElementById('plot');
            plotDiv.on("plotly_click", function (data) {
                if (!data || !data.points || data.points.length == 0)
                    return;
                var point = data.points[0];
                
                var mg = point.x;
                var mgIdx = point.pointIndex[1];
                var clusterIdx = point.y - 1;
                var clusterId = point.data.y[clusterIdx];
                var mgType = bodysiteMgLabels[mgIdx];
                
                console.log(point);
                
                if (!(clusterIdx in point.data.z)) {
                    console.log("Not found: " + clusterId + "/" + clusterIdx + " " + mg + " " + mgType);
                    return;
                }
        
                var clusterData = point.data.z[clusterIdx];
                console.log(clusterData);
                var groupedData = [];
                var groupMap = {};
                var groupCount = 0;
                for (var i = 0; i < clusterData.length; i++) {
                    var site = bodysiteMgLabels[i];
                    if (!(site in groupMap)) {
                        groupMap[site] = groupCount++;
                        groupedData.push({y: [], type: "box", name: site});
                    }
                    groupedData[groupMap[site]].y.push(clusterData[i]);
                }
        
                var layout = {
                    width: 550,
                    height: 550
                };
        
                console.log(groupedData);
        
                Plotly.newPlot("boxplot-plot", groupedData, layout);
                //$("#boxplot").data("data", groupedData);
                $("#boxplot").dialog({minWidth: 600, minHeight: 600});
            });
        }
    }
}


