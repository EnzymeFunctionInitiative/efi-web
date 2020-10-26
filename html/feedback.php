<?php
require_once(__DIR__."/../conf/settings_paths.inc.php");
require_once(__BASE_DIR__."/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__."/vendor/autoload.php");

require "Mail.php";
require "Mail/mime.php";


$StyleAdditional = array('<style>.hideme { display: none; }</style>');
require_once(__DIR__ . "/inc/header.inc.php");


// Remove beginning and ending spaces.
function trim_val(&$val) {
    $val = trim($val);
}
array_filter($_POST, "trim_val");

if (!empty($_POST["url"]))
    exit();

// Specify parameters to validate/sanitize.
$param_spec = array(
    "name" => FILTER_SANITIZE_STRING,
    "inst" => FILTER_SANITIZE_STRING,
    "email" => FILTER_VALIDATE_EMAIL,
    "feedback" => FILTER_SANITIZE_STRING,
);

$params = filter_input_array(INPUT_POST, $param_spec);
$is_all_set = $params !== false && $params !== NULL && $params["name"] && $params["inst"] && $params["email"] && $params["feedback"];


if ($is_all_set) {
    send_feedback($params);
    header("Location: " . $_SERVER['REQUEST_URI'] . "?t=y");
    exit();
}


////// SHOW THANK YOU /////////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET["t"]) && $_GET["t"] == "y") {
?>

<h2>Thank You for Your Feedback</h2>

<p>
Thank you for your feedback!  One of our staff will respond to your message shortly.
</p>

<?php
////// SHOW FEEDBACK FORM ////////////////////////////////////////////////////////////////////////////////////////////
} else {
?>

<h2>Send Feedback or Questions about the EFI Tools</h2>

<p>
We value comments and questions from the community, including bug reports.
To submit feedback, please provide the following information.
</p>

<form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>" id="feedback-form">
    <div class="login-form">
        <table border="0" style="margin-top: 15px; margin-bottom: 15px">
            <tbody>
                <tr><td>Your name:</td><td><input type="text" name="name" id="feedback-name"></td></tr>
                <tr><td>Your institution:</td><td><input type="text" name="inst" id="feedback-inst"></td></tr>
                <tr><td>Your email address:</td><td><input type="text" name="email" id="feedback-email"></td></tr>
                <tr><td colspan="2">Comments:</td></tr>
                <tr><td colspan="2"><textarea name="feedback" id="feedback-text" rows="10" cols="80"></textarea></td></tr>
            </tbody>
        </table>
        <button type="submit" class="light">Send Feedback</button>
    </div>
    <div id="submit-error" style="display: none;">
        <b>Please fill in all fields.</b>
    </div>
    <input type="text" name="url" value="" class="hideme">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
</form>

    <script>

    $(document).ready(function() {
        function markField(elem, clear) {
            if (clear)
                elem.css({"background-color": "#fff"});
            else
                elem.css({"background-color": "#E77471"});
        }

        $("#feedback-form").submit(function(e) {
            var emailElem = $("#feedback-email");
            var nameElem = $("#feedback-name");
            var instElem = $("#feedback-inst");
            var textElem = $("#feedback-text");

            var email = emailElem.val();
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var emailIsValid = emailRe.test(email);

            var nameIsValid = $("#feedback-name").val().length > 0;
            var instIsValid = $("#feedback-inst").val().length > 0;
            var textIsValid = $("#feedback-text").val().length > 0;

            markField(emailElem, emailIsValid);
            markField(nameElem, nameIsValid);
            markField(instElem, instIsValid);
            markField(textElem, textIsValid);

            if (!emailIsValid || !nameIsValid || !instIsValid || !textIsValid) {
                $("#submit-error").show();
                return false;
            } else {
                $("#submit-error").hide();
                return true;
            }
        });
    });

    </script>
<?php
}
?>

<div style="margin-top: 150px"></div>

<?php $HideFeedback = true; ?>
<?php require_once('inc/footer.inc.php'); ?>


<?php


function send_feedback($params) {

    $name = $params["name"];
    $inst = $params["inst"];
    $email = $params["email"];
    $text = $params["feedback"];

    $plain_email = <<<EMAIL

We received feedback from a website user:

NAME: $name

INSTITUTION: $inst

E-MAIL: $email

MESSAGE:

$text


EMAIL;

//    $html_email = nl2br($plain_email, false);

    $subject = "EFI Feedback Form Submission";
    $to = global_settings::get_feedback_email();
    $from = "EFI Feedback Form <" . global_settings::get_admin_email() . ">";

    $message = new Mail_mime(array("eol" => PHP_EOL));
    $message->setTXTBody($plain_email);
//    $message->setHTMLBody($html_email);
    $body = $message->get();
    $extraheaders = array("From" => $from, "Subject" => $subject);
    $headers = $message->headers($extraheaders);

    $mail = Mail::factory("mail");
    $mail->send($to, $headers, $body);
}

?>

