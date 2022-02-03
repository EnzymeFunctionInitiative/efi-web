

function setupArchiveUi() {
    $(".archive-btn").click(function() {
        var id = $(this).data("id");
        var key = $(this).data("key");
        var aid = $(this).data("analysis-id");
        var requestType = "archive";
        var jobType = "generate";
        var trElem = $(this).parent().parent();

        var elemList = [trElem];
        if (!aid) {
            aid = 0;
            var idQuery = `[data-parent-id='${id}']`;
            var kids = $(".archive-btn"+idQuery);
            var aids = [];
            for (kid of kids) {
                var jKid = $(kid);
                if (jKid.data("analysis-id"))
                    aids.push(jKid.data("analysis-id"));
                elemList.push(jKid.parent().parent());
            }
            for (kidAid of aids) {
                idQuery = `[data-parent-aid='${kidAid}']`;
                kids = $(".archive-btn"+idQuery);
                for (kid of kids) {
                    var jKid = $(kid);
                    elemList.push(jKid.parent().parent());
                    var jKidId = jKid.data("id");
                    var cKidQuery = `[data-parent-id='${jKidId}']`;
                    var cKids = $(".archive-btn"+cKidQuery);
                    for (ckid of cKids) {
                        var cKid = $(ckid);
                        elemList.push(cKid.parent().parent());
                    }
                }
            }
        } else {
            var idQuery = `[data-parent-aid='${aid}']`;
            var kids = $(".archive-btn"+idQuery);
            for (kid of kids) {
                var jKid = $(kid);
                elemList.push(jKid.parent().parent());
                var jKidId = jKid.data("id");
                var cKidQuery = `[data-parent-id='${jKidId}']`;
                var cKids = $(".archive-btn"+cKidQuery);
                for (ckid of cKids) {
                    var cKid = $(ckid);
                    elemList.push(cKid.parent().parent());
                }
            }
        }

        var elementHideFn = function() {
            elemList.map(x => x.hide());
        };

        $("#archive-confirm").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Archive Job": function() {
                    requestJobUpdate(id, aid, key, requestType, jobType, elementHideFn);
                    $( this ).dialog("close");
                },
                Cancel: function() {
                    $( this ).dialog("close");
                }
            }
        });
    });
}


function setupSortUi() {
    var updateSortIcon = function() {
        var sortIcon = sortMethod == SORT_DATE_DESC ? "<i class='fas fa-chevron-down'></i>" : "<i class='fas fa-list-alt'></i>";
        $("#sort-jobs-toggle").html(sortIcon);
    };
    var toggleSortIcon = function() {
        sortMethod = sortMethod == SORT_DATE_DESC ? SORT_DATE_GROUP : SORT_DATE_DESC;
        updateSortIcon();
    };
    updateSortIcon();
    $("#sort-jobs-toggle").click(function() {
        toggleSortIcon();
        window.location.replace("<?php echo $_SERVER['PHP_SELF']; ?>" + (sortMethod == SORT_DATE_DESC ? "?sb=1" : ""));
    });
}


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


