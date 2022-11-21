
var DIAGRAM_UPLOAD = 0;
var SSN_UPLOAD = 1;
var ARCHIVE = 2;

function submitEstJob(args) {
    args.isSsn = true;
    uploadFileShared(args);
    //uploadFileShared("", formId, "", "", messageId, emailId, submitId, true, estId, estKey, estSsn, false);
}

function uploadSsn(args) {
    args.isSsn = true;
    uploadFileShared(args);
    //uploadFileShared(fileInputId, formId, progressNumId, progressBarId, messageId, emailId, submitId, true, 0, "", 0, false);
}

function uploadSsnFilter(args) {
//function uploadSsnFilter(fileInputId, formId, progressNumId, progressBarId, messageId, emailAddr, submitId) {
    args.isFilterSubmit = true;
    uploadFileShared(args);
    //uploadFileShared(fileInputId, formId, progressNumId, progressBarId, messageId, emailAddr, submitId, true, 0, "", 0, true);
}

function uploadDiagramFile(args) {
//function uploadDiagramFile(fileInputId, formId, progressNumId, progressBarId, messageId, emailId, submitId) {
    args.isSsn = false;
    uploadFileShared(args);
    //uploadFileShared(fileInputId, formId, progressNumId, progressBarId, messageId, emailId, submitId, isSsn, 0, "", 0, false);
}

function uploadFileShared(args) {
    
    var fd = new FormData();
    if (args.isFilterSubmit && args.emailId.includes("@"))
        fd.append("email", args.emailId); // in this case emailId is actually the email address
    else if (args.emailId.length > 0)
        addParam(fd, "email", args.emailId);
    addParam(fd, "submit", args.submitId);
    if (args.isSsn) {
        addParam(fd, "neighbor_size", "neighbor_size");
        addParam(fd, "cooccurrence", "cooccurrence");
        addParam(fd, "db_mod", "db_mod");
        addParam(fd, "parent_id", "parent_id");
        addParam(fd, "parent_key", "parent_key");
        addParam(fd, "extra_ram", "extra_ram", true);
    }

    var completionHandler = function() { enableForm(args.formId); };
    var fileHandler = function(xhr) {};
    if (typeof args.estId !== "undefined" && args.estId) {
        fd.append("est-id", args.estId);
        fd.append("est-key", args.estKey);
        fd.append("est-ssn", args.estSsn);
    } else {
        var files = document.getElementById(args.fileInputId).files;
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, args.progressNumId, args.progressBarId);
        };
    }

    disableForm(args.formId);
    var script = args.isSsn ? "upload_ssn.php" : "upload_diagram.php";

    var uploadType = args.isSsn ? SSN_UPLOAD : DIAGRAM_UPLOAD;
    doFormPost(script, fd, args.messageId, fileHandler, uploadType, completionHandler);
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
//    document.getElementById('ssn_file').disabled = true;
//    document.getElementById('neighbor_size').disabled = true;
//    document.getElementById('email').disabled = true;
//    document.getElementById('submit').disabled = true;
}

function enableForm(formId) {
    document.getElementById(formId).disabled = false;
//    document.getElementById('ssn_file').disabled = false;
//    document.getElementById('neighbor_size').disabled = false;
//    document.getElementById('email').disabled = false;
//    document.getElementById('submit').disabled = false;
}

function addParam(fd, param, id, isCb) {
    isCb = isCb || false;
    if (!id)
        return;
    var elem = document.getElementById(id);
    if (elem) {
        if (isCb)
            fd.append(param, elem.checked);
        else
            fd.append(param, elem.value);
    }
}

function submitOptionAForm(formAction, args) {
//function submitOptionAForm(formAction, optionId, inputId, titleId, evalueId, maxSeqId, emailId, nbSizeId, messageId, dbModId, seqTypeId) {

    var fd = new FormData();
    addParam(fd, "option", args.optionId);
    addParam(fd, "title", args.titleId);
    addParam(fd, "sequence", args.inputId);
    addParam(fd, "evalue", args.evalueId);
    addParam(fd, "max-seqs", args.maxSeqId);
    addParam(fd, "nb-size", args.nbSizeId);
    addParam(fd, "email", args.emailId);
    addParam(fd, "db-mod", args.dbModId);
    addParam(fd, "seq-type", args.seqTypeId);
    var fileHandler = function(xhr) {};
    var completionHandler = function() {};

    doFormPost(formAction, fd, args.messageId, fileHandler, DIAGRAM_UPLOAD, completionHandler);
}


