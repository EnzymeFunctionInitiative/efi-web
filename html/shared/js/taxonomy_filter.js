
function setupTaxonomyUi(taxonomyApp) {
    $("button.add-tax-btn").click(function() {
        var optionId = $(this).data("option-id");
        taxonomyApp.addTaxCondition(optionId);
    });
    $(".taxonomy-preselects").change(function() {
        var opt = $(this).data("taxoption");
        var name = $(this).val();
        taxonomyApp.addTaxPreselectConditions(opt, name);
    });
    var johnPreselect = [
            ["Superkingdom", "bacteria"],
            ["Superkingdom", "archaea"],
            ["Phylum", "Ascomycota"],
            ["Phylum", "Basidiomycota"],
            ["Phylum", "Fungi incertae sedis"],
            ["Phylum", "unclassified fungi"],
            ["Species", "metagenome"],
        ];
    taxonomyApp.addTaxPreselectSet("Bacteria, Archaea, Fungi", johnPreselect, ".taxonomy-preselects");
}


