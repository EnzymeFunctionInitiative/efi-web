
function showLoginForm() {
    $("#login-form").dialog("open");
}

function addLoginActions(loginAction, successRedirect) {
    var loginMenuBtn = $("#login-menu");
    if (loginMenuBtn.length) {
        var loginForm = $("#login-form");

        var loginCheckFn = function() {
            var emailElem = $("#login-email");
            var passElem = $("#login-password");
            var email = emailElem.val();
            var pass = passElem.val();
            
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var emailIsValid = emailRe.test(email);
            if (!emailIsValid)
                emailElem.css({"background-color": "#E77471"});
            else 
                emailElem.css({"background-color": "#fff"});

            if (pass.length == 0)
                passElem.css({"background-color": "#E77471"});
            else 
                passElem.css({"background-color": "#fff"});

            if (pass.length && emailIsValid) {
                var fd = new FormData();
                fd.append("email", email);
                fd.append("password", pass);
                fd.append("action", "login");
                var loginHandler = function() {
                    window.location.href = successRedirect;
                    loginForm.addClass("hidden");
                };
                var errorHandler = function(message) {
                    var loginError = $("#login-error");
                    loginError.text(message);
                    var resetBtn = $("<div><button type='button' class='light'>Reset Password</button></div>");
                    loginError.parent().append(resetBtn);
                    resetBtn.click(function() {
                            var resetFd = new FormData();
                            resetFd.append("email", email);
                            resetFd.append("action", "send-reset");
                            var okHandler = function() {
                                resetBtn.hide();
                                loginError.text("A password reset email has been sent to " + email + ". Please follow the instructions in the email to reset your password.");
                            };
                            var errHandler = function() {};
                            doLoginFormSubmit(loginAction, resetFd, okHandler, errorHandler);
                        });
                };
                doLoginFormSubmit(loginAction, fd, loginHandler, errorHandler);
            }
        };


        loginForm.dialog({resizeable: false, draggable: false, autoOpen: false, height: 350, width: 500,
            buttons: { "Sign In": loginCheckFn }
        });

        loginMenuBtn.click(function(e) {
            $("#login-form").dialog("open");
//            var offset = loginMenuBtn.parent().offset();
//            var height = loginMenuBtn.parent().height();
//            var parentWidth = loginMenuBtn.parent().width();
//            var btnBottom = offset.top + height;
//            var formLeft = offset.left - (loginForm.width() - parentWidth);
//            loginForm.css({top: btnBottom + "px", left: formLeft + "px"});
//            loginForm.toggleClass("hidden");
        });

//        var loginSubmit = $("#login-btn");
//        loginSubmit.click(function(e) );
//
//        var loginCreate = $("#login-create-link");
//        $("#login-create-form").dialog({resizeable: false, draggable: false, autoOpen: false, height: 450, width: 500,
//                buttons: {
////                    "Create": function() {
////                        doFormUserCreate();
////                        $("#login-create-form-contents").addClass("hidden");
////                        $("#login-create-form-confirmation").removeClass("hidden");
//////                        $(".ui-dialog-buttonpane button:contains('Create')").button({classes: {"ui-button": "hidden"}});
////                    },
//                    "Close": function() {
//                        $(this).dialog("close");
//                    }
//                }
//            });
//        loginCreate.click(function(e) {
//            loginForm.addClass("hidden");
//            $("#login-create-form").dialog("open");
//        });
//
//        var loginCreateSubmit = $("#login-create-btn");
//        loginCreateSubmit.click(function(e) {
//
//            var emailElem = $("#login-create-email");
//            var passElem = $("#login-create-password");
//            var passElem2 = $("#login-create-password-confirm");
//            var email = emailElem.val();
//            var pass = passElem.val();
//            var pass2 = passElem2.val();
//            var doSubscribe = $("#login-create-mailinglist").prop("checked");
//            
//            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//            var emailIsValid = emailRe.test(email);
//            if (!emailIsValid)
//                emailElem.css({"background-color": "#E77471"});
//            else 
//                emailElem.css({"background-color": "#fff"});
//
//            if (pass.length == 0 || pass != pass2) {
//                passElem.css({"background-color": "#E77471"});
//                passElem2.css({"background-color": "#E77471"});
//            } else {
//                passElem.css({"background-color": "#fff"});
//                passElem2.css({"background-color": "#fff"});
//            }
//
//            if (pass.length && pass == pass2 && emailIsValid) {
//                var fd = new FormData();
//                fd.append("email", email);
//                fd.append("password", pass);
//                if (doSubscribe)
//                    fd.append("mailinglist", "1");
//                else
//                    fd.append("mailinglist", "0");
//                fd.append("action", "create");
//
//                var createHandler = function() {
//                    $("#login-create-form-contents").addClass("hidden");
//                    $("#login-create-form-confirmation").removeClass("hidden");
//                };
//
//                doLoginFormSubmit(loginAction, fd, "#login-create-error", createHandler, "Unable to create the user account.");
//            }
//        });
    }
}


function doLoginFormSubmit(formAction, formData, completionHandler, errorHandler) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", formAction, true);
    xhr.send(formData);
    xhr.onreadystatechange = function(){
        if (xhr.readyState == 4) {
            var jsonObj = false;
            try {
                jsonObj = JSON.parse(xhr.responseText);
            } catch(e) {}

            if (jsonObj && jsonObj.valid) {
                if (jsonObj.cookieInfo)
                    document.cookie = jsonObj.cookieInfo;
                completionHandler();
            } else if (jsonObj && jsonObj.message) {
                errorHandler(jsonObj.message);
            } else {
                errorHandler("");
            }
        }
    }
}


