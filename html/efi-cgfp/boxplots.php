<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__CGFP_DIR__ . "/includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/global_settings.class.inc.php");
require_once(__BASE_DIR__ . "/libs/global_functions.class.inc.php");

$filename = "";
if (isset($_GET["filename"])) {
    $filename = pathinfo(global_functions::safe_filename($_GET["filename"]), PATHINFO_FILENAME);
}
    
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="../vendor/fortawesome/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/heatmap.css?v=2">
    <link rel="stylesheet" type="text/css" href="css/boxplot.css?v=2">
    <link rel="stylesheet" type="text/css" href="../css/buttons.css?v=2">
    <link rel="stylesheet" type="text/css" href="../css/form.css?v=2">

    <!--<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>-->
    <script src="../vendor/plotly/plotly.js/plotly.min.js"></script>
    <script src="../vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
    <script src="js/heatmap.js?v=6"></script>

    <title>Boxplots for <?php echo $filename; ?></title>
</head>
<body>

<div id="search-container" class="search-container fill">
    <div>
        <div class="title">
            Submitted SSN: <b><?php echo $filename; ?></b>
        </div>
        <div>
            <div class="search-help bigger">
                Boxplots showing per-site abundance (using the median method)
                can be generated for clusters and/or singletons 
                in the SSN.
            </div>
            <input type="text" name="cluster-input" id="cluster-input" class="bigger" />
            <button type="button" id="add-btn" class="normal">Add Clusters</button><br>
            Abundances are computed using the median method.
        </div>
        <div class="search-help">
            Enter a comma-separated list or numeric range of cluster and/or singleton numbers
            (e.g. <i>1, 2, 3-9, 15, S1, S100-S500</i> would be an acceptable input).
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
        ResType: "<?php echo isset($_GET["res"]) ? $_GET["res"] : ""; ?>",
        RefDb: "<?php echo (isset($_GET["ref-db"]) ? $_GET["ref-db"] : ""); ?>",
        SearchType: "<?php echo (isset($_GET["search-type"]) ? $_GET["search-type"] : ""); ?>",
        FileName: "<?php echo $filename; ?>",
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


