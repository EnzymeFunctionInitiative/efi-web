<?php
require_once(__DIR__."/../../../init.php");

use \efi\cgfp\functions;


$month = date('n');
if (isset($_POST['month'])) {
    $month = $_POST['month'];
}
$year = date('Y');
if (isset($_POST['year'])) {
    $year = $_POST['year'];
}

$requests = functions::get_cgfp_applications($db);

$id_graph_type = "identify_daily_jobs";
$q_graph_type = "quantify_daily_jobs";

$id_graph_array = array('graph_type' => $id_graph_type, 'month' => $month, 'year' => $year);
$identify_graph = "<img src='daily_graph.php?" . http_build_query($id_graph_array) . "' id='daily-id-graph'>";
$q_graph_array = array('graph_type' => $q_graph_type, 'month' => $month, 'year' => $year);
$quantify_graph = "<img src='daily_graph.php?" . http_build_query($q_graph_array) . "' id='daily-q-graph'>";








require_once(__DIR__."/inc/header.inc.php");

echo "<div style=\"margin-top: 70px\"></div>\n";

$month_html = "<select class='form-control month-sel' name='month'>";
for ($i=1;$i<=12;$i++) {
    if ($month == $i) {
        $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    } else {
        $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    }
}
$month_html .= "</select>";

$year_html = "<select class='form-control year-sel' name='year'>";
for ($i=2014;$i<=date('Y');$i++) {
    if ($year = $i) {
        $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>\n";
    } else {
        $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
    }

}
$year_html .= "</select>";



?>

<?php include("stats_nav.php"); ?>

<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit' name='create_user_report' value='Get Daily Graph'>

<hr>
<?php echo $identify_graph; ?>
<?php echo $quantify_graph; ?>
</form>

<script type="text/javascript" src="stats_nav.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    var graphApp = "daily_graph.php";
    var idGraphType = "<?php echo $id_graph_type; ?>";
    var qGraphType = "<?php echo $q_graph_type; ?>";
    setMonth(<?php echo $month; ?>);
    setYear(<?php echo $year; ?>);
    
    $("#prev-month").click(function() {
        decMonth();
        var url = graphApp + "?" + "graph_type=" + idGraphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-id-graph").attr("src", url);
        url = graphApp + "?" + "graph_type=" + qGraphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-q-graph").attr("src", url);
    });
    $("#next-month").click(function() {
        incMonth();
        var url = graphApp + "?" + "graph_type=" + idGraphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-id-graph").attr("src", url);
        url = graphApp + "?" + "graph_type=" + qGraphType + "&" + "month=" + getMonth() + "&year=" + getYear();
        $("#daily-q-graph").attr("src", url);
    });
});
</script>

<?php



echo "<h3>User Applications</h3>\n";

foreach ($requests as $req) {
    $name = $req["name"];
    $email = $req["email"];
    $inst = $req["institution"];
    $body = $req["body"];

    echo <<<HTML
<hr>
<div>Name: $name</div>
<div>Email: $email</div>
<div>Institution: $inst</div>
<div>Description of use:</div>
<div style="margin-left: 20px">$body</div>
<div style="margin-bottom: 50px"></div>
HTML;
}


require_once(__DIR__."/inc/footer.inc.php");


