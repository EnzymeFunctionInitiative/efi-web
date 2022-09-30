
function setupTaxonomyUi(taxonomyApp) {
    var addTaxCatBtn = function() {
        var optionId = $(this).data("option-id");
        var firstSel = taxonomyApp.getTaxonomyCategories()[0];
        taxonomyApp.addTaxCondition(optionId, firstSel);
    };
    var clearTaxBtn = function() {
        var optionId = $(this).data("option-id");
        var theId = "#taxonomy-" + optionId + "-container";
        $(theId).empty();
        var sel = $("#taxonomy-" + optionId + "-select").get(0);
        sel.selectedIndex = 0;
        $("#taxonomy-" + optionId + "-add-btn").off("click").click(addTaxCatBtn);
        $("#taxonomy-" + optionId + "-add-btn").text("Add taxonomic condition");
        $("#taxonomy-" + optionId + "-preset-name").val("");
    };

    $("button.add-tax-btn").click(addTaxCatBtn);
    //$("button.taxonomy-clear-preselect").click(clearTaxBtn);

    $(".taxonomy-preselects").change(function() {
        var optionId = $(this).data("option-id");
        var name = $(this).children("option:selected").text();
        var nameId = $(this).children("option:selected").val();
        taxonomyApp.addTaxPreselectConditions(optionId, name);
        var optIdStr = optionId ? optionId + "-" : "";
        $("#taxonomy-" + optIdStr + "add-btn").off("click").click(clearTaxBtn);
        $("#taxonomy-" + optIdStr + "add-btn").text("Reset");
        $("#taxonomy-" + optIdStr + "preset-name").val(nameId + "|" + name);
    });

    var bafPreselect = [
            ["Superkingdom", "bacteria"],
            ["Superkingdom", "archaea"],
            ["Phylum", "Ascomycota"],
            ["Phylum", "Basidiomycota"],
            ["Phylum", "Fungi incertae sedis"],
            ["Phylum", "unclassified fungi"],
            ["Species", "metagenome"],
        ];
    taxonomyApp.addTaxPreselectSet("Bacteria, Archaea, Fungi", "bacteria_fungi", bafPreselect, ".taxonomy-preselects");

    var eukPreselect = [
            ["Superkingdom", "eukaryota"],
            ["Phylum", "-Ascomycota"],
            ["Phylum", "-Basidiomycota"],
            ["Phylum", "-Fungi incertae sedis"],
            ["Phylum", "-unclassified fungi"],
            ["Species", "-metagenome"],
        ];
    taxonomyApp.addTaxPreselectSet("Eukaryota, no Fungi", "eukaroyta_no_fungi", eukPreselect, ".taxonomy-preselects");

    //var eukPreselect = [
    //        ["Superkingdom", "viruses"],
    //    ];
    //taxonomyApp.addTaxPreselectSet("Viruses", "viruses", eukPreselect, ".taxonomy-preselects");

    var fungiPreselect = [
            ["Phylum", "Ascomycota"],
            ["Phylum", "Basidiomycota"],
            ["Phylum", "Fungi incertae sedis"],
            ["Phylum", "unclassified fungi"],
        ];
    taxonomyApp.addTaxPreselectSet("Fungi", "fungi", fungiPreselect, ".taxonomy-preselects");
}


