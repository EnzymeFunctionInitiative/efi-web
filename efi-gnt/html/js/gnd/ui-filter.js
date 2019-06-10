

const DISPLAY_NAME = 100;
const DISPLAY_ID = 101;


class GndFilterUi {
    constructor(gndRouter, gndFilter, gndColor, pfamFilterContainerId, interproFilterContainerId, legendContainerId) {
        this.pfamFilterContainer = $(pfamFilterContainerId);
        this.interproFilterContainer = $(interproFilterContainerId);
        this.legendContainer = $(legendContainerId);
        this.filter = gndFilter;
        this.color = gndColor;
        this.nameMap = {};
        this.annoToggleId = "";
        this.legendList = {};

        this.displayMode = DISPLAY_NAME;

        gndRouter.addListener(this);
    }

    
    onMessage(payload) {
        if (payload.MessageType == "DataRetrievalStatus") {
            if (!payload.Data.Retrieving) {
                this.addAllFamiliesToLegend();
            }
        } else if (payload.MessageType == "FilterUpdate") {
            for (var i = 0; i < payload.Data.Family.length; i++) {
                var famId = payload.Data.Family[i];
                if (famId in this.nameMap) {
                    var data = this.nameMap[famId];
                    var text = this.displayMode == DISPLAY_ID ? famId : data[1];
                    this.addSelectedLegendItem(data[0], famId, data[1], text);
                }
            }
        }
    }


    toggleAnnotationFilter(checked) {
        this.filter.setFilterBySwissProt(checked);
    }


    // valid input: pfam, interpro
    setFamilySelection(familyType) {
        if (familyType == "pfam") {
            this.filter.setFilterByPfam();
        } else if (familyType == "interpro") {
            this.filter.setFilterByInterPro();
        }
    }


    setAnnotationToggle(id) {
        this.annoToggleId = id;
    }


    getFilterHasSwissProt() {
        return this.filter.hasSwissProt();
    }


    changeFamilyText(checked) {
        if (checked)
            this.displayMode = DISPLAY_ID;
        else
            this.displayMode = DISPLAY_NAME;
        this.addAllFamiliesToLegend();
    }


    addAllFamiliesToLegend() {
        this.legendList = {};
        this.legendContainer.empty();
        this.addFamiliesToLegend(this.pfamFilterContainer, FILTER_PFAM);
        this.addFamiliesToLegend(this.interproFilterContainer, FILTER_INTERPRO);
    }
    addFamiliesToLegend(filterContainer, filterMode) {
        filterContainer.empty();

        var showFamsById = this.displayMode == DISPLAY_ID;
        var families = [];
        if (filterMode == FILTER_PFAM)
            families = this.filter.getPfamFamilies(showFamsById);
        else if (filterMode == FILTER_INTERPRO)
            families = this.filter.getInterProFamilies(showFamsById);

        var that = this;

        $.each(families, function(i, fam) {
            var legendIndex = filterMode + "-" + i;
            var famId = fam.Id;
            var famName = fam.Name;
            var famText = that.displayMode == DISPLAY_ID ? famId : famName;
            var tooltipText = that.displayMode == DISPLAY_ID ? famName : famId;
            var isChecked = fam.Selected ? "checked" : "";
            that.nameMap[fam.Id] = [legendIndex, famName];
    
            var entry = $("<div class='filter-cb-div' title='" + tooltipText + "'></div>").appendTo(filterContainer);
            $("<input id='filter-cb-" + legendIndex + "' class='filter-cb' type='checkbox' value='" + legendIndex + "' " + isChecked + "/>")
                .appendTo(entry)
                .click(function(e) {
                    $(that.annoToggleId).prop("checked", false);
                    if (this.checked) {
                        that.filter.addFamilyFilter(famId);
                        that.addSelectedLegendItem(legendIndex, famId, famName, famText);
                    } else {
                        that.filter.removeFamilyFilter(famId);
                        $("#legend-" + legendIndex).remove();
                    }
                });
            $("<span id='filter-cb-text-" + legendIndex + "' class='filter-cb-number'><label for='filter-cb-" + legendIndex + "'>" + famText + " <span class='filter-small'>(" + tooltipText + ")</span></label></span>") .
                appendTo(entry);
            
            if (fam.Selected)
                that.addSelectedLegendItem(legendIndex, famId, famName, famText);
        });
    
        $('.filter-cb-div').tooltip({delay: {show: 50}, placement: 'top', trigger: 'hover'});
    }


    addSelectedLegendItem(index, id, famName, displayText) {
        if (!(index in this.legendList)) {
            var color = this.color.getPfamColor(id);
            var activeFilter = $("<div id='legend-" + index + "' title='" + famName + "'>" +
                "<span class='active-filter-icon' style='background-color:" + color + "'> </span> " + displayText + "</div>")
                .appendTo(this.legendContainer);
            this.legendList[index] = id;
        } else {
            $("#legend-" + index).remove();
            delete this.legendList[index];
        }
    }


    clearFilter() {
        $("input.filter-cb:checked").prop("checked", false);
        this.legendContainer.empty();
        this.legendList = {};
        this.filter.clearFilters();
        $(this.annoToggleId).prop("checked", false);
    }
}

