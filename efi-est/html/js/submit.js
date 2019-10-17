
var FORM_ACTION = "create.php";
var DEBUG = 0;
var ARCHIVE = 2;

//TODO: switch everything over to jQuery (currently the file upload stuff is in vanilla JS)

function getDefaultCompletionHandler() {
    var handler = function(jsonObj) {
        var nextStepScript = "stepb.php";
        window.location.href = nextStepScript + "?id=" + jsonObj.id;
    };
    return handler;
}

function addCommonFormData(opt, fd) {
    var email = $("#email-" + opt).val();
    var jobName = $("#job-name-" + opt).val();
    var famInput = $("#families-input-" + opt).val();
    var evalue = $("#evalue-" + opt).val();
    var dbMod = $("#db-mod-" + opt).val();
    var useUniref = $("#use-uniref-" + opt).prop("checked");
    var unirefVer = $("#uniref-ver-" + opt).val();
    var fraction = $("#fraction-" + opt).val();
    var cpuX2 = $("#cpu-x2-" + opt).prop("checked");
    var exlFrag = $("#exclude-fragments-" + opt).prop("checked");

    fd.append("email", email);
    fd.append("job-name", jobName);
    fd.append("families_input", famInput);
    fd.append("families_use_uniref", useUniref);
    fd.append("families_uniref_ver", unirefVer);
    fd.append("evalue", evalue);
    fd.append("fraction", fraction);
    fd.append("db-mod", dbMod);
    fd.append("cpu-x2", cpuX2);
    fd.append("exclude-fragments", exlFrag);
}

function submitOptionForm(option, famHelper, outputIds) {
    if (option == "opta")
        submitOptionAForm(famHelper, outputIds);
    else if (option == "optb")
        submitOptionBForm(famHelper, outputIds);
    else if (option == "optc")
        submitOptionCForm(famHelper, outputIds);
    else if (option == "optd")
        submitOptionDForm(famHelper, outputIds);
    else if (option == "opte")
        submitOptionEForm(famHelper, outputIds);
}

