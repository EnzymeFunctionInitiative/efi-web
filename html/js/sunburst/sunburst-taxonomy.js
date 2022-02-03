
////////////////////////////////////////////////////////////////////////////////////////////////////
// SUNBURST
//

function AppSunburst(jobId, jobKey, unirefVersion, baseDir) {
    this.jobId = jobId;
    this.jobKey = jobKey;
    this.unirefVersion = unirefVersion;
    this.baseDir = baseDir;
}


function addParentRef(treeData, theParent) {
    treeData.theParent = theParent;
    if (typeof treeData.children !== "undefined") {
        for (var i = 0; i < treeData.children.length; i++) {
            addParentRef(treeData.children[i], treeData);
        }
    }
}


AppSunburst.prototype.addSunburstFeatureAsync = function() {

    var that = this;

    var progress = new Progress($("#sunburstProgressLoader"));
    progress.start();
    var parms = {id: this.jobId, key: this.jobKey};
    $.ajax({
        dataType: "json",
        url: that.baseDir + "/get_tax_data.php",
        data: parms,
        success: function(jsonData) {
            if (typeof(jsonData.valid) !== "undefined" && jsonData.valid == "false") {
                //TODO: handle error
                alert(jsonData.message);
                progress.stop();
            } else {
                that.addSunburstFeature(jsonData.data.data);
                progress.stop();
            }
        },
        error: function(jsonData, exception) {
            console.log("AJAX error: " + exception);
            progress.stop();
        }
    });
}


AppSunburst.prototype.addSunburstFeature = function(treeData) {

    var idTypeStr = this.unirefVersion ? "UniRef"+this.unirefVersion : "UniProt";
    var that = this;
    var Colors = getSunburstColorFn(); // from sunburst_helpers.js

    addParentRef(treeData, null);

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
            var theColor = levelsColorFn(data, i-1);
            var theLevel = depthMap[i][0];
            $("#sunburstChartLevels").append('<div style="background-color: ' + theColor + '" class="sunburst-level">' + theLevel + '</div>');
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
        $("#sbDownloadLink").attr("download", fname);
        $("#sbDownloadLink").attr("href", makeTextFile(text));
        $("#sbDownloadLink")[0].click();
    });
    $("#sunburstDlFasta").click(function() {
        that.getDownloadWarningFn();
    });
}
AppSunburst.prototype.getDownloadWarningFn = function() {
    var that = this;
    $("#sunburst-fasta-download").dialog({
        resizeable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Continue": function() {
                that.sunburstDownloadFasta();
                $(this).dialog("close");
            },
            Cancel: function() {
                $(this).dialog("close");
            }
        }
    });
}
function fixNodeName(str) {
    return str.replace(/[^a-z0-9]/gi, "_");
}
function getIdType() {
    return $("input[name='sunburstIdType']:checked").val();
}


AppSunburst.prototype.sunburstDownloadFasta = function() {
    var that = this;

    //var progress = new Progress($("#sunburstProgressLoader"));
    //progress.start();

    var idType = getIdType();
    var ids = getIdsFromTree(that.sbCurrentData, idType);
    var fixedNodeName = fixNodeName(that.sbCurrentData.node)
    var jsIds = JSON.stringify(ids);

    var form = $('<form method="POST" action="' + that.baseDir + '/get_sunburst_fasta.php"></form>');
    form.append($('<input name="id" type="hidden">').val(that.jobId));
    form.append($('<input name="key" type="hidden">').val(that.jobKey));
    form.append($('<input name="o" type="hidden">').val(fixedNodeName));
    form.append($('<input name="ids" type="hidden">').val(jsIds));
    form.append($('<input name="it" type="hidden">').val(idType));
    $("body").append(form);

    //$("#sbDownloadBtn").hide();

    form.submit();
    setTimeout(function() {
        //progress.stop();
    }, 1000);

    //var parms = {
    //    id: that.jobId,
    //    key: that.jobKey,
    //    o: fixedNodeName,
    //    ids: jsIds,
    //    it: idType,
    //    type: "jquery"
    //};

    // html5 type
    //var ajax = new XMLHttpRequest();
    //ajax.onreadystatechange = function() {
    //    if (ajax.readyState === 4 && ajax.status === 200) {
    //        var dummy = document.createElement("a");
    //        var json = JSON.decode(ajax.response);
    //        var blob = base64ToBlob(.data, ajax.response.download.mimetype);
    //        var url = window.URL.createObjectUrl(blob);
    //        dummy.href = url;
    //        dummy.download = ajax.response.download.filename;
    //        dummy.style.display = "none";
    //        dummy.click();
    //        window.URL.revokeObjectUrl(url);
    //    }
    //};
    //ajax.open("POST", that.baseDir + "/get_sunburst_fasta.php");
    //ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    //ajax.responseType = "text";
    //ajax.send(JSON.stringify(parms));
    //ajax.addEventListener("readystatechange", function() {
    //    if (ajax.readyState === XMLHttpRequest.DONE) {
    //        progress.stop();
    //    }
    //});

    // ajax type
    //$.ajax({
    //    type: "POST",
    //    dataType: "json",
    //    url: that.baseDir + "/get_sunburst_fasta.php",
    //    data: parms,
    //    success: function(data) {
    //        //progress.stop();
    //    }
    //});
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

function base64ToBlob(base64, mimetype, slicesize) {
    if (!window.atob || !window.Uint8Array) {
        // The current browser doesn't have the atob function. Cannot continue
        return null;
    }
    mimetype = mimetype || '';
    slicesize = slicesize || 512;
    var bytechars = atob(base64);
    var bytearrays = [];
    for (var offset = 0; offset < bytechars.length; offset += slicesize) {
        var slice = bytechars.slice(offset, offset + slicesize);
        var bytenums = new Array(slice.length);
        for (var i = 0; i < slice.length; i++) {
            bytenums[i] = slice.charCodeAt(i);
        }
        var bytearray = new Uint8Array(bytenums);
        bytearrays[bytearrays.length] = bytearray;
    }
    return new Blob(bytearrays, {type: mimetype});
}

