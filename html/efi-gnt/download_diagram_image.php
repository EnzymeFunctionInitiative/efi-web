<?php
require_once(__DIR__."/../../init.php");

use \efi\gnt\settings;


$gntServer = settings::get_web_root();
$refServer = parse_url($_SERVER['HTTP_REFERER'],  PHP_URL_HOST);

$isError = false;
if (strpos($gntServer, $refServer) === FALSE || !isset($_POST["svg"])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    exit;
}


$type = "svg";
// PNG not supported yet
//if (sanitize::post_sanitize_string("type") == "png")
//    $type = "png";

$filename = sanitize::post_sanitize_string("name", "image");
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
$height = sanitize::post_sanitize_num("height", 0);


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
        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"${new_width}px\" height=\"${height}px\">" . $lsvg . $legend1_svg . $legend2_svg . "</svg>";
    }
//    header('Content-type: text/plain');
    header('Content-type: image/svg+xml');
    header('Content-Disposition: attachment; filename="' . $filename . '"'); 
    print $svg;
} elseif ($type == "png") {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
}


