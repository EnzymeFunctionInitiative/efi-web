
    var iframes = $('iframe');
    iframes.each(function() {
        var src = $(this).attr('src');
        $(this).data('src', src).attr('src', '');
    });

    var handleTabPress = function(masterId, elemObj) {
        var curAttrValue = elemObj.attr("href");
        var tabPage = $(masterId + " " + curAttrValue);
        //tabPage.show().siblings().hide();
        //tabPage.fadeIn(300).show().siblings().hide();
        //elemObj.parent("li").addClass("active").siblings().removeClass("active");
        var theFrame = tabPage.children().first();
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
<?php if (!$IsExample) { ?>
    handleTabPress("#heatmap-tabs", $("#heatmap-tabs .tab-headers .active a").first());
<?php } else { ?>
//    var firstHmTab = $("#heatmap-tabs .tab-headers .active a").first();
<?php } ?>
    
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


