
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

<?php if (!isset($IsLoggedIn) || !$IsLoggedIn) { ?>
<div id="login-form" class="login-form hidden">
    <div style="margin-bottom:15px">Sign in or <a href="<?php echo $SiteUrlPrefix; ?>/user_account.php?action=create">create an account</a> to view previous job history.</div>
    <table border="0">
        <tbody>
            <tr><td>Email Address:</td><td><input type="text" name="login-email" id="login-email"></td></tr>
            <tr><td>Password:</td><td><input type="password" name="login-password" id="login-password"></td></tr>
        </tbody>
    </table>
    <div id="login-error"></div>
</div>

<?php } ?>

