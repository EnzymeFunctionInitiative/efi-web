<?php
require_once(__DIR__."/../../init.php");
if (!isset($IsDisabled))
    $IsDisabled = false;
if (!isset($IsLoginPage))
    $IsLoginPage = false;
if (($IsDisabled && $IsLoginPage) || !isset($IsLoggedIn) || !$IsLoggedIn) {
?>
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
