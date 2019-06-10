

const FILTER_PFAM = 1;
const FILTER_INTERPRO = 2;
const FILTER_SWISSPROT = 4;


class GndFilter {
    constructor(gndRouter, gndDb) {
        this.gndRouter = gndRouter;
        this.db = gndDb;

        this.filterMode = FILTER_PFAM;
        this.saveFilterMode = FILTER_PFAM;

        this.familyMap = {};
        this.familyNames = {};
        this.swissProtMap = {}; // arrows with SwissProt annotation

        this.currentFamilies = {}; // families currently highlighted
        
        this.pfamList = {};
        this.interproList = {};
    }


    // Public
    clearFilters() {
        this.currentFamilies = {};
        this.filterMode = this.saveFilterMode = FILTER_PFAM;
        $(".an-arrow").removeClass("an-arrow-mute").removeClass("an-arrow-selected").removeClass("an-arrow-bold");
    }
    // Private
    checkForClearingFilter() {
        if (Object.keys(this.currentFamilies).length == 0 && !(this.filterMode & FILTER_SWISSPROT))
            this.clearFilters();
    }

    reset() {
        this.familyMap = {};
        this.familyNames = {};
        this.currentFamilies = {}; // families currently highlighted
        this.pfamList = {};
        this.interproList = {};
        this.swissProtMap = {};
    }


    ////////////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC SET WHICH ATTRIBUTE TO FILTER/HIGHLIGHT BY
    //
    setFilterByPfam() {
        this.filterMode = FILTER_PFAM;
    }
    setFilterByInterPro() {
        this.filterMode = FILTER_INTERPRO;
    }
    setFilterBySwissProt(show) {
        if (show) {
            this.muteAll();
            this.saveFilterMode = this.filterMode;
            this.filterMode = FILTER_SWISSPROT;
        }

        for (var arrowId in this.swissProtMap) {
            this.updateArrowClass(this.swissProtMap[arrowId], show);
        }

        if (!show) {
            this.filterMode = this.saveFilterMode;
            this.checkForClearingFilter();
            if (Object.keys(this.currentFamilies).length > 0)
                this.muteAll();
            for (var family in this.currentFamilies) {
                this.updateClasses(this.familyMap[family], true);
            }
        }
        // This code is for when we add functionality to show SwissProt and highlight by family at the same time.
        //if (show)
        //    this.filterMode = this.filterMode | FILTER_SWISSPROT;
        //else
        //    this.filterMode = this.filterMode ^ FILTER_SWISSPROT;
        //this.setArrowFilterBySwissProt();
    }


    ////////////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC INITIALIZE ARROW
    //

