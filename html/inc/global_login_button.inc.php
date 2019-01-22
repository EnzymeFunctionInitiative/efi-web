<?php
$LoginText = '<a href="#" class="about" id="login-menu">SIGN IN</a>';
if (isset($IsLoggedIn) && $IsLoggedIn)
    $LoginText = '<a href="#" class="about" id="logout-menu" title="Logout"><i class="fas fa-sign-out-alt"></i> ' . $IsLoggedIn . '</a>';
?>

