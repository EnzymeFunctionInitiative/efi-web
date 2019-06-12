

const ZOOM_FACTOR_OUT_LARGE = 0.25;
const ZOOM_FACTOR_OUT_SMALL = 0.88888888888888888;
const ZOOM_FACTOR_IN_SMALL = 1.125;
const ZOOM_FACTOR_IN_LARGE = 4;


function checkBrowserSupport() {
    //TODO: do check for class support
    var supported = (navigator.userAgent.search("Chrome") >= 0 ||
                         navigator.userAgent.search("Firefox") >= 0 ||
                         (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0)) &&
                        navigator.userAgent.search("Edge") < 0;

    if (supported) {
        $("#save-canvas-button").show();
    } else {
        $("#save-canvas-button").hide();
    }
    return supported;
}



class GndUi {
    constructor(msgRouter, controller, uiFilter) {
        this.msgRouter = msgRouter;
        this.XT = controller;
        this.uiFilter = uiFilter;

        this.filterAnnoToggleId = "";
        this.filterAnnoToggleLabelId = "";
        this.diagramCountId = "";
        this.diagramTotalId = "";
        this.loaderMessageId = "";
        this.progressBarId = "";

        this.msgRouter.addListener(this);
    }

    onMessage(payload) {
//        if (payload.MessageType == "UpdateShowButtonStatus") {
//            console.log("Update " + payload.Data.Disabled);
//            if (payload.Data.Disabled) {
//                this.showMoreBtn.prop("disabled", "true").addClass("disabled");
//                this.showAllBtn.prop("disabled", "true").addClass("disabled");
//            } else {
//                this.showMoreBtn.prop("disabled", "false").removeClass("disabled");
//                this.showAllBtn.prop("disabled", "false").removeClass("disabled");
//            }
//        } else
        if (payload.MessageType == "DataRetrievalStatus") {
            if (payload.Data.Retrieving) {  // Called at start of retrieval
                this.progressLoader.removeClass("hidden-placeholder");
                if (!payload.Data.Initial) {
                    this.updateProgressBar(payload.Data.PercentCompleted);
                    $(this.loaderMessageId).hide();
                } else {
                    $(this.loaderMessageId).text(payload.Data.Message).show();
                }
                $(".zoom-btn").attr("disabled", true).addClass("disabled");
                this.showMoreBtn.attr("disabled", "true").addClass("disabled");
                this.showAllBtn.attr("disabled", "true").addClass("disabled");
            } else {
                $(this.loaderMessageId).hide();
                this.progressLoader.addClass("hidden-placeholder");
                this.updateProgressBar(-1);
                $(".zoom-btn").attr("disabled", false).removeClass("disabled");
                var enableSp = this.uiFilter.getFilterHasSwissProt();
                if (enableSp) {
                    $(this.filterAnnoToggleId).attr("disabled", false);
                    $(this.filterAnnoToggleLabelId).attr("data-original-title", "").removeClass("disabled");
                } else {
                    $(this.filterAnnoToggleId).attr("disabled", true);
                    $(this.filterAnnoToggleLabelId).attr("data-original-title", "No SwissProt annotated proteins are present.").addClass("disabled");
                }
                if (!payload.Data.EndOfData) {
                    this.showMoreBtn.removeAttr("disabled").removeClass("disabled");
                    this.showAllBtn.removeAttr("disabled").removeClass("disabled");
                }
                $(this.diagramCountId).text(payload.Data.DiagramCount);
            }
            if (payload.Data.Message) {
            } else {
            }
        } else if (payload.MessageType == "InitDataRetrieved") {
            this.setTotalCount(payload.Data.TotalCount);
        }
    }


    updateProgressBar(pct) {
        if (!this.progressBarId)
            return;
        if (pct < 0) {
            $(".progress").addClass("hidden");
            pct = 0;
        } else {
            $(".progress").removeClass("hidden");
        }
        $(this.progressBarId)
            .css("width", pct + "%")
            .attr("aria-valuenow", pct)
            .text(pct + "%")
    }


    registerLoaderMessage(id) {
        this.loaderMessageId = id;
    }
    registerProgressBar(id) {
        this.progressBarId = id;
    }
    setTotalCount(count) {
        $(this.diagramTotalId).text(count);
    }
    registerDiagramCountField(currentId, totalId) {
        this.diagramCountId = currentId;
        this.diagramTotalId = totalId;
    }
    registerFilterAnnotation(id, labelId) {
        var that = this;
        this.filterAnnoToggleId = id;
        this.filterAnnoToggleLabelId = labelId;
        this.uiFilter.setAnnotationToggle(id);
        $(id).click(function(e) {
            that.uiFilter.toggleAnnotationFilter(this.checked);
        });
    }
    registerFilterFamilyGroup(pfamId, interproId) {
        var that = this;
        var toggleIndicator = function(e) {
            var obj = $(e)
                .prev(".panel-heading")
                .find(".accordion-arrow")
                .toggleClass("glyphicon-triangle-right glyphicon-triangle-bottom");
        };
        $(pfamId).on("show.bs.collapse", function() {
            that.uiFilter.setFamilySelection("pfam");
            toggleIndicator(this);
        });
        $(interproId).on("show.bs.collapse", function() {
            that.uiFilter.setFamilySelection("interpro");
            toggleIndicator(this);
        });
        $(pfamId).on("hide.bs.collapse", function() {
            toggleIndicator(this);
        });
        $(interproId).on("hide.bs.collapse", function() {
            toggleIndicator(this);
        });
    }
    registerFilterClear(id) {
        var that = this;
        $(id).click(function(e) {
            that.uiFilter.clearFilter();
        });
    }
    registerFilterControl(id) {
        var that = this;
        $(id).click(function(e) {
            that.uiFilter.changeFamilyText(this.checked);
        });
    }
    registerSearchBtn(id, inputId, startInfoId) {
        var that = this;
        $(id).click(function(e) {
            var input = $(inputId).val();
            var query = input.replace(/\n/g, " ").replace(/\r/g, " ");
            that.XT.search(query);
            $(startInfoId).hide();
            $(".initial-hidden").removeClass("initial-hidden");
        });
    }
    registerWindowUpdateBtn(id, inputId) {
        var that = this;
        $(id).click(function(e) {
            var nbSize = $(inputId).val();
            that.XT.setWindow(nbSize);
        });
    }
    registerProgressLoader(id) {
        this.progressLoader = $(id);
    }
    registerShowMoreBtn(id) {
        var that = this;
        this.showMoreBtn = $(id);
        this.showMoreBtn.click(function(e) {
            that.XT.loadNext();
        });
    }
    registerShowAllBtn(id) {
        var that = this;
        this.showAllBtn = $(id);
        this.showAllBtn.click(function(e) {
            that.XT.loadAll();
        });
    }
    registerZoom(outLarge, outSmall, inSmall, inLarge) {
        var that = this;
        var factors = [];
        this.zoomOutLarge = $(outLarge);
        this.zoomOutSmall = $(outSmall);
        this.zoomInSmall = $(inSmall);
        this.zoomInLarge = $(inLarge);
        this.zoomOutLarge.click(function(e) { that.XT.setZoom(ZOOM_FACTOR_OUT_LARGE); });
        this.zoomOutSmall.click(function(e) { that.XT.setZoom(ZOOM_FACTOR_OUT_SMALL); });
        this.zoomInSmall.click(function(e) { that.XT.setZoom(ZOOM_FACTOR_IN_SMALL); });
        this.zoomInLarge.click(function(e) { that.XT.setZoom(ZOOM_FACTOR_IN_LARGE); });
    }
}

