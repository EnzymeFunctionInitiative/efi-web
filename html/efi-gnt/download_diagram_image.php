<?php
require_once(__DIR__."/../../conf/settings_paths.inc.php");
require_once(__GNT_DIR__."/includes/main.inc.php");
require_once(__GNT_DIR__."/libs/settings.class.inc.php");

$gntServer = settings::get_web_root();
$refServer = parse_url($_SERVER['HTTP_REFERER'],  PHP_URL_HOST);

$isError = false;
if (strpos($gntServer, $refServer) === FALSE || !isset($_POST["svg"])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}


$type = "svg";
if (isset($_POST["type"]) && $_POST["type"] == "png")
    $type = "png";

$filename = "image";
if (isset($_POST["name"]) && strlen($_POST["name"]) > 3)
    $filename = $_POST["name"];

$filename .= "." . $type;

$svg = $_POST["svg"];
$svg = rawurldecode($svg);
$legend1_svg = "";
if (isset($_POST["legend1-svg"])) {
    $legend1_svg = $_POST["legend1-svg"];
    $legend1_svg = rawurldecode($legend1_svg);
}
$legend2_svg = "";
if (isset($_POST["legend2-svg"])) {
    $legend2_svg = $_POST["legend2-svg"];
    $legend2_svg = rawurldecode($legend2_svg);
}
$height = "";
if (isset($_POST["height"])) {
    $height = $_POST["height"];
}


if ($type == "svg") {
    $pos = strpos($svg, '>') + 1;
    $svg = substr($svg, 0, $pos) . '<defs><style type="text/css"><![CDATA[.an-arrow-selected{opacity:1.0;stroke:#000;stroke-width:3;}.an-arrow-mute{opacity:0.4;}]]></style></defs>' . substr($svg, $pos);
    $new_width = ($legend1_svg && $legend2_svg) ? 1600 : (($legend1_svg || $legend2_svg) ? 1300 : 1000);
    if ($legend1_svg || $legend2_svg) {
        $lsvg = str_replace('width="100%"', 'width="1000px"', $svg);
        if (!$height) {
            preg_match("/\"height:(\d+)px\"/", $lsvg, $matches);
            $height = 1000;
            if (isset($matches[1]))
                $height = $matches[1];
        }
        $svg = "<svg width=\"${new_width}px\" height=\"${height}px\">" . $lsvg . $legend1_svg . $legend2_svg . "</svg>";
    }
//    header('Content-type: text/plain');
    header('Content-type: image/svg+xml');
    header('Content-Disposition: attachment; filename="' . $filename . '"'); 
    print $svg;
} elseif ($type == "png") {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
}


?>

