
    var iframes = $('iframe');
    iframes.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });

    var handleTabPress = function(masterId, elemObj) {
        var curAttrValue = elemObj.attr("href");
        var tabPage = $(masterId + " " + curAttrValue);
        var theFrame = $(masterId + " " + curAttrValue + " iframe");
        if (!theFrame.data("shown")) {
            theFrame.attr("src", function() {
                return $(this).data("src");
            });
            theFrame.data("shown", true);
        }
    };

    $("#heatmap-tabs .tab-headers a").on("click", function(e) {
        e.preventDefault();
        handleTabPress("#heatmap-tabs", $(this));
    });
    
    $("#download-tabs .tab-headers a").on("click", function(e) {
        e.preventDefault();
        handleTabPress("#download-tabs", $(this));
    });
    handleTabPress("#download-tabs", $("#download-tabs .tab-headers .active a").first());

    $("#job-stats-btn").click(function() {
        $(".stats-row").toggle();
        if ($(this).text() == "Show Job Statistics")
            $(this).text("Hide Job Statistics");
        else
            $(this).text("Show Job Statistics");
    });
    //$(".stats-row").hide();
    $(".tabs").tabs();

    var boxplotWindow;

    $(".open-boxplots").click(function(evt) {
        //if (typeof boxplotWindow !== "undefined")
        //    return;
        var windowSize = ["width=500,height=" + (window.outerHeight-150)];
        var url = "boxplots.php?<?php echo $hm_parm_string; ?>";
        boxplotWindow = window.open(url, "BoxplotWindow", windowSize);
        evt.preventDefault();
    });

    $("#click-to-load-heatmap a").on("click", function(e) {
        e.preventDefault();
        handleTabPress("#heatmap-tabs", $("#heatmap-tabs .tab-headers .active a").first());
    });

