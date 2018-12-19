<?php

if (!isset($message)) {
    $message = "That page does not exist.";
}

$jokes = array(
    array("What do you call the enzyme that breaks down ice cream?", "Haagendase"),
    array("You must be a catalyst...", "'Cause my EA decreased"),
    array("They call me DJ Enzyme...", "Because I'm always breaking it down."), 
    array("What do you get when you cross a rabbit with an amoeba?", "An amoebit.  It can multiply and divide at the same time."),
    array("Biology is the only science in which multiplication is the same thing as division.", ""),
);


$joke_idx = rand(0, 4);
$joke = $jokes[$joke_idx][0] . "<br>\n" . $jokes[$joke_idx][1];


if (!isset($IsPretty)) {
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">   
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>File Not Found</title>

        <style>
            .error-icon {
                font: 6em bold;
                float: left;
                margin-left: 5%;
                margin-right: 30px;
            }

            .error-title {
                font: 4em bold;
                padding-top: 20px;
                margin-top: 5%;
                
            }

            .error-text {
                font-size: 1.2em;
            }
        </style>
    </head>

    <body>
<?php
}
?>

<div style="font: 2em bold">Not found!</div>
<div style="font-size: 1.5em; margin-top: 20px;">We're having a hard time finding what you requested.</div>
<div style="font-size: 1.2em; margin-top: 20px;"><img src="/images/404.jpg" alt="Picture of scientists looking into microscope."></div>

<!-- Here's a dumb joke for you:
<div style="font-size: 0.7em; margin-top: 30px;"><?php echo $joke; ?></div>
-->

<!--
        <div id="error-message" style="margin-bottom: 50px">
            <div style="font: 6em bold;float: left;margin-left: 5%;margin-right: 30px">:-(</div>
            <div style="font: 4em bold;padding-top: 20px;margin-top: 5%;">Does not compute...</div>
            <div style="font-size: 1.2em;"><?php echo $message; ?></div>
        </div>
-->
<?php
if (!isset($IsPretty)) {
?>
    </body>
</html>
<?php
}
?>


