<?php
$LoginText = '<a href="#" class="about" id="login-menu">SIGN IN</a>';
if (isset($IsLoggedIn) && $IsLoggedIn)
    $LoginText = '<a href="#" class="about" id="logout-menu">Logout ' . $IsLoggedIn . '</a>';
?>

