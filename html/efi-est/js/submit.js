
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
    var largeMem = $("#large-mem-" + opt).prop("checked");
    var exlFrag = $("#exclude-fragments-" + opt).prop("checked");
    var allSeq = $("#include-all-seq-" + opt).prop("checked");

    fd.append("email", email);
    fd.append("job-name", jobName);
    fd.append("families_input", famInput);
    fd.append("families_use_uniref", useUniref);
    fd.append("families_uniref_ver", unirefVer);
    if (evalue)
        fd.append("evalue", evalue);
    if (fraction)
        fd.append("fraction", fraction);
    fd.append("db-mod", dbMod);
    fd.append("cpu-x2", cpuX2);
    fd.append("large-mem", largeMem);
    if (exlFrag)
        fd.append("exclude-fragments", exlFrag);
    if (allSeq)
        fd.append("include-all-seq", allSeq);

    addTaxSearch(opt, fd);
}

function addTaxSearch(opt, fd) {
    var containerId = '#taxonomy-' + opt + '-container';
    $(containerId + " .tax-group").each(function(index) {
        var divs = $(this).children();
        var taxSelect = divs[0].children[0].value;
        var taxSearch = divs[1].children[0].value;
        if (taxSelect && taxSearch) {
            var taxGroup = taxSelect + ":" + taxSearch;
            fd.append("tax_search[]", taxGroup);
        }
    });
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
        addParam(fd, "blast_db_type", "blast-db-type");
        
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
        addCbParam(fd, "domain", "domain-optb");
        if ($("#domain-optb").prop("checked")) {
            addRadioParam(fd, "domain_region", "domain-region-optb");
        }
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
            fd.append("domain", true);
            addParam(fd, "domain_family", "domain-family-optd");
            addRadioParam(fd, "domain_region", "domain-region-optd");
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

function submitTaxonomyForm(option) {
    option = option || "opt_tax";

    var messageId = "message-" + option;

    var fd = new FormData();
    fd.append("option_selected", option);

    addCommonFormData(option, fd);
    
    var completionHandler = getDefaultCompletionHandler();
    var fileHandler = function(xhr) {};

    doFormPost(FORM_ACTION, fd, messageId, fileHandler, completionHandler);
}

function submitColorSsnForm(type) { // the parameters are optional
    type = type || "";

    var option = type ? type : "colorssn";
    var messageId = "message-" + option;

    var fd = new FormData();
    fd.append("option_selected", option);
    addParam(fd, "email", "email-" + option);
    addParam(fd, "efiref", option + "-efiref");
    addCbParam(fd, "skip_fasta", option + "-skip-fasta");
    
    var id = option + "-extra-ram";
    var val = $("#" + id).prop("checked");
    if (typeof val !== "undefined") {
        if (val) {
            var val2 = $("#" + id + "-val").val();
            if (typeof val2 !== "undefined" && val2> 1)
                fd.append("extra_ram", val2);
        }
    }

    if (type == "cluster") {
        var hmmOpt = "";
        if ($("#" + "make-weblogo-" + option).prop("checked"))
            hmmOpt = "WEBLOGO";
        if ($("#" + "make-hmm-" + option).prop("checked"))
            hmmOpt += ",HMM";
        if ($("#" + "make-cr-" + option).prop("checked"))
            hmmOpt += ",CR";
        if ($("#" + "make-hist-" + option).prop("checked"))
            hmmOpt += ",HIST";
        fd.append("make-hmm", hmmOpt);
        addParam(fd, "aa-threshold", "aa-threshold-" + option);
        addParam(fd, "hmm-aa", "hmm-aa-list-" + option);
        addParam(fd, "min-seq-msa", "min-seq-msa-" + option);
        addParam(fd, "max-seq-msa", "max-seq-msa-" + option);
    } else if (type == "cr") {
        addParam(fd, "ascore", option + "-ascore");
        addParam(fd, "color-ssn-source-color-id", "color-ssn-source-color-id");
    }
    addParam(fd, "ssn-source-id", "ssn-source-id-" + option);
    addParam(fd, "ssn-source-idx", "ssn-source-idx-" + option);
    //addCbParam(fd, "exlude-fragments", "exclude-" + option);
    
    var completionHandler = getDefaultCompletionHandler();
    var fileHandler = function(xhr) {};
    var files = document.getElementById(option + "-file").files;
    if (files.length > 0) {
        fd.append("file", files[0]);
        fileHandler = function(xhr) {
            addUploadStuff(xhr, "progress-num-" + option, "progress-bar-" + option);
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


function requestJobUpdate(generateId, analysisId, jobKey, requestType, jobType, elementHideFn) {
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
    //var completionHandler = function(jsonObj) { window.location.href = "index.php"; };
    var completionHandler = elementHideFn;

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

    xhr.open("POST", formAction, true);
    xhr.send(formData);
    xhr.onreadystatechange  = function(){
        if (xhr.readyState == 4  ) {
            // Javascript function JSON.parse to parse JSON data
            var jsonObj = JSON.parse(xhr.responseText);

            console.log(messageId);
    
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

function addCbParam(fd, param, id) {
    var val = $("#" + id).prop("checked");;
    if (typeof val !== "undefined") {
        fd.append(param, val);
        return true;
    } else {
        return false;
    }
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

