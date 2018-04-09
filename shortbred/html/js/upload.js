

function uploadFile(fileInputId, formId, progressNumId, progressBarId, messageId, emailId, submitId, jobGroupId, isSsn) {
    var fd = new FormData();
    addParam(fd, "email", emailId);
    addParam(fd, "submit", submitId);
    addParam(fd, "job-group", jobGroupId);

    var files = document.getElementById(fileInputId).files;
    var completionHandler = function() { enableForm(formId); };
    fd.append("file", files[0]);
    var fileHandler = function(xhr) {
        addUploadStuff(xhr, progressNumId, progressBarId);
    };

    disableForm(formId);

    var script = "upload_ssn.php";
    doFormPost(script, fd, messageId, fileHandler, completionHandler);
    
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
                var nextStepScript = "stepb.php";
                if (jsonObj.cookieInfo)
                    document.cookie = jsonObj.cookieInfo;
                window.location.href = nextStepScript + "?id=" + jsonObj.id + "&key=" + jsonObj.key;
            }
            if (jsonObj.message) {
                document.getElementById(messageId).innerHTML = jsonObj.message;
            } else {
                completionHandler();
                document.getElementById(messageId).innerHTML = "";
            }
        }
    }
}

