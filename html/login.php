<?php
require_once(__DIR__."/../conf/settings_paths.inc.php");
require_once(__BASE_DIR__."/includes/main.inc.php");
require_once(__BASE_DIR__."/libs/global_settings.class.inc.php");


if (global_settings::get_website_enabled()) {
    header("Location: index.php");
    die();
}


require_once(__BASE_DIR__."/libs/user_auth.class.inc.php");
require_once(__BASE_DIR__."/includes/login_check.inc.php");

$IsLoginPage = true;
require_once("inc/header.inc.php");
?>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<?php if ($IsLoggedIn) { ?>
<a href="#" class="about" id="logout-menu" title="Logout"><i class="fas fa-sign-out-alt"></i> LOGOUT</a>
<?php } else { ?>
<a href="#" class="about" id="login-menu" title="Login"><i class="fas fa-sign-in-alt"></i> LOGIN</a>
<?php } ?>
<br>
<br>
<br>
<br>
<br>
<br>

<?php
require_once("inc/footer.inc.php");
?>

