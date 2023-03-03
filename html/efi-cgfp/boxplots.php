<?php
require_once(__DIR__."/../../init.php");

use \efi\global_functions;
use \efi\training\example_config;
use \efi\sanitize;

$is_example = example_config::is_example();
$is_static_example = sanitize::get_sanitize_string("example");

$filename = "";
if (isset($_GET["filename"])) {
    $filename = pathinfo(global_functions::safe_filename($_GET["filename"]), PATHINFO_FILENAME);
}

$id = sanitize::validate_id("id", sanitize::GET);
$key = sanitize::validate_key("key", sanitize::GET);
$qid = sanitize::validate_id("quantify-id", sanitize::GET);

if ($is_static_example) {
    $is_example = "false";
} else if (!$is_example) {
    $is_example = "false";
} else if ($is_example !== false) {
    $is_example = '"' . $is_example . '"';
}

$res_type = sanitize::get_sanitize_string("res", "");
$ref_db = sanitize::get_sanitize_string("ref-db", "");
$search_type = sanitize::get_sanitize_string("search-type", "");
$diamond_sens = sanitize::get_sanitize_string("d-senst", "");
$cdhit_sid = sanitize::get_sanitize_string("cdhit-sid", "");

if (!$is_static_example && ($id === false || $key === false)) {
    error_404();
    exit;
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
    <script src="../vendor/plotly/plotly.js/dist/plotly.min.js"></script>
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
<?php if ($is_static_example) { ?>
        StaticExample: true,
        Id: 0,
        Key: 0,
        QuantifyId: 0,
<?php } else { ?>
        StaticExample: false,
        DynamicExample: <?php echo $is_example; ?>,
        Id: "<?php echo $id; ?>",
        Key: "<?php echo $key; ?>",
        QuantifyId: <?php echo $qid; ?>,
<?php } ?>
        ResType: "<?php echo $res_type; ?>",
        RefDb: "<?php echo $ref_db; ?>",
        SearchType: "<?php echo $search_type; ?>",
        FileName: "<?php echo $filename; ?>",
        DiamondSens: "<?php echo $diamond_sens; ?>",
        CdHitSid: "<?php echo $cdhit_sid; ?>",
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


