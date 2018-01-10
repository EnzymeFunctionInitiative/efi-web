<?php
session_start();

if (!array_key_exists("hi", $_SESSION))
    $_SESSION['hi'] = "main";

require_once "../includes/main.inc.php";
require_once "inc/header.inc.php";

?>

<p style="margin-top: 30px">
Stuff here
</p>


<p style="margin-bottom: 60px">
</p>



<?php require_once("inc/footer.inc.php"); ?>


