
////////////////////////////////////////////////////////////////////////////////////////////////////
// SUNBURST
//

function AppSunburst(jobId, jobKey, unirefVersion) {
    this.jobId = jobId;
    this.jobKey = jobKey;
    this.unirefVersion = unirefVersion;
}


function addParentRef(treeData, theParent) {
    treeData.theParent = theParent;
    if (typeof treeData.children !== "undefined") {
        for (var i = 0; i < treeData.children.length; i++) {
            addParentRef(treeData.children[i], treeData);
        }
    }
}


AppSunburst.prototype.addSunburstFeature = function(treeData) {

    var idTypeStr = this.unirefVersion ? "UniRef"+this.unirefVersion : "UniProt";
    var that = this;
    var Colors = getSunburstColorFn(); // from sunburst_helpers.js

    var newTreeData = addParentRef(treeData, null);

    //var setupSvgDownload = function() {
    //    var svg = $("#sunburstChart svg")[0];
    //    $("#sunburstSvg").click(function() {
    //        var svgData = svg.outerHTML;
    //        var svgBlob = new Blob([svgData], {type:"image/svg+xml;charset=utf-8"});
    //        var svgUrl = URL.createObjectURL(svgBlob);
    //        var downloadLink = document.createElement("a");
    //        downloadLink.href = svgUrl;
    //        downloadLink.download = "newesttree.svg";
    //        document.body.appendChild(downloadLink);
    //        downloadLink.click();
    //        document.body.removeChild(downloadLink);
    //    });
    //};

    var addCurViewNumSeq = function() {
        if (!that.sbCurrentData)
            return;
        var numUniProt = commify(that.sbCurrentData.numSequences);
        var idStr = that.sbCurrentData.numSequences > 1 ? "IDs" : "ID";
        $("#sunburstIdNums").text(numUniProt + " " + idTypeStr + " " + idStr + " visible");
    };

    var maxDepth = 9;
    var depthMap = {
        0 : [ "Root",   "Superkingdom", "Kingdom",  "Phylum",   "Class",    "Order",    "Family",   "Genus",    "Species" ],
        1 : [           "Superkingdom", "Kingdom",  "Phylum",   "Class",    "Order",    "Family",   "Genus",    "Species" ], 
        2 : [                           "Kingdom",  "Phylum",   "Class",    "Order",    "Family",   "Genus",    "Species" ], 
        3 : [                                       "Phylum",   "Class",    "Order",    "Family",   "Genus",    "Species" ], 
        4 : [                                                   "Class",    "Order",    "Family",   "Genus",    "Species" ], 
        5 : [                                                               "Order",    "Family",   "Genus",    "Species" ], 
        6 : [                                                                           "Family",   "Genus",    "Species" ], 
        7 : [                                                                                       "Genus",    "Species" ], 
        8 : [                                                                                                   "Species" ], 
    };
    var levelsColorFn = getColorForSunburstLevelFn();

    var showTaxLevels = function(data) {
        $("#sunburstChartLevels").empty();
        for (var i = data.depth; i < maxDepth; i++) {
            var theColor = levelsColorFn(data, i);
            var theLevel = depthMap[i][0];
            $("#sunburstChartLevels").append('<div style="background-color: ' + theColor + '" class="sunburst-level">' + theLevel + '</div>');
            //console.log(levelsColorFn(data, i));
        }
    };

                    that.sbRootData = treeData;
                    that.sbCurrentData = treeData;
                    addCurViewNumSeq();
                    showTaxLevels(treeData);
                    var sb = Sunburst()
                        .width(600)
                        .height(600)
                        .data(treeData)
                        .label("node")
                        .size("numSpecies")
                        .color(Colors)
                        .excludeRoot(true)
                        //.color((d, parent) => color(parent ? parent.data.name : null))
                        //.tooltipContent((d, node) => `Size: <i>${node.value}</i>`)
                        (document.getElementById("sunburstChart"));
                    sb.onClick(function(data) {
                        if (!data)
                            return;
                        that.sbCurrentData = data;
                        addCurViewNumSeq();
                        sb.focusOnNode(data);
                        showTaxLevels(data);
                    });

//    $("#dataAvailableSunburst").click(function() {
//        var progress = new Progress($("#sunburstProgressLoader"));
//        progress.start();
//        $("#sunburstModal").modal();
//        $("#sunburstChart").empty();
//        var parms = {cid: that.network.Id, a: "tax", v: that.version};
//        $.ajax({
//            dataType: "json",
//            url: "getdata.php",
//            data: parms,
//            success: function(treeData) {
//                if (typeof(treeData.valid) !== "undefined" && treeData.valid === "false") {
//                    //TODO: handle error
//                    alert(treeData.message);
//                } else {
//                    that.sbRootData = treeData;
//                    that.sbCurrentData = treeData;
//                    addCurViewNumSeq();
//                    var sb = Sunburst()
//                        .width(600)
//                        .height(600)
//                        .data(treeData)
//                        .label("node")
//                        .size("numSpecies")
//                        .color(Colors)
//                        .excludeRoot(true)
//                        //.color((d, parent) => color(parent ? parent.data.name : null))
//                        //.tooltipContent((d, node) => `Size: <i>${node.value}</i>`)
//                        (document.getElementById("sunburstChart"));
//                    sb.onClick(function(data) {
//                        that.sbCurrentData = data;
//                        addCurViewNumSeq();
//                        sb.focusOnNode(data);
//                    });
//                    //setupSvgDownload();
//                    progress.stop();
//                }
//            }
//        });
//    }).enableDataAvailableButton();

    this.sbDownloadFile = null;
    var makeTextFile = function(text) {
        var data = new Blob([text], {type: 'text/plain'});
    
        // If we are replacing a previously generated file we need to
        // manually revoke the object URL to avoid memory leaks.
        if (that.sbDownloadFile !== null) {
          window.URL.revokeObjectURL(that.sbDownloadFile);
        }
    
        that.sbDownloadFile = window.URL.createObjectURL(data);
    
        return that.sbDownloadFile;
    };

    var fixNodeName = function(str) {
        return str.replace(/[^a-z0-9]/gi, "_");
    };

    var getIdType = function() {
        return $("input[name='sunburstIdType']:checked").val();
    };


    if (this.unirefVersion === 90) {
        $("#sunburstIdTypeUniRef90Container").show();
    }
    if (this.unirefVersion === 50) {
        $("#sunburstIdTypeUniRef50Container").show();
        $("#sunburstIdTypeUniRef90Container").show();
    }
    //if (this.unirefVersion !== false) {
    //    $("#sunburstTypeDownloadContainer").show();
    //}

    $("#sunburstDlIds").click(function() {
        var idType = getIdType();
        var ids = getIdsFromTree(that.sbCurrentData, idType);
        var fname = that.jobId + "_";
        if (idType != "uniref")
            fname += idType + "_";
        fname += fixNodeName(that.sbCurrentData.node) + ".txt";
        var text = ids.join("\r\n");
        //$("#sbDownloadBtn").show();
        //$("#sunburstDownloadModal").show();
        $("#sbDownloadLink").attr("download", fname);
        $("#sbDownloadLink").attr("href", makeTextFile(text));
        $("#sbDownloadLink")[0].click();
        //$("#sbDownloadBtn").find("a").trigger("click"); //#sbDownloadLink
        //$("#sbDownloadBtn").trigger("click"); //#sbDownloadLink
        //$("#sbDownloadLink").click(function() {
        //    //$("#sunburstDownloadModal").modal();
        //    $("#sunburstDownloadModal").hide();
        //});
        //$("#sunburstDownloadModal").modal();
    });
    $("#sunburstDlFasta").click(function() {
        var idType = getIdType();
        var progress = new Progress($("#downloadProgressLoader"));
        progress.start();
        var ids = getIdsFromTree(that.sbCurrentData, idType);
        var form = $('<form method="POST" action="get_sunburst_fasta.php"></form>');
        var fc = $('<input name="id" type="hidden">').val(that.jobId);
        form.append(fc);
        var fv = $('<input name="key" type="hidden">').val(that.jobKey);
        form.append(fv);
        var fo = $('<input name="o" type="hidden">').val(fixNodeName(that.sbCurrentData.node));
        form.append(fo);
        var fids = $('<input name="ids" type="hidden">').val(JSON.stringify(ids));
        form.append(fids);
        var fidtype = $('<input name="it" type="hidden">').val(idType);
        form.append(fidtype);
        $("body").append(form);
        $("#sbDownloadBtn").hide();
        //$("#sunburstDownloadModal").show();
        //$("#sunburstDownloadModal h5").show();
        //$("#sunburstDownloadModal").dialog({
        //        modal: true,
        //        buttons: {
        //            Close: function() {
        //                $(this).dialog("close");
        //            }
        //        }});
        form.submit();
        setTimeout(function() {
            //$("#sunburstDownloadModal").modal("hide");
            progress.stop();
        }, 1000);


        //$.ajax({
        //    type: "POST",
        //    dataType: "json",
        //    url: "getfasta.php",
        //    data: parms,
        //    success: function(data) {
        //        $("#sunburstDownloadModal").modal("hide");
        //    }
        //});
        //$("#sbDownloadLink").attr("href", );
    });
}


