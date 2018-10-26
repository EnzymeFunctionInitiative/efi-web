<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/heatmap.css">
    <link rel="stylesheet" type="text/css" href="../css/buttons.css">

    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
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
<div style="clear: both" class="filter-bs-group" id="filter-bs-group">
    <b>Body Sites:</b>
</div>
<div>
    <button type="button" class="small dark" id="filter-btn">Apply Filter</button>
    <button type="button" class="small dark" id="reset-btn">Reset Filter</button>
</div>
<!--<div id="filter-hide"><button class="small light" type="button">Hide Filters</button></div>-->
</div>
<!--<div id="filter-show"><button class="small dark" type="button">Show Filters</button></div>-->

<div style="margin-top:50px;width:100%;position:fixed;bottom:0;height:50px;margin-bottom:150px">
    <i id="progress-loader" class="fas fa-sync black fa-spin fa-4x fa-fw hidden-placeholder"></i>
</div>


<script src="../js/jquery-3.2.1.min.js"></script>
<script src="../bs/js/bootstrap.min.js"></script>
<script src="js/heatmap.js"></script>

<script>
$(document).ready(function() {

    var data = {
        Id: "<?php echo $_GET["id"]; ?>",
        Key: "<?php echo $_GET["key"]; ?>",
        ResType: "<?php echo $_GET["res"]; ?>",
        QuantifyId: <?php echo(isset($_GET["quantify-id"]) ? $_GET["quantify-id"] : 0); ?>,
        RefDb: "<?php echo (isset($_GET["ref-db"]) ? $_GET["ref-db"] : ""); ?>",
        SearchType: "<?php echo (isset($_GET["search-type"]) ? $_GET["search-type"] : ""); ?>",
        FileName: "<?php echo (isset($_GET["filename"]) ? $_GET["filename"] : ""); ?>",
        DiamondSens: "<?php echo (isset($_GET["d-sens"]) ? $_GET["d-sens"] : ""); ?>",
        CdHitSid: "<?php echo (isset($_GET["cdhit-sid"]) ? $_GET["cdhit-sid"] : ""); ?>",
    };

    var app = new HeatmapApp(data, "#progress-loader");

    var bodySiteFn = function() {
        var bs = app.getBodySites();
        var group = $("#filter-bs-group");
        for (var i = 0; i < bs.length; i++) {
            var lbl = group.append('<label>' +
                '<input type="checkbox" id="filter-bs-' + i + '" value="' + bs[i] + '" class="filter-bs-item"> ' + bs[i] +
                '</label>');
            $("#filter-bs-" + i).click(function() { app.doFormPost(); });
        }
        $("#filter-box").show();
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


