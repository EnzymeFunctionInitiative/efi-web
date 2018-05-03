<?php
// A main.inc.php must have been loaded before including this file in order to obtain the $db variable,
// used below.
require_once(__DIR__ . "/../libs/user_auth.class.inc.php");

$IsLoggedIn = false;
$IsAdminUser = false;
if (user_auth::has_token_cookie()) {
    $userEmail = user_auth::get_email_from_token($db, user_auth::get_user_token());
    if ($userEmail)
        $IsLoggedIn = $userEmail;

    $IsAdminUser = user_auth::get_user_admin($db, $userEmail);
}
?>