function getIdsFromTree(data, idType) {
    var nextLevel = function(level) {
        var ids = [];
        // Bottom level
        if (typeof level.sequences !== "undefined") {
            for (var i = 0; i < level.sequences.length; i++) {
                var id = idType == "uniref50" ? level.sequences[i].sa50 : (idType == "uniref90" ? level.sequences[i].sa90 : level.sequences[i].seqAcc);
                //ids.push(level.sequences[i].seqAcc);
                ids.push(id);
            }
        } else {
            for (var i = 0; i < level.children.length; i++) {
                var nextIds = nextLevel(level.children[i]);
                for (var j = 0; j < nextIds.length; j++) {
                    ids.push(nextIds[j]);
                }
            }
        }
        return ids;
    };

    var ids = nextLevel(data);
    ids = Array.from(new Set(ids));
    return ids;
}

function triggerDownload (imgURI) {
  var evt = new MouseEvent('click', {
    view: window,
    bubbles: false,
    cancelable: true
  });

  var a = document.createElement('a');
  a.setAttribute('download', 'MY_COOL_IMAGE.png');
  a.setAttribute('href', imgURI);
  a.setAttribute('target', '_blank');

  a.dispatchEvent(evt);
}

function commify(num) {
    return parseInt(num).toLocaleString();
}

// PUBLIC
function getColorForSunburstLevelFn() {
    var getKingdom = function (d) {
        // Root
        if (!d)
            return "";
        // Kingdom
        if (!d.theParent)
            return d.node;
        while (d.depth > 1)
           d = d.theParent;
        return d.node;
    };
    
    // From the sunbursts on http://pfam.xfam.org/
    var Colors = getColorList();

    var getColor = getColorFn(7);

    return function(d, depth) { // data object
        var K = getKingdom(d);
        // Root
        if (!K)
            return "gray";
        else if (K == "Root")
            return "white";
        if (typeof Colors[K] !== "undefined")
            return getColor(Colors[K], depth);
        else
            return "gray";
    };
}


