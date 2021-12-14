

var TaxPreselects = {
    "John": [
        ["Superkingdom", "bacteria"],
        ["Superkingdom", "archaea"],
        ["Phylum", "Ascomycota"],
        ["Phylum", "Basidiomycota"],
        ["Phylum", "Fungi incertae sedis"],
        ["Phylum", "unclassified fungi"],
        ["Species", "metagenome"],
    ],
};


function add_tax_condition(opt, defaultSelected = "", defaultSearch = "") {
    var typeSelect = $('<select class="tax-select bigger m-5"></select>');
    var cats = ["Superkingdom", "Kingdom", "Phylum", "Class", "Order", "Family", "Genus", "Species"];
    for (var i = 0; i < cats.length; i++) {
        var selected = cats[i] == defaultSelected ? " selected" : "";
        typeSelect.append('<option' + selected + '>' + cats[i] + '</option>');
    }
    var div1 = $('<div style="display: inline-block"></div>').append(typeSelect);

    var searchText = "";
    if (defaultSearch)
        searchText = ' value="' + defaultSearch + '"';
    var valueInput = $('<input class="tax-search small" type="text" ' + searchText + '/>');
    var div2 = $('<div style="display: inline-block"></div>').append(valueInput);

    var mainDiv = $('<div class="tax-group" style=""></div>').append(div1).append(div2);

    var div3 = $('<div style="display: inline-block; cursor: pointer"><i class="fas fa-trash m-5"></i></div>');
    div3.click(function() {
        mainDiv.remove();
    });

    mainDiv.append(div3);

    var containerId = '#taxonomy-' + opt + '-container';
    $(containerId).append(mainDiv);
}


function add_tax_preselect(opt, preselectName) {
    if (!(preselectName in TaxPreselects))
        return;
    var containerId = '#taxonomy-' + opt + '-container';
    $(containerId).empty();
    var preselect = TaxPreselects[preselectName];
    for (var i = 0; i < preselect.length; i++) {
        add_tax_condition(opt, preselect[i][0], preselect[i][1]);
    }
}


