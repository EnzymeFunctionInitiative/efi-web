<?php
require "../libs/user_auth.class.inc.php";
require_once "../includes/main.inc.php";

require_once "inc/header.inc.php";

$action = "";
if (isset($_GET["action"]))
    $action = $_GET["action"];

$setPassword = false;
if (isset($_GET["sp"]))
    $setPassword = true;

$loginAction = "user_login.php";

?>
<script>var loginAction = "<?php echo $loginAction; ?>";</script>
<?php

////// CREATE USER ACCOUT /////////////////////////////////////////////////////////////////////////////////////////////

if ($action == "create") {



?>
    <h2>Create A User Account</h2>
    <div id="login-create-form-contents" class="login-form">
        Creating a user account allows you to view a list of and access your previous jobs.
        It also pre-fills some information on the job creation forms.
        <table border="0" style="margin-top: 15px; margin-bottom: 15px">
            <tbody>
                <tr><td>Email Address:</td><td><input type="text" name="login-create-email" id="login-create-email"></td></tr>
                <tr><td>Password:</td><td><input type="password" name="login-create-password" id="login-create-password"></td></tr>
                <tr><td>Confirm password:</td><td><input type="password" name="login-create-password-confirm" id="login-create-password-confirm"></td></tr>
                <tr><td colspan="2"><input type="checkbox" name="login-create-mailinglist" id="login-create-mailinglist" checked>
                        <label for="login-create-mailinglist">Sign me up for infrequent EFI tool news</label></td></tr>
            </tbody>
        </table>
        <button type="button" id="login-create-btn" class="light">Create Account</button>
        <div id="login-create-error"></div>
    </div>
    <div id="login-create-form-confirmation" class="hidden">
        A verification email has been sent to the email address given. 
        Follow the instructions in the email to activate your account.
        <p class="center"><a href="index.php"><button class="light" type="button">Continue</button></a></p>
    </div>

    <script>

    $(document).ready(function() {
        var loginCreateSubmit = $("#login-create-btn");
        loginCreateSubmit.click(function(e) {

            var emailElem = $("#login-create-email");
            var passElem = $("#login-create-password");
            var passElem2 = $("#login-create-password-confirm");
            var email = emailElem.val();
            var pass = passElem.val();
            var pass2 = passElem2.val();
            var doSubscribe = $("#login-create-mailinglist").prop("checked");
            
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var emailIsValid = emailRe.test(email);
            if (!emailIsValid)
                emailElem.css({"background-color": "#E77471"});
            else 
                emailElem.css({"background-color": "#fff"});

            if (pass.length == 0 || pass != pass2) {
                passElem.css({"background-color": "#E77471"});
                passElem2.css({"background-color": "#E77471"});
            } else {
                passElem.css({"background-color": "#fff"});
                passElem2.css({"background-color": "#fff"});
            }

            if (pass.length && pass == pass2 && emailIsValid) {
                var fd = new FormData();
                fd.append("email", email);
                fd.append("password", pass);
                if (doSubscribe)
                    fd.append("mailinglist", "1");
                else
                    fd.append("mailinglist", "0");
                fd.append("action", "create");

                var createHandler = function() {
                    $("#login-create-form-contents").addClass("hidden");
                    $("#login-create-form-confirmation").removeClass("hidden");
                };
                var errorHandler = function(msg) {
                    if (!msg)
                        msg = "Unable to create the user account.";
                    $("#login-create-error").text(msg);
                };

                doLoginFormSubmit(loginAction, fd, createHandler, errorHandler);
            }
        });
    });

    </script>
<?php



////// RESET PASSWORD /////////////////////////////////////////////////////////////////////////////////////////////////

} elseif ($action == "reset" || ($action == "confirm" && $setPassword)) {
    $resetToken = "";
    if (isset($_GET["reset-token"]) && $_GET["reset-token"]) {
        if (user_auth::check_reset_token($db, $_GET["reset-token"])) {
            $resetToken = $_GET["reset-token"];
        }
    }

    if ($action == "confirm") {
        $titleText = "Set";
        $resetToken = $_GET["token"];
    } else {
        $titleText = "Reset";
    }

    if (!$resetToken) {
        echo "<br><br><br><b>The provided information is invalid.</b>\n";
    } else {


?>

    <h2><?php echo $titleText; ?> a Password</h2>
    <div id="login-reset-form-contents" class="login-form">
        <table border="0" style="margin-top: 15px; margin-bottom: 15px">
            <tbody>
                <tr><td>Password:</td><td><input type="password" name="login-reset-password" id="login-reset-password"></td></tr>
                <tr><td>Confirm password:</td><td><input type="password" name="login-reset-password-confirm" id="login-reset-password-confirm"></td></tr>
            </tbody>
        </table>
        <button type="button" id="login-reset-btn" class="light">Reset Password</button>
        <div id="login-reset-error"></div>
    </div>

    <script>

    $(document).ready(function() {
        var loginCreateSubmit = $("#login-reset-btn");
        loginCreateSubmit.click(function(e) {

            var passElem = $("#login-reset-password");
            var passElem2 = $("#login-reset-password-confirm");
            var pass = passElem.val();
            var pass2 = passElem2.val();
            var doSubscribe = $("#login-reset-mailinglist").prop("checked");
            
            if (pass.length == 0 || pass != pass2) {
                passElem.css({"background-color": "#E77471"});
                passElem2.css({"background-color": "#E77471"});
            } else {
                passElem.css({"background-color": "#fff"});
                passElem2.css({"background-color": "#fff"});
            }

            if (pass.length && pass == pass2) {
                var fd = new FormData();
                fd.append("reset_token", "<?php echo $resetToken; ?>");
                fd.append("password", pass);
                fd.append("action", "reset");

                var resetHandler = function() {
                    window.location.href = "index.php?show-login=1";
                };
                var errorHandler = function(msg) {
                    if (!msg)
                        msg = "Unable to reset the password.";
                    $("#login-reset-error").text(msg);
                };

                doLoginFormSubmit(loginAction, fd, resetHandler, errorHandler);
            }
        });
    });

    </script>

<?php
    }


////// CHANGE PASSWORD ////////////////////////////////////////////////////////////////////////////////////////////////

} elseif ($action == "change") {

?>

    <h2>Change Password</h2>
    <div id="login-change-form-contents" class="login-form">
        <table border="0" style="margin-top: 15px; margin-bottom: 15px">
            <tbody>
                <tr><td>Email Address:</td><td><input type="text" name="login-change-email" id="login-change-email"></td></tr>
                <tr><td>Password:</td><td><input type="password" name="login-change-old-password" id="login-change-old-password"></td></tr>
                <tr><td>Password:</td><td><input type="password" name="login-change-password" id="login-change-password"></td></tr>
                <tr><td>Confirm password:</td><td><input type="password" name="login-change-password-confirm" id="login-change-password-confirm"></td></tr>
            </tbody>
        </table>
        <button type="button" id="login-change-btn" class="light">Reset Password</button>
        <div id="login-change-error"></div>
    </div>

    <script>

    $(document).ready(function() {
        var loginCreateSubmit = $("#login-change-btn");
        loginCreateSubmit.click(function(e) {

            var emailElem = $("#login-create-email");
            var oldPassElem = $("#login-change-old-password");
            var passElem = $("#login-change-password");
            var passElem2 = $("#login-change-password-confirm");
            var email = emailElem.val();
            var oldPass = oldPassElem.val();
            var pass = passElem.val();
            var pass2 = passElem2.val();
            
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var emailIsValid = emailRe.test(email);
            if (!emailIsValid)
                emailElem.css({"background-color": "#E77471"});

            if (oldPass.length == 0) {
                oldPassElem.css({"background-color": "#E77471"});
            }

            if (pass.length == 0 || pass != pass2) {
                passElem.css({"background-color": "#E77471"});
                passElem2.css({"background-color": "#E77471"});
            } else {
                passElem.css({"background-color": "#fff"});
                passElem2.css({"background-color": "#fff"});
            }

            if (oldPass.length && pass.length && pass == pass2 && emailIsValid) {
                var fd = new FormData();
                fd.append("email", email);
                fd.append("old-password", oldPass);
                fd.append("password", pass);
                fd.append("action", "change");

                var changeHandler = function() {
                    window.location.href = "index.php?show-login=1";
                };
                var errorHandler = function(msg) {
                    if (!msg)
                        msg = "Unable to change the password.";
                    $("#login-change-error").text(msg);
                };

                doLoginFormSubmit(loginAction, fd, changeHandler, errorHandler);
            }
        });
    });

    </script>

<?php


////// CONFIRM EMAIL //////////////////////////////////////////////////////////////////////////////////////////////////

} elseif ($action == "confirm") {

    if (isset($_GET["token"]) && $_GET["token"] && user_auth::validate_new_account($db, $_GET["token"])) {
?>

    <h2>Account Activation</h2>
    The account has been activated.
    <p class="center"><a href="index.php?show-login=1"><button class="light" type="button">Continue</button></a></p>

<?php
    } else {
?>

    <h2>Account Activation</h2>
    Unable to validate the account.
    <p class="center"><a href="index.php"><button class="light" type="button">Continue</button></a></p>

<?php
    }
} else {
?>
<?php
}


?>


<?php require_once('inc/footer.inc.php'); ?>


