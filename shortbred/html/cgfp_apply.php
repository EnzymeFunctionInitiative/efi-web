<?php 

require_once("../includes/main.inc.php");
require_once(__BASE_DIR__ . "/libs/user_auth.class.inc.php");
require_once("Mail.php");
require_once("Mail/mime.php");

$user_email = "";

if (user_auth::has_token_cookie()) {
    $user_token = user_auth::get_user_token();
    $user_email = user_auth::get_email_from_token($db, $user_token);
}

if (!$user_email)
    error500();


$name = isset($_POST["app_name"]) ? $_POST["app_name"] : "";
$institution = isset($_POST["app_institution"]) ? $_POST["app_institution"] : "";
$desc = isset($_POST["app_desc"]) ? $_POST["app_desc"] : "";

if (!$name || !$institution || !$desc)
    error404("|$name|$institution|$desc|");


$message = <<<MSG
A EFI-CGFP User Group application has been submitted.  Here is the information
that was submitted:

NAME: $name

EMAIL: $user_email

INSTITUTION: $institution

APPLICATION DESCRIPTION:
$desc

MSG;

$subject = "EFI-CGFP User Application";
$to = settings::get_app_email();
if (!$to)
    $to = global_settings::get_admin_email();

send_email($subject, $message, $to);

functions::add_cgfp_application($db, $name, $user_email, $institution, $desc);

function send_email($subject, $plain_email, $to = "") {
    $from = "EFI-CGFP User Application <" . settings::get_admin_email() . ">";

    $message = new Mail_mime(array("eol" => "\n"));
    $message->setTXTBody($plain_email);
    $body = $message->get();
    $extraheaders = array("From "=> $from, "Subject" => $subject);
    $headers = $message->headers($extraheaders);

    $mail = Mail::factory("mail");
    $mail->send($to, $headers, $body);
}


require_once "inc/header.inc.php"; 

?>



<h2>Application Confirmed</h2>
<p>&nbsp;</p>
<p>Your application has been submitted.  You will be notified when it has been reviewed.</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p></p>
<p>&nbsp;</p>


<?php require_once "inc/footer.inc.php"; ?>


