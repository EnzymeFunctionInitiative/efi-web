<?php

require_once("../../includes/stats_main.inc.php");
require_once("../../libs/functions.class.inc.php");

$month = date('n');
if (isset($_POST['month'])) {
    $month = $_POST['month'];
}
$year = date('Y');
if (isset($_POST['year'])) {
    $year = $_POST['year'];
}

$requests = functions::get_cgfp_applications($db);

$id_graph_array = array('graph_type' => "identify_daily_jobs", 'month' => $month, 'year' => $year);
$identify_graph = "<img src='daily_graph.php?" . http_build_query($id_graph_array) . "'>";
$q_graph_array = array('graph_type' => "identify_daily_jobs", 'month' => $month, 'year' => $year);
$quantify_graph = "<img src='daily_graph.php?" . http_build_query($q_graph_array) . "'>";








require_once("inc/header.inc.php");

echo "<div style=\"margin-top: 70px\"></div>\n";

$month_html = "<select class='form-control' name='month'>";
for ($i=1;$i<=12;$i++) {
    if ($month == $i) {
        $month_html .= "<option value='" . $i . "' selected='selected'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    } else {
        $month_html .= "<option value='" . $i . "'>" . date("F", mktime(0, 0, 0, $i, 10)) . "</option>\n";
    }
}
$month_html .= "</select>";

$year_html = "<select class='form-control' name='year'>";
for ($i=2014;$i<=date('Y');$i++) {
    if ($year = $i) {
        $year_html .= "<option selected='selected' value='" . $i . "'>". $i . "</option>\n";
    } else {
        $year_html .= "<option value='" . $i . "'>". $i . "</option>\n";
    }

}
$year_html .= "</select>";



?>

<form class='form-inline' method='post' action='<?php echo $_SERVER['PHP_SELF']; ?>'>
<?php echo $month_html; ?>
<?php echo $year_html; ?>
<input class='btn btn-primary' type='submit' name='create_user_report' value='Get Daily Graph'>

<hr>
<?php echo $identify_graph; ?>
<?php echo $quantify_graph; ?>
</form>



<?php



echo "<h3>User Applications</h3>\n";

foreach ($requests as $req) {
    $name = $req["name"];
    $email = $req["email"];
    $inst = $req["institution"];
    $body = $req["body"];

    echo <<<HTML
<hr style="margin-top:30px">
<div>Name: $name</div>
<div>Email: $email</div>
<div>Institution: $inst</div>
<div>Description of use:</div>
<div style="margin-left: 20px">$body</div>
HTML;
}


require_once("inc/footer.inc.php");
?>

