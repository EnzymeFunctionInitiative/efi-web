<?php

function error404($message = "") {
    errorBase($message, false, true, false);
}

function errorBase($message, $IsPretty, $Is404Page, $IsExpiredPage) {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
    if ($IsPretty)
        include("inc/header.inc.php");
    include("not_found.php");
    if ($IsPretty)
        include("inc/footer.inc.php");
    die();
}

function prettyError404($message = "") {
    errorBase($message, true, true, false);
}

function prettyErrorExpired($message = "") {
    errorBase($message, true, false, true);
}

?>

