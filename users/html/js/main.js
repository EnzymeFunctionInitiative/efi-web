
var DEBUG = 0;


function submitNewGroup(completionHandler) {

    var formAction = "do_group.php";
    
    var fd = new FormData();
    fd.append("action", "new");
    addParam(fd, "name", "new-group-name");
    addParam(fd, "open", "new-group-open");
    addParam(fd, "closed", "new-group-closed");

    doFormPost(formAction, fd, completionHandler);
}


function submitToggleGroupStatus(completionHandler, group) {

    var formAction = "do_group.php";

    var fd = new FormData();
    fd.append("action", "toggle");
    fd.append("group", group);

    doFormPost(formAction, fd, completionHandler);
}


function submitNewUser(completionHandler) {

    var formAction = "do_user.php";
    
    var fd = new FormData();
    fd.append("action", "new");
    addParam(fd, "email", "new-user-email");
    addParam(fd, "password", "new-user-password");
    addParam(fd, "password-confirm", "new-user-password-confirm");
    addParam(fd, "group", "new-user-group");

    doFormPost(formAction, fd, completionHandler);
}


function submitBulkUser(completionHandler) {

    var formAction = "do_user.php";
    
    var fd = new FormData();
    fd.append("action", "new-bulk");
    addParam(fd, "user-bulk", "user-bulk");

    doFormPost(formAction, fd, completionHandler);
}


function submitUpdateGroup(completionHandler, action) {

    var formAction = "do_user.php";

    var userIdList = [];
    $.each($("input[name='sel-user-id']:checked"), function() {
        userIdList.push($(this).val())
    });
    var userIds = userIdList.join(",");

    var fd = new FormData();
    if (action == 1)
        fd.append("action", "remove-group");
    else if (action == 2)
        fd.append("action", "update-group");
    fd.append("user-ids", userIds);
    addParam(fd, "group", "update-user-group");

    doFormPost(formAction, fd, completionHandler);
}


function submitUpdateJobGroup(completionHandler, type, action) {

    var formAction = "do_job.php";

    var jobIdList = [];
    $.each($("input[name='" + type + "-job-id']:checked"), function() {
        jobIdList.push($(this).val())
    });
    var jobIds = jobIdList.join(",");

    var action = action == 1 ? "remove" : "update";
    var prefix = type + "-" + action;

    var fd = new FormData();
    fd.append("action", action + "-group");
    fd.append("type", type);
    fd.append("job-ids", jobIds);
    addParam(fd, "group", prefix + "-job-group");

    doFormPost(formAction, fd, completionHandler);
}


function doFormPost(formAction, formData, completionHandler) {

    var xhr = new XMLHttpRequest();

    if (DEBUG) {
        for (var pair of formData.entries()) {
            console.log(pair[0] + " = " + pair[1]);
        }
    } else {
        xhr.open("POST", formAction, true);
        xhr.send(formData);
        xhr.onreadystatechange  = function(){
            if (xhr.readyState == 4  ) {
                // Javascript function JSON.parse to parse JSON data
                var jsonObj;
                try {
                    jsonObj = JSON.parse(xhr.responseText);
                } catch(e) {
                    jsonObj = {valid: false, message: "Unknown error occurred."};
                }
    
                // jsonObj variable now contains the data structure and can
                // be accessed as jsonObj.name and jsonObj.country.
                completionHandler(jsonObj);
            }
        }
    }
}

function addCbParam(fd, param, id, isCheckbox) {
    if (typeof id === 'undefined')
        id = param;
    var elem = document.getElementById(id);
    if (elem)
        fd.append(param, elem.checked);
}


function addParam(fd, param, id, isCheckbox) {
    if (typeof id === 'undefined')
        id = param;
    var elem = document.getElementById(id);
    if (elem)
        fd.append(param, elem.value);
}


