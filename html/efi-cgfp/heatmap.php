<?php
require_once(__DIR__."/../../init.php");

use \efi\global_settings;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/heatmap.css?v=2">
    <link rel="stylesheet" type="text/css" href="../css/buttons.css?v=2">

    <!--<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>-->
    <script src="js/plotly-1.42.5.min.js"></script>
</head>
<body>

<div id="plot"></div>

<div id="filter-box" style="display: none">
<div style="float: left">Show specific clusters: <input type="text" class="form-input" id="cluster-filter" /></div>
<div style="float: left; margin-left: 25px">
    Abundance to display: <input type="text" class="form-input min-max min-max-default" id="min" value="min" /> to
    <input type="text" class="form-input min-max min-max-default" id="max" value="max" />
</div>
<div style="float: left; margin-left: 25px">
    <input type="checkbox" name="mean-cb" value="1" class="form-cb" id="mean-cb"><label for="mean-cb">Use mean</label>
    <input type="checkbox" name="hits-only-cb" value="1" class="form-cb" id="hits-only-cb"><label for="hits-only-cb">Display hits only</label>
</div>
<div style="clear: both" id="filter-bs-group">
    <b>Metagenome Type:</b>
</div>
<div id="filter-cat-group" style="display:none">
    <b>Group By:</b>
</div>
<div>
    <button type="button" class="small dark" id="filter-btn">Apply Filter</button>
    <button type="button" class="small dark" id="reset-btn">Reset Filter</button>
</div>
<!--<div id="filter-hide"><button class="small light" type="button">Hide Filters</button></div>-->
</div>
<!--<div id="filter-show"><button class="small dark" type="button">Show Filters</button></div>-->

<div style="margin-top:50px;width:100%;position:fixed;bottom:0;height:50px;margin-bottom:150px">
    <i id="progress-loader" class="fas fa-sync black fa-spin fa-4x fa-fw hidden"></i>
</div>

<div id="boxplot" title="Box Plot" class="hidden">
<div id="boxplot-plot"></div>
</div>


<script src="../vendor/components/jquery/jquery.min.js"></script>
<script src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script src="../vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="js/heatmap.js?v=6"></script>

<script>
$(document).ready(function() {

    var width = <?php echo (isset($_GET["w"]) && is_numeric($_GET["w"])) ? $_GET["w"] : 950; ?>;
    width = width > 1000 ? 1000 : width < 600 ? 600 : width;

    var data = {
        Width: width,
<?php if (isset($_GET["example"])) { ?>
        StaticExample: true,
        Id: 0,
        Key: 0,
        QuantifyId: 0,
<?php } else { ?>
        StaticExample: false,
        DynamicExample: <?php echo (isset($_GET["x"]) ? "true" : "false"); ?>,
        Id: "<?php echo $_GET["id"]; ?>",
        Key: "<?php echo $_GET["key"]; ?>",
        QuantifyId: <?php echo(isset($_GET["quantify-id"]) ? $_GET["quantify-id"] : 0); ?>,
<?php } ?>
        ResType: "<?php echo $_GET["res"]; ?>",
        RefDb: "<?php echo (isset($_GET["ref-db"]) ? $_GET["ref-db"] : ""); ?>",
        SearchType: "<?php echo (isset($_GET["search-type"]) ? $_GET["search-type"] : ""); ?>",
        FileName: "<?php echo (isset($_GET["filename"]) ? $_GET["filename"] : ""); ?>",
        DiamondSens: "<?php echo (isset($_GET["d-sens"]) ? $_GET["d-sens"] : ""); ?>",
        CdHitSid: "<?php echo (isset($_GET["cdhit-sid"]) ? $_GET["cdhit-sid"] : ""); ?>",
    };

    var useBoxplots = false; //<?php echo (global_settings::advanced_options_enabled() ? "true" : "false"); ?>;

    var app = new HeatmapApp(data, "#progress-loader", useBoxplots);

    var bodySiteFn = function() {
        var bs = app.getBodySites();
        var group = $("#filter-bs-group");
        for (var i = 0; i < bs.length; i++) {
            group.append('<label>' +
                '<input type="checkbox" id="filter-bs-' + i + '" value="' + bs[i] + '" class="filter-bs-item"> ' + bs[i] +
                '</label>');
            $("#filter-bs-" + i).click(function() { app.doFormPost(); });
        }
        $("#filter-box").show();
        var cats = app.getCategories();
        if (typeof cats !== "undefined") {
            var group = $("#filter-cat-group");
            for (var i = 0; i < cats.length; i++) {
                var checked = i == 0 ? "checked" : "";
                group.append('<label>' +
                    '<input type="radio" name="category" id="filter-cat-' + i + '" value="' + cats[i] + '" ' + checked + ' class="filter-cat-item"> ' + cats[i] +
                    '</label>');
                $("#filter-cat-" + i).click(function() { app.doFormPost(); });
            }
            if (cats.length > 0)
                $("#filter-cat-group").show();
        }
    };

    app.doFormPost(bodySiteFn);

    $("body").keyup(function(e) {
        if (e.which == 27) {
            $("#filter-box").fadeOut();
        }
    });
    $(".form-input").keyup(function(e) {
        if (e.which == 13) { // Enter key
            app.doFormPost();
            e.preventDefault();
        }
    });
    $(".form-cb").click(function(e) {
        app.doFormPost();
    });
    $("#filter-btn").click(function() {
        app.doFormPost();
    });
    $("#reset-btn").click(function() {
        $("#cluster-filter").val("");
        $("#min").val("min").addClass("min-max-default");
        $("#max").val("max").addClass("min-max-default");
        $(".form-cb").prop("checked", false);
        $(".filter-bs-item").prop("checked", false);
        app.resetFilter();
    });

    $(".min-max").focus(function() {
        if (this.value == "min" || this.value == "max")
            this.value = "";
        $(this).removeClass("min-max-default");
    }).focusout(function() {
        if (this.value == "") {
            this.value = this.id == "min" ? "min" : "max";
            $(this).addClass("min-max-default");
        } else {
        }
    });

    /*
    $("#filter-show").click(function() {
        $("#filter-box").fadeIn();
    });

    $("#filter-hide").click(function() {
        $("#filter-box").fadeOut();
    });
     */
});
</script>

</body>
</html>