    // Public
    // Adds an SVG arrow object to the filter mappings. This should be called
    // for every arrow in each diagram that is retrieved.
    // The arrow parameter is a jQuery/SnapSVG object.
    // The data parameter is a GndDrawableArrow
    // family-specific colors to the arrow.
    addArrow(arrowGroup, data) {
        var updateFams = [];
        for (var i = 0; i < data.Pfam.length; i++) {
            var fam = data.Pfam[i];
            if (this.addArrowHelper(fam, data.PfamDesc[i], this.pfamList, arrowGroup))
                updateFams.push(fam);
        }
        for (var i = 0; i < data.InterPro.length; i++) {
            var fam = data.InterPro[i];
            if (this.addArrowHelper(fam, data.InterProDesc[i], this.interproList, arrowGroup))
                updateFams.push(fam);
        }

        if (data.IsSwissProt)
            this.swissProtMap[data.Id] = arrowGroup;

        if (updateFams.length > 0 || (data.IsSwissProt && this.filterMode & FILTER_SWISSPROT))
            this.updateArrowClass(arrowGroup, true);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC FILTER ADD/REMOVE
    //
    addFamilyFilter(family) {
        // If this is the first time we add a filter, then we mute all the arrows.
//        this.filterMode = FILTER_PFAM;
        if (Object.keys(this.currentFamilies).length == 0)
            this.muteAll();
        this.updateCurrentFamilies(family, true);
        this.updateClasses(this.familyMap[family], true);
    }
    removeFamilyFilter(family) {
//        this.filterMode = FILTER_PFAM;
        this.updateCurrentFamilies(family, false);
        this.updateClasses(this.familyMap[family], false);
        this.checkForClearingFilter();
    }
    // Used when the user clicks on the diagram to toggle the filter.
    // Currently only highlights related Pfams, not InterPros.
    toggleFamilyFilterByArrowId(arrowId, classList) {
        var isActive = classList.includes("an-arrow-selected");
        var data = this.db.getData(arrowId);

        var addFilter = !isActive;

        var list = [];
        if (this.filterMode == FILTER_PFAM)
            this.addFamilyListFilter(data.Pfam, addFilter, list);
        else if (this.filterMode == FILTER_INTERPRO)
            this.addFamilyListFilter(data.InterPro, addFilter, list);

        // Send a message to the user interface to check/uncheck the corresponding family boxes.
        var msg = new Payload(); msg.MessageType = "FilterUpdate"; msg.Data = {Family: list, IsActive: isActive};
        this.gndRouter.sendMessage(msg);
    }


    ////////////////////////////////////////////////////////////////////////////////////////////
    // FAMILY METHODS
    //
    getFamilies(sortById = true) { // All families, both types
        return this.getFamiliesFromMap(this.familyMap, this.familyNames, sortById);
    }
    getPfamFamilies(sortById = true) {
        var fams = this.getFamiliesFromMap(this.pfamList, this.familyNames, sortById);
        return fams;
    }
    getInterProFamilies(sortById = true) {
        var fams = this.getFamiliesFromMap(this.interproList, this.familyNames, sortById);
        return fams;
    }
    //Private
    getFamiliesFromMap(listMap, nameList, sortById) {
        var list = Object.keys(listMap);
        var fams = [];
        for (var i = 0; i < list.length; i++) {
            var famId = list[i];
            var famName = "";
            if (famId == "none")
                famName = "None";
            else
                famName = nameList[famId];

            var isSelected = famId in this.currentFamilies;
            fams.push({Id: famId, Name: famName, Selected: isSelected});
        }

        if (sortById)
            fams.sort(function(a, b) { return a.Id.localeCompare(b.Id); });
        else
            fams.sort(function(a, b) { return a.Name.localeCompare(b.Name); });

        return fams;
    }
    hasSwissProt() {
        return Object.keys(this.swissProtMap).length > 0;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////
    // PRIVATE METHODS
    //

    // These methods only update the data structures.
    // View is updated later.
    updateCurrentFamilies(family, addFilter) {
        if (addFilter)
            this.currentFamilies[family] = 1;
        else if (family in this.currentFamilies)
            delete this.currentFamilies[family];
    }
    addFamilyListFilter(families, addFilter, list) {
        for (var i = 0; i < families.length; i++) {
            var family = families[i];
            if (addFilter)
                this.addFamilyFilter(family);
            else
                this.removeFamilyFilter(family);
            list.push(family);
        }
    }


    // Updates the view for a list of arrows.
    updateClasses(arrowGroups, addFilter) {
        for (var ai = 0; ai < arrowGroups.length; ai++) {
            this.updateArrowClass(arrowGroups[ai], addFilter);
        }
    }
    // Updates the view for an individual arrow.
    updateArrowClass(arrowGroup, addFilter) {
        var removeClass = addFilter ? "an-arrow-mute" : "an-arrow-selected";
        var addClass = addFilter ? "an-arrow-selected" : "an-arrow-mute";
        arrowGroup.arrow.removeClass(removeClass);
        arrowGroup.arrow.addClass(addClass);
        for (var si = 0; si < arrowGroup.subArrows.length; si++) {
            arrowGroup.subArrows[si].removeClass(removeClass);
            arrowGroup.subArrows[si].addClass(addClass);
        }
    }
    muteAll() {
        $(".an-arrow").removeClass("an-arrow-selected").addClass("an-arrow-mute");
    }


    // Private helper
    addArrowHelper(fam, famName, secondaryMap, arrowGroup) {
        if (!(fam in this.familyMap)) {
            this.familyMap[fam] = [];
            this.familyNames[fam] = famName;
        }
        this.familyMap[fam].push(arrowGroup);
        if (!(fam in secondaryMap))
            secondaryMap[fam] = [];
        secondaryMap[fam].push(arrowGroup);

        if (fam in this.currentFamilies) {
            return true;
        } else {
            return false;
        }
    }
}

