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
        <title>File Not Found</title>
        <style>
            body {
                background: #00AFF9 url(/images/Unplugged.png) center/cover no-repeat;
                height: 100vh;
                margin: 0;
                color: white;
            }
            
            h1 {
                margin: .8em 3rem;
                font: 4em Arial;
            }
            p {
                margin: .2em 3rem;
                font: 2em Arial;
            }

            a, a:hover {
                text-decoration: underline;
                color: white;
            }
        </style>
    </head>

    <body>
<?php
}
?>

<h1>Whoops!</h1>

<p>Something went wrong.</p>

<p><a href="#" onclick="window.history.back()">Go back to previous page</a></p>


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