function submitOptionAForm(famHelper, outputIds) { // familySizeHelper

    var optionId = "opta";

    var submitFn = function() {
        var fd = new FormData();
        fd.append("option_selected", "A");
        addCommonFormData(optionId, fd);
        addParam(fd, "blast_input", "blast-input");
        addParam(fd, "blast_evalue", "blast-evalue");
        addParam(fd, "blast_max_seqs", "blast-max-seqs");
        
        var fileHandler = function(xhr) {};
        var completionHandler = getDefaultCompletionHandler();
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    if (!checkSequence($("#blast-input").val())) {
        $("#blast-input").addClass("input-error");
        $("#" + outputIds.warningMsg).text("Invalid Query Sequence.  Please input a protein sequence.");
        return false;
    }
    if (!famHelper.checkUnirefRequirement(optionId, submitFn)) {
        return false;
    }
}

function submitOptionBForm(famHelper, outputIds) {

    var optionId = "optb";

    var submitFn = function() {
        var fd = new FormData();
        fd.append("option_selected", "B");
        addCommonFormData(optionId, fd);
        addCbParam(fd, "pfam_domain", "domain-optb");
        addParam(fd, "pfam_seqid", "pfam-seqid");
        addParam(fd, "pfam_length_overlap", "pfam-length-overlap");
        
        var fileHandler = function(xhr) {};
        var completionHandler = getDefaultCompletionHandler();
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    if (!famHelper.checkUnirefRequirement(optionId, submitFn)) {
        return false;
    }
}

function submitOptionCForm(famHelper, outputIds) {

    var optionId = "optc";

    var submitFn = function() {
        var fd = new FormData();
        fd.append("option_selected", "C");
        addCommonFormData(optionId, fd);
        addParam(fd, "fasta_input", "fasta-input");
        addCbParam(fd, "fasta_use_headers", "fasta-use-headers");
    
        var completionHandler = getDefaultCompletionHandler();
        var fileHandler = function(xhr) {};
        var files = document.getElementById("fasta-file").files;
        if (files.length > 0) {
            fd.append("file", files[0]);
            fileHandler = function(xhr) {
                addUploadStuff(xhr, "progress-num-fasta", "progress-bar-fasta");
            };
        }
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    if (!famHelper.checkUnirefRequirement(optionId, submitFn)) {
        return false;
    }
}

function submitOptionDForm(famHelper, outputIds) {

    var optionId = "optd";

    var submitFn = function() {
        var source = $("#optionD-src-tabs").data("source");
        
        var fd = new FormData();
        fd.append("option_selected", "D");
        addCommonFormData(optionId, fd);
        addParam(fd, "accession_input", "accession-input-" + source);
        addCbParam(fd, "accession_use_uniref", "accession-use-uniref");
        addParam(fd, "accession_uniref_version", "accession-uniref-version");
        if (source == "uniprot")
            addParam(fd, "accession_seq_type", "uniprot");
        else
            addParam(fd, "accession_seq_type", "accession-seq-type");

        if ($("#domain-optd").prop("checked")) {
            fd.append("accession_use_dom", true);
            addParam(fd, "accession_dom_fam", "accession-input-domain-family");
            addRadioParam(fd, "accession_dom_reg", "accession-input-domain-region");
        }
    
        var completionHandler = getDefaultCompletionHandler();
        var fileHandler = function(xhr) {};
        var files = document.getElementById("accession-file-" + source).files;
        if (files.length > 0) {
            fd.append("file", files[0]);
            fileHandler = function(xhr) {
                addUploadStuff(xhr, "progress-num-accession-" + source, "progress-bar-accession-" + source);
            };
        }
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    if (!famHelper.checkUnirefRequirement(optionId, submitFn)) {
        return false;
    }
}

function submitOptionEForm(famHelper, outputIds) {

    var optionId = "opte";

    var submitFn = function() {
    var fd = new FormData();
        fd.append("option_selected", "E");
        addCommonFormData(optionId, fd);
        addCbParam(fd, "pfam_domain", "domain-opte");
        addParam(fd, "pfam_seqid", "seqid-opte");
        addParam(fd, "pfam_min_seq_len", "min-seq-len-opte");
        addParam(fd, "pfam_max_seq_len", "max-seq-len-opte");
        addParam(fd, "pfam_length_overlap", "length-overlap-opte");
        addCbParam(fd, "pfam_demux", "demux-opte");
    
        var fileHandler = function(xhr) {};
        var completionHandler = getDefaultCompletionHandler();
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    if (!famHelper.checkUnirefRequirement(optionId, submitFn)) {
        return false;
    }
}

function submitColorSsnForm() {

    var messageId = "message-colorssn";

    var fd = new FormData();
    fd.append("option_selected", "colorssn");
    addParam(fd, "email", "email-colorssn");
    addCbParam(fd, "extra_ram", "colorssn-extra-ram");

    var hmmOpt = "";
    if ($("#" + "colorssn-make-weblogo").prop("checked"))
        hmmOpt = "WEBLOGO";
    if ($("#" + "colorssn-make-hmm").prop("checked"))
        hmmOpt += ",HMM";
    if ($("#" + "colorssn-make-cr").prop("checked"))
        hmmOpt += ",CR";
    if ($("#" + "colorssn-make-hist").prop("checked"))
        hmmOpt += ",HIST";
    fd.append("make-hmm", hmmOpt);
    addParam(fd, "aa-threshold", "colorssn-aa-threshold");
    addParam(fd, "hmm-aa", "colorssn-hmm-aa-list");
    addParam(fd, "min-seq-msa", "colorssn-min-seq-msa");
    //addCbParam(fd, "exlude-fragments", "colorssn-exclude-fragments");
    var completionHandler = getDefaultCompletionHandler();
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

function submitStepEColorSsnForm(analysisId, ssnIndex) {

    var fd = new FormData();
    fd.append("option_selected", "colorssn");
    fd.append("ssn-source-id", analysisId);
    fd.append("ssn-source-idx", ssnIndex);

    var completionHandler = getDefaultCompletionHandler();
    var fileHandler = function(xhr) {};

    doFormPost(FORM_ACTION, fd, "", fileHandler, completionHandler);
}


function requestJobUpdate(generateId, analysisId, jobKey, requestType, jobType) {
    var fd = new FormData();
    fd.append("id", generateId);
    fd.append("key", jobKey);
    if (requestType == "cancel") {
        fd.append("rt", "c");
    } else if (requestType == "archive") {
        fd.append("rt", "a");
        fd.append("aid", analysisId);
    }

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
        document.getElementById(progressTextId).innerHTML = percentComplete.toString() + '%';
        var bar = document.getElementById(progressBarId);
        bar.value = percentComplete;
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
                    if (jsonObj.cookieInfo)
                        document.cookie = jsonObj.cookieInfo;
                    completionHandler(jsonObj);
                }
                if (!jsonObj.valid && jsonObj.message) {
                    document.getElementById(messageId).innerHTML = jsonObj.message;
                } else {
                    if (messageId)
                        document.getElementById(messageId).innerHTML = "";
                }
            }
        }
    }
}

function addCbParam(fd, param, id) {
    var val = $("#" + id).prop("checked");;
    if (typeof val !== "undefined")
        fd.append(param, val);
}

function addRadioParam(fd, param, groupName) {
    var value = $("input[name='" + groupName + "']:checked").val();
    if (value)
        fd.append(param, value);
}

function addParam(fd, param, id) {
    var val = $("#" + id).val();
    if (typeof val !== "undefined")
        fd.append(param, val);
}


function toggleUniref(comboId, unirefCheckbox) {
    if (unirefCheckbox.checked) {
        document.getElementById(comboId).disabled = false;
    } else {
        document.getElementById(comboId).disabled = true;
    }
}

