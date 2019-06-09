

const DISPLAY_NAME = 100;
const DISPLAY_ID = 101;


class GndFilterUi {
    constructor(gndRouter, gndFilter, gndColor, filterContainerId, legendContainerId) {
        this.filterContainer = $(filterContainerId);
        this.legendContainer = $(legendContainerId);
        this.filter = gndFilter;
        this.color = gndColor;
        this.families = [];
        this.nameMap = {};
        this.annoToggleId = "";
        this.legendList = {};

        this.displayMode = DISPLAY_NAME;

        gndRouter.addListener(this);
    }

    
    onMessage(payload) {
        if (payload.MessageType == "DataRetrievalStatus") {
            if (!payload.Data.Retrieving) {
                this.addFamiliesToLegend();
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
        this.addFamiliesToLegend();
    }


    addFamiliesToLegend(fams) {
        this.filterContainer.empty();
        this.legendContainer.empty();
        this.legendList = {};

        var showFamsById = this.displayMode == DISPLAY_ID;
        this.families = this.filter.getFamilies(showFamsById);

        var that = this;

        $.each(this.families, function(i, fam) {
            var famId = fam.Id;
            var famName = fam.Name;
            var famText = that.displayMode == DISPLAY_ID ? famId : famName;
            var tooltipText = that.displayMode == DISPLAY_ID ? famName : famId;
            var isChecked = fam.Selected ? "checked" : "";
            that.nameMap[fam.Id] = [i, famName];
    
            var entry = $("<div class='filter-cb-div' title='" + tooltipText + "'></div>").appendTo(that.filterContainer);
            $("<input id='filter-cb-" + i + "' class='filter-cb' type='checkbox' value='" + i + "' " + isChecked + "/>")
                .appendTo(entry)
                .click(function(e) {
                    $(that.annoToggleId).prop("checked", false);
                    if (this.checked) {
                        that.filter.addFamilyFilter(famId);
                        that.addSelectedLegendItem(i, famId, famName, famText);
                    } else {
                        that.filter.removeFamilyFilter(famId);
                        $("#legend-" + i).remove();
                    }
                });
            $("<span id='filter-cb-text-" + i + "' class='filter-cb-number'><label for='filter-cb-" + i + "'>" + famText + " <span class='filter-small'>(" + tooltipText + ")</span></label></span>") .
                appendTo(entry);
            
            if (fam.Selected)
                that.addSelectedLegendItem(i, famId, famName, famText);
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
        this.legendList.empty();
        this.filter.clearFilters();
        $(this.annoToggleId).prop("checked", false);
    }
}

