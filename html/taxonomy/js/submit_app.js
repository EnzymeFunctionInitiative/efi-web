
var FORM_ACTION = "create.php";
var DEBUG = 0;
var ARCHIVE = 2;

//TODO: switch everything over to jQuery (currently the file upload stuff is in vanilla JS)

function AppTaxSubmit(idData, taxonomyApp) {
    this.idData = idData;
    this.taxApp = taxonomyApp;
}


function getDefaultCompletionHandler() {
    var handler = function(jsonObj) {
        var nextStepScript = "stepb.php";
        window.location.href = nextStepScript + "?id=" + jsonObj.id;
    };
    return handler;
}

AppTaxSubmit.prototype.addCommonFormData = function(opt, fd) {
    var email = $("#email-" + opt).val();
    var jobName = $("#job-name-" + opt).val();
    var famInput = $("#families-input-" + opt).val();
    var dbMod = $("#db-mod-" + opt).val();
    var exlFrag = $("#exclude-fragments-" + opt).prop("checked");

    fd.append("email", email);
    fd.append("job-name", jobName);
    fd.append("families_input", famInput);
    fd.append("db-mod", dbMod);
    if (exlFrag)
        fd.append("exclude-fragments", exlFrag);

    var taxGroups = this.taxApp.getTaxSearchConditions(opt);
    taxGroups.forEach((group) => fd.append("tax_search[]", group));
};

AppTaxSubmit.prototype.submitOptionForm = function(optionId) {
    var submitFn = false;
    var outputIds = this.idData[optionId].output;
    if (optionId == "optb")
        submitFn = this.getOptionBFormFn(outputIds);
    else if (optionId == "optc")
        submitFn = this.getOptionCFormFn(outputIds);
    else if (optionId == "optd")
        submitFn = this.getOptionDFormFn(outputIds);

    if (typeof submitFn === "function") {
        submitFn();
    }
}

AppTaxSubmit.prototype.getOptionBFormFn = function(outputIds) {

    var that = this;

    var optionId = "optb";

    var submitFn = function() {
        var fd = new FormData();
        fd.append("option_selected", "B");
        that.addCommonFormData(optionId, fd);
        
        var fileHandler = function(xhr) {};
        var completionHandler = getDefaultCompletionHandler();
    
        doFormPost(FORM_ACTION, fd, outputIds.warningMsg, fileHandler, completionHandler);
    };

    return submitFn;
};

AppTaxSubmit.prototype.getOptionCFormFn = function(outputIds) {

    var that = this;

    var optionId = "optc";

    var submitFn = function() {
        var fd = new FormData();
        fd.append("option_selected", "C");
        that.addCommonFormData(optionId, fd);
        addParam(fd, "fasta_input", "fasta-input");
    
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

    return submitFn;
};

AppTaxSubmit.prototype.getOptionDFormFn = function(outputIds) {

    var that = this;

    var optionId = "optd";

    var submitFn = function() {
        var source = $("#optionD-src-tabs").data("source");
        
        var fd = new FormData();
        fd.append("option_selected", "D");
        that.addCommonFormData(optionId, fd);
        addParam(fd, "accession_input", "accession-input-" + source);
        addParam(fd, "accession_uniref_version", "accession-uniref-version");
        if (source == "uniprot")
            addParam(fd, "accession_seq_type", "uniprot");
        else
            addParam(fd, "accession_seq_type", "accession-seq-type");

        console.log(source);
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

    return submitFn;
};



