<?php

const TYPE404 = 404;
const TYPE500 = 500;

function error404($message = "") {
    errorBase($message, false, TYPE404, false);
}

function error500($message = "") {
    errorBase($message, false, TYPE500, false);
}

function errorBase($message, $IsPretty, $errType, $IsExpiredPage) {
    $errText = $errType == TYPE500 ? "500 Server Error" : "404 Not Found";
    header($_SERVER["SERVER_PROTOCOL"]." ".$errType, true, $errType);
    if ($IsPretty)
        include("inc/header.inc.php");
    if ($errType == TYPE500)
        include(__DIR__."/../html/server_error.php");
    else
        include(__DIR__."/../html/not_found.php");
    if ($IsPretty)
        include("inc/footer.inc.php");
    die();
}

function prettyError404($message = "") {
    errorBase($message, true, TYPE404, false);
}

function prettyErrorExpired($message = "") {
    errorBase($message, true, TYPE500, true);
}

?>

