
function submitQuantify(formId, selectId, searchTypeId, jobNameId, messageId, sbId, sbKey) {

    var datasetType = "";

    if (!selectId) {
        var selTab = $("#download-tabs .tab-headers li.ui-tabs-active a").attr("href");
        selTab = selTab.substr(4);
        selectId = selTab + "_search_to";
        datasetType = $("#" + selTab + "-dt").val();
    }

    var fd = new FormData();
    fd.append("id", sbId);
    fd.append("key", sbKey);
    addParam(fd, "search-type", searchTypeId);
    addParam(fd, "job-name", jobNameId);
    
    var hmpIds = [];
    var selObj = document.getElementById(selectId);
    for (var i = 0; i < selObj.options.length; i++) {
        hmpIds.push(selObj.options[i].value);
    }

    if (hmpIds.length == 0) {
        alert("You must select at least one metagenome.");
        return;
    }

    fd.append("hmp-ids", hmpIds);
    if (datasetType)
        fd.append("dataset-type", datasetType);

    var completionHandler = function(jsonObj) {
        enableForm(formId);
        var nextStepScript = "stepd.php";
        window.location.href = nextStepScript + "?id=" + sbId + "&quantify-id=" + jsonObj.quantify_id + "&key=" + sbKey;
    };
    var fileHandler = function(xhr) {};

    disableForm(formId);

    var script = "submit_quantify.php";
    doFormPost(script, fd, messageId, fileHandler, completionHandler);
}

function submitFromESTJob(estId, estKey) {
    uploadInitialSSNFileShared(estId, estKey);
}

function uploadInitialSSNFile() {
    uploadInitialSSNFileShared(0, "");
}

function uploadInitialSSNFileShared(estId, estKey) {
    fileInputId = "ssn_file";
    formId = "upload_form";
    progressNumId = "progress_number";
    progressBarId = "progress_bar";
    messageId = "ssn_message";

    var fd = new FormData();
    addParam(fd, "email", "ssn_email");
    addParam(fd, "submit", "ssn_submit");
    addParam(fd, "min-seq-len", "ssn_min_seq_len");
    addParam(fd, "max-seq-len", "ssn_max_seq_len");
    addParam(fd, "search-type", "ssn_search_type");
    addParam(fd, "ref-db", "ssn_ref_db");
    addParam(fd, "cdhit-sid", "ssn_cdhit_sid");
    addParam(fd, "diamond-sens", "ssn_diamond_sens");
    addParam(fd, "db-mod", "ssn_db_mod");

    var fileHandler;
    var completionHandler;

    if (estId && estKey) {
        fd.append("est-id", estId);
        fd.append("est-key", estKey);
        completionHandler = function(jsonObj) {
            var nextStepScript = "stepb.php";
            window.location.href = nextStepScript + "?id=" + jsonObj.id + "&key=" + jsonObj.key;
        };
    } else {
        var files = document.getElementById(fileInputId).files;
        var completionHandler = function(jsonObj) {
            enableForm(formId);
            var nextStepScript = "stepb.php";
            window.location.href = nextStepScript + "?id=" + jsonObj.id + "&key=" + jsonObj.key;
        };
    
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, progressNumId, progressBarId);
        };
    
        disableForm(formId);
    }

    var script = "upload_ssn.php";
    doFormPost(script, fd, messageId, fileHandler, completionHandler);
}

function uploadAlignmentScoreUpdateSSN(fileInputId, formId, progressNumId, progressBarId, messageId, email, parentId, parentKey) {
    var fd = new FormData();
    fd.append("email", email);
    fd.append("submit", "submit");
    fd.append("update-id", parentId);
    fd.append("update-key", parentKey);

    var files = document.getElementById(fileInputId).files;
    var completionHandler = function(jsonObj) {
        enableForm(formId);
        var nextStepScript = "stepb.php";
        window.location.href = nextStepScript + "?id=" + jsonObj.id + "&key=" + jsonObj.key;
    };

    fd.append("file", files[0]);
    var fileHandler = function(xhr) {
        addUploadStuff(xhr, progressNumId, progressBarId);
    };

    disableForm(formId);

    var script = "upload_ssn.php";
    doFormPost(script, fd, messageId, fileHandler, completionHandler);
}

function requestJobUpdate(generateId, jobKey, quantifyId, requestType, jobType) {
    var fd = new FormData();
    fd.append("id", generateId);
    fd.append("key", jobKey);
    if (requestType == "cancel")
        fd.append("rt", "c");
    else if (requestType == "archive")
        fd.append("rt", "a");
    if (quantifyId)
        fd.append("quantify-id", quantifyId);

    var fileHandler = function(xhr) { };
    var completionHandler = function(jsonObj) { window.location.href = "index.php"; };

    var script = "update_job_status.php";
    doFormPost(script, fd, "", fileHandler, completionHandler);
}

function addUploadStuff(xhr, progressNumId, progressBarId) {
    xhr.upload.addEventListener("progress", function(evt) { uploadProgress(evt, progressNumId, progressBarId);}, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);
}

function uploadProgress(evt, progressTextId, progressBarId) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 100 / evt.total);
        document.getElementById(progressTextId).innerHTML = "Uploading File: " + percentComplete.toString() + '%';
        var bar = document.getElementById(progressBarId);
        bar.value = percentComplete;
    }
    else {
        document.getElementById(progressTextId).innerHTML = 'unable to compute';
    }
}

function uploadComplete(evt) {
    /* This event is raised when the server send back a response */
    //alert(evt.target.responseText);
}

function uploadFailed(evt) {
    alert("There was an error attempting to upload the file.");
}

function uploadCanceled(evt) {
    alert("The upload has been canceled by the user or the browser dropped the connection.");
}

function disableForm(formId) {
    document.getElementById(formId).disabled = true;
}

function enableForm(formId) {
    document.getElementById(formId).disabled = false;
}

function addParam(fd, param, id) {
    var elem = document.getElementById(id);
    if (elem)
        fd.append(param, elem.value);
}

function doFormPost(formAction, formData, messageId, fileHandler, completionHandler) {
    var xhr = new XMLHttpRequest();
    if (typeof fileHandler === "function")
        fileHandler(xhr);
    xhr.open("POST", formAction, true);
    xhr.send(formData);
    xhr.onreadystatechange  = function(){
        if (xhr.readyState == 4  ) {

            // Javascript function JSON.parse to parse JSON data
            var jsonObj = JSON.parse(xhr.responseText);

            // jsonObj variable now contains the data structure and can
            // be accessed as jsonObj.name and jsonObj.country.
            if (jsonObj.valid) {
                if (jsonObj.cookieInfo)
                    document.cookie = jsonObj.cookieInfo;
            }
            if (jsonObj.message) {
                if (messageId)
                    document.getElementById(messageId).innerHTML = jsonObj.message;
            } else if (jsonObj.valid) {
                completionHandler(jsonObj);
                if (messageId)
                    document.getElementById(messageId).innerHTML = "";
            }
        }
    }
}

