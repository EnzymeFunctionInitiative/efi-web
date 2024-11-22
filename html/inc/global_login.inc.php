<?php
require_once(__DIR__."/../../init.php");
?>

<script>
$(document).ready(function() {
    var updateMsg = $("#update-message");
    if (updateMsg.children().count > 0 || updateMsg.text().trim().length > 0)
        updateMsg.removeClass("initial-hidden");
<?php if (!isset($IsLoggedIn) || !$IsLoggedIn) { ?>
    addLoginActions("<?php echo $SiteUrlPrefix; ?>/user_login.php", "index.php");
<?php } else { ?>
    addLogoutActions("<?php echo $SiteUrlPrefix; ?>/user_login.php", "index.php");
<?php } ?>

<?php if (isset($_GET["show-login"]) && $_GET["show-login"] == 1) { ?>
    showLoginForm();
<?php } ?>
}).tooltip();
</script>

<?php
require_once(__DIR__ . "/login_form.inc.php");
?>

