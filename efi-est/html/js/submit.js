
var FORM_ACTION = "create.php";
var DEBUG = 0;


function submitOptionAForm() {

    var messageId = "option-a-message";
    
    var fd = new FormData();
    fd.append("option_selected", "A");
    addParam(fd, "email", "option-a-email");
    addParam(fd, "job-group", "option-a-job-group");
    addParam(fd, "blast_input", "blast-input");
    addParam(fd, "evalue", "blast-evalue");
    addParam(fd, "blast_max_seqs", "blast-max-seqs");
    addParam(fd, "fraction", "blast-fraction");
    addParam(fd, "families_input", "families-input-opta");
    addCbParam(fd, "families_use_uniref", "opta-use-uniref");
    var fileHandler = function(xhr) {};
    var completionHandler = function() {};

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitOptionBForm() {

    var messageId = "option-b-message";

    var fd = new FormData();
    fd.append("option_selected", "B");
    addParam(fd, "email", "option-b-email");
    addParam(fd, "job-group", "option-b-job-group");
    addParam(fd, "families_input", "families-input");
    addParam(fd, "evalue", "pfam-evalue");
    addParam(fd, "fraction", "pfam-fraction");
    addCbParam(fd, "pfam_domain", "pfam-domain");
    addParam(fd, "pfam_seqid", "pfam-seqid");
    addParam(fd, "pfam_length_overlap", "pfam-length-overlap");
    addCbParam(fd, "families_use_uniref", "pfam-use-uniref");
    var fileHandler = function(xhr) {};
    var completionHandler = function() {};

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitOptionCForm() {

    var messageId = "option-c-message";

    var fd = new FormData();
    fd.append("option_selected", "C");
    addParam(fd, "email", "option-c-email");
    addParam(fd, "job-group", "option-c-job-group");
    addParam(fd, "fasta_input", "fasta-input");
    addCbParam(fd, "fasta_use_headers", "fasta-use-headers");
    addParam(fd, "families_input", "families-input-optc");
    addCbParam(fd, "families_use_uniref", "optc-use-uniref");
    addParam(fd, "evalue", "fasta-evalue");
    addParam(fd, "fraction", "fasta-fraction");

    var completionHandler = function() {};
    var fileHandler = function(xhr) {};
    var files = document.getElementById("fasta-file").files;
    if (files.length > 0) {
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, "progress-num-fasta", "progress-bar-fasta");
        };
    }

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitOptionDForm() {

    var messageId = "option-d-message";

    var fd = new FormData();
    fd.append("option_selected", "D");
    addParam(fd, "email", "option-d-email");
    addParam(fd, "job-group", "option-d-job-group");
    addParam(fd, "accession_input", "accession-input");
    addParam(fd, "families_input", "families-input-optd");
    addCbParam(fd, "families_use_uniref", "optd-use-uniref");
    addCbParam(fd, "accession_use_uniref", "accession-use-uniref");
    addParam(fd, "accession_uniref_version", "accession-uniref-version");
    addParam(fd, "evalue", "accession-evalue");
    addParam(fd, "fraction", "accession-fraction");

    var completionHandler = function() {};
    var fileHandler = function(xhr) {};
    var files = document.getElementById("accession-file").files;
    if (files.length > 0) {
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, "progress-num-accession", "progress-bar-accession");
        };
    }

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitOptionEForm() {

    var messageId = "option-e-message";

    var fd = new FormData();
    fd.append("option_selected", "E");
    addParam(fd, "email", "option-e-email");
    addParam(fd, "job-group", "option-e-job-group");
    addParam(fd, "families_input", "option-e-input");
    addParam(fd, "evalue", "pfam-plus-evalue");
    addParam(fd, "fraction", "pfam-plus-fraction");
    addCbParam(fd, "pfam_domain", "pfam-plus-domain");
    addParam(fd, "pfam_seqid", "pfam-plus-seqid");
    addParam(fd, "pfam_length_overlap", "pfam-plus-length-overlap");
    addCbParam(fd, "pfam_demux", "pfam-plus-demux");
    var fileHandler = function(xhr) {};
    var completionHandler = function() {};

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitColorSsnForm() {

    var messageId = "colorssn-message";

    var fd = new FormData();
    fd.append("option_selected", "colorssn");
    addParam(fd, "email", "colorssn-email");
    addParam(fd, "job-group", "colorssn-job-group");
    var completionHandler = function() {};
    var fileHandler = function(xhr) {};
    var files = document.getElementById("colorssn-file").files;
    if (files.length > 0) {
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, "progress-num-colorssn", "progress-bar-colorssn");
        };
    }

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
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
}

function uploadFailed(evt) {
    alert("There was an error attempting to upload the file.");
}

function uploadCanceled(evt) {
    alert("The upload has been canceled by the user or the browser dropped the connection.");
}





function doFormPost(formAction, formData, messageId, fileHandler, completionHandler) {

    formData.append("submit", "submit");

    var xhr = new XMLHttpRequest();
    if (typeof fileHandler === "function")
        fileHandler(xhr);

    if (DEBUG) {
        for (var pair of formData.entries()) {
            console.log(pair[0] + " = " + pair[1]);
        }
    } else {
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
                    window.location.href = nextStepScript + "?id=" + jsonObj.id;
                }
                if (!jsonObj.valid && jsonObj.message) {
                    document.getElementById(messageId).innerHTML = jsonObj.message;
                } else {
                    completionHandler();
                    document.getElementById(messageId).innerHTML = "";
                }
            }
        }
    }
}

function addCbParam(fd, param, id, isCheckbox) {
    if (typeof id === 'undefined')
        id = param;
    var isChecked = document.getElementById(id).checked;
    fd.append(param, isChecked);
}


function addParam(fd, param, id, isCheckbox) {
    if (typeof id === 'undefined')
        id = param;
    var elem = document.getElementById(id);
    if (elem)
        fd.append(param, elem.value);
}


function toggleUniref(comboId, unirefCheckbox) {
    if (unirefCheckbox.checked) {
        document.getElementById(comboId).disabled = false;
    } else {
        document.getElementById(comboId).disabled = true;
    }
}

