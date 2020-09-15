<?php

const TYPE404 = 404;
const TYPE500 = 500;

function error_404($message = "") {
    error404($message);
}
function error404($message = "") {
    error_base($message, false, TYPE404, false);
}

function error_500($message = "") {
    error500($message);
}
function error500($message = "") {
    error_base($message, false, TYPE500, false);
}

function error_base($message, $IsPretty, $errType, $IsExpiredPage) {
    $errText = $errType == TYPE500 ? "500 Server Error" : "404 Not Found";
    header($_SERVER["SERVER_PROTOCOL"]." ".$errType, true, $errType);
    if ($IsPretty)
        include("inc/header.inc.php");
    if ($errType == TYPE500)
        include(__DIR__."/../html/not_found.php");
    else
        include(__DIR__."/../html/not_found.php");
    if ($IsPretty)
        include("inc/footer.inc.php");
    die();
}

function pretty_error_404($message = "") {
    prettyError404($message);
}
function prettyError404($message = "") {
    error_base($message, true, TYPE404, false);
}

function pretty_error_expired($message = "") {
    prettyErrorExpired($message);
}
function prettyErrorExpired($message = "") {
    error_base($message, true, TYPE500, true);
}