function submitOptionDForm(formAction, args) {
//function submitOptionDForm(formAction, optionId, inputId, titleId, emailId, nbSizeId, fileId, progressNumId, progressBarId, messageId, dbModId, seqTypeId) {
    args.inputField = "ids";
    submitOptionForm(formAction, args);
    //submitOptionForm(formAction, optionId, "ids", inputId, titleId, emailId, nbSizeId, fileId, progressNumId, progressBarId, messageId, dbModId, seqTypeId);
}


function submitOptionCForm(formAction, args) {
//function submitOptionCForm(formAction, optionId, inputId, titleId, emailId, nbSizeId, fileId, progressNumId, progressBarId, messageId, dbModId) {
    args.inputField = "fasta";
    args.seqTypeId = "";
    submitOptionForm(formAction, args);
    //submitOptionForm(formAction, optionId, "fasta", inputId, titleId, emailId, nbSizeId, fileId, progressNumId, progressBarId, messageId, dbModId, "");
}


function submitOptionForm(formAction, args) {
//function submitOptionForm(formAction, optionId, inputField, inputId, titleId, emailId, nbSizeId, fileId, progressNumId, progressBarId, messageId, dbModId, seqTypeId) {
    var fd = new FormData();
    addParam(fd, "option", args.optionId);
    addParam(fd, "title", args.titleId);
    addParam(fd, args.inputField, args.inputId);
    addParam(fd, "nb-size", args.nbSizeId);
    addParam(fd, "email", args.emailId);
    addParam(fd, "db-mod", args.dbModId);
    if (args.seqTypeId)
        addParam(fd, "seq-type", args.seqTypeId);

    if (typeof args.taxId !== "undefined" && args.taxId) {
        fd.append("tax-id", args.taxId);
        fd.append("tax-tree-id", args.taxTreeId);
        fd.append("tax-id-type", args.taxIdType);
        fd.append("tax-key", args.taxKey);
    }

    var files = document.getElementById(args.fileId).files;
    var fileHandler = function(xhr) {};
    var completionHandler = function() {};
    if (files.length > 0) {
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, args.progressNumId, args.progressBarId);
        };
    }

    doFormPost(formAction, fd, args.messageId, fileHandler, DIAGRAM_UPLOAD, completionHandler);
}


function requestJobUpdate(identifyId, jobKey, requestType, jobType) {
    var fd = new FormData();
    fd.append("id", identifyId);
    fd.append("key", jobKey);
    if (requestType == "cancel")
        fd.append("rt", "c");
    else if (requestType == "archive")
        fd.append("rt", "a");
    if (jobType == "gnn")
        fd.append("jt", "g");
    else if (jobType == "diagram")
        fd.append("jt", "d");

    var fileHandler = function(xhr) { };
    var completionHandler = function(jsonObj) { window.location.href = "index.php"; };

    var script = "update_job_status.php";
    doFormPost(script, fd, "", fileHandler, ARCHIVE, completionHandler);
}


function doFormPost(formAction, formData, messageId, fileHandler, requestType, completionHandler) {
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
            if (jsonObj.valid && requestType != ARCHIVE) {
                var nextStepScript = "stepb.php";
                var diagUpload = requestType == SSN_UPLOAD ? "" : "&diagram=1";
                if (jsonObj.cookieInfo)
                    document.cookie = jsonObj.cookieInfo;
                window.location.href = nextStepScript + "?id=" + jsonObj.id + "&key=" + jsonObj.key + diagUpload;
            }
            if (jsonObj.message) {
                document.getElementById(messageId).innerHTML = jsonObj.message;
            } else {
                completionHandler();
                if (messageId)
                    document.getElementById(messageId).innerHTML = "";
            }
        }
    }
}

