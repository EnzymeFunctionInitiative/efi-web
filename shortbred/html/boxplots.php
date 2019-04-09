<?php
include(__DIR__ . "/../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/heatmap.css">
    <link rel="stylesheet" type="text/css" href="css/boxplot.css">
    <link rel="stylesheet" type="text/css" href="../css/buttons.css">
    <link rel="stylesheet" type="text/css" href="../css/form.css">

    <!--<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>-->
    <script src="js/plotly-1.42.5.min.js"></script>
    <script src="../js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="js/heatmap.js"></script>
</head>
<body>

<div id="search-container" class="search-container fill">
    <div>
        <div>
        <div class="search-help bigger">
            Boxplots showing per-site abundance can be generated for the clusters that are contained
            in the SSN.
        </div>
           <input type="text" name="cluster-input" id="cluster-input" class="bigger" />
           <button type="button" id="add-btn" class="normal">Add Clusters</button>
       </div>
        <div class="search-help">
            Enter a comma-separated list or numeric range of cluster numbers
            (e.g. <i>1, 2, 3-9, 15</i>).
        </div>
    </div>
</div>
<div id="support-error" style="display: none;">
    <h2>Your browser is not supported for generating CGFP boxplots.  Please use Google Chrome,
        Mozilla Firefox, or Apple Safari to access this tool.</h2>
</div>

<div id="master-plot-container" style="display: none;">
</div>

<div style="margin-top:50px;width:100%;position:fixed;bottom:0;height:50px;margin-bottom:150px">
    <i id="progress-loader" class="fas fa-sync black fa-spin fa-4x fa-fw hidden"></i>
</div>


<script>
$(document).ready(function() {
    if (navigator.userAgent.indexOf("MSIE") >= 0) {
        $("#support-error").show();
        $("#search-container").hide();
    }
    
    var params = {
<?php if (isset($_GET["example"])) { ?>
        Example: true,
        Id: 0,
        Key: 0,
        QuantifyId: 0,
<?php } else { ?>
        Example: false,
        Id: "<?php echo $_GET["id"]; ?>",
        Key: "<?php echo $_GET["key"]; ?>",
        QuantifyId: <?php echo(isset($_GET["quantify-id"]) ? $_GET["quantify-id"] : 0); ?>,
<?php } ?>
        ResType: "<?php echo isset($_GET["res"]) ? $_GET["res"] : ""; ?>",
        RefDb: "<?php echo (isset($_GET["ref-db"]) ? $_GET["ref-db"] : ""); ?>",
        SearchType: "<?php echo (isset($_GET["search-type"]) ? $_GET["search-type"] : ""); ?>",
        FileName: "<?php echo (isset($_GET["filename"]) ? $_GET["filename"] : ""); ?>",
        DiamondSens: "<?php echo (isset($_GET["d-sens"]) ? $_GET["d-sens"] : ""); ?>",
        CdHitSid: "<?php echo (isset($_GET["cdhit-sid"]) ? $_GET["cdhit-sid"] : ""); ?>",
    };


    var app = new BoxplotApp(params, "#progress-loader");

    var searchFn = function() {
        $("#search-container").removeClass("fill");
        var clusterQuery = $("#cluster-input").val();
        $("#master-plot-container").show();
        $(".search-help").hide();
        app.search(clusterQuery);
    };

    $("#add-btn").click(searchFn);

    $("#cluster-input").keypress(function(evt) {
        if (evt.which == 13) {
            evt.preventDefault();
            searchFn();
        }
    });
});
</script>


</body>
</html>


