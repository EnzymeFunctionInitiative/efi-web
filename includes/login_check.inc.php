<?php
require_once(__DIR__."/../init.php");

use \efi\user_auth;

$IsLoggedIn = false;
$IsAdminUser = false;
if (user_auth::has_token_cookie() && $db !== false) {
    $userEmail = user_auth::get_email_from_token($db, user_auth::get_user_token());
    if ($userEmail)
        $IsLoggedIn = $userEmail;

    $IsAdminUser = user_auth::get_user_admin($db, $userEmail);
}

