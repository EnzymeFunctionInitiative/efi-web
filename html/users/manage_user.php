<?php
require_once(__DIR__."/../../init.php");

use \efi\user_auth;
use \efi\users\user_manager;


require_once(__BASE_DIR__."/includes/login_check.inc.php");


$user_mgr = new user_manager($db);
$user_ids = $user_mgr->get_user_ids();
$all_groups = $user_mgr->get_group_names();
$user_est = $user_mgr->get_user_stats(__EFI_EST_DB_NAME__, __EFI_EST_TABLE__);
$user_gnt = $user_mgr->get_user_stats(__EFI_GNT_DB_NAME__, __EFI_GNT_GNN_TABLE__);
$user_diagrams = $user_mgr->get_user_stats(__EFI_GNT_DB_NAME__, __EFI_GNT_DIAGRAM_TABLE__);
$user_shortbred = $user_mgr->get_user_stats(__EFI_SHORTBRED_DB_NAME__, __EFI_SHORTBRED_TABLE__);

require_once("inc/header.inc.php");

?>

<p style="margin-top: 30px">
</p>

<h3>Users</h3>

<table class="pretty">
    <thead>
        <th>#</th>
        <th class="id-col">Email</th>
        <th>Admin</th>
        <th>Group(s)</th>
        <th>Status</th>
        <th># Jobs</th>
        <th>Actions</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < count($user_ids); $i++) {
    $user_id = $user_ids[$i];
    $user = $user_mgr->get_user($user_id);
    $user_email = $user["email"];
    $user_status = $user["status"];
    $is_admin = $user["admin"] ? '<i class="fas fa-check"></i>' : "";
    $num_jobs = count_jobs($user_email, $user_est, $user_gnt, $user_diagrams, $user_shortbred);
    $groups = implode(", ", $user["group"]);
    $user_num = $i + 1;
    echo <<<ROW
        <tr>
            <td>$user_num</td>
            <td>$user_email</td>
            <td>$is_admin</td>
            <td>$groups</td>
            <td>$user_status</td>
            <td>$num_jobs</td>
            <td><input type="checkbox" name="sel-user-id" value="$user_id" data-info="$user_email (#jobs=$num_jobs; status=$user_status)"></td>
        </tr>

ROW;
}

?>
    </tbody>
</table>


<button id="add-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-user-plus"></i> Add Single User</button>
<button id="update-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-users-cog"></i> Add Users to Group</button>
<button id="remove-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-user-secret"></i> Remove Users from Group</button>
<button id="set-admin-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-user-secret"></i> Set User to Admin</button>
<button id="reset-password-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-unlock-alt"></i> Reset Passwords</button>
<button id="delete-user-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-trash-alt"></i> Delete User</button>

<div style="margin-top:30px;">
Bulk insert/update:<br>
<textarea name="user-bulk" id="user-bulk" cols="99" rows="15">
</textarea>
<br>
<button id="bulk-add-btn" class="ui-button ui-widget ui-corner-all">Bulk Add</button>
</div>






<div id="add-user-dlg" class="hidden" title="Add User">
<div>Email: <input type="text" name="new-user-email" id="new-user-email"></div>
<div>Password: <input type="password" name="new-user-password" id="new-user-password"> (optional)</div>
<div>Password (confirm): <input type="password" name="new-user-password-confirm" id="new-user-password-confirm"> (optional)</div>
<div>Group:
    <select name="new-user-group" id="new-user-group">
        <option value="<?php echo user_manager::DEFAULT_GROUP; ?>"><?php echo user_manager::DEFAULT_GROUP; ?></option>
<?php
foreach ($all_groups as $group) {
    echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select> (optional)</div>




<div style="color: red" id="new-user-msg"></div>
</div>




<div id="add-group-dlg" class="hidden" title="Add Users to Group">
Group:<br>
<select name="update-user-group" id="update-user-group">
<?php
foreach ($all_groups as $group) {
    if ($group != user_manager::DEFAULT_GROUP)
        echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>

<div id="remove-group-dlg" class="hidden" title="Remove Users from Group">
Remove users from selected group:<br>
<select name="remove-user-group" id="remove-user-group">
<?php
foreach ($all_groups as $group) {
    echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>

<div id="delete-user-dlg" class="hidden" title="Delete Users">
Are you sure you want to delete these users from the system?<br>
<div id="delete-user-list"></div>
</div>



<script>

$(document).ready(function() {
    var addDlg = $("#add-user-dlg");
    var updateGroupDlg = $("#add-group-dlg");
    var removeGroupDlg = $("#remove-group-dlg");
    var deleteUserDlg = $("#delete-user-dlg");
    var setAdminDlg = $("#set-admin-dlg");
    
    var defaultHandler = function(json) {
        if (json.valid) {
            window.location = "manage_user.php";
        }
    };

    var addUserFn = function() {
        var handler = function(json) {
            if (json.valid) {
                addDlg.dialog("close");
                window.location = "manage_user.php";
            } else {
                $("#new-user-msg").text(json.message);
            }
        };
        submitNewUser(handler);
    };

    var updateGroupFn = function() {
        submitUpdateGroup(defaultHandler, 2);
    };
    var removeGroupFn = function() {
        submitUpdateGroup(defaultHandler, 1);
    };
    var deleteUserFn = function() {
        submitUserDelete(defaultHandler);
    };

    var populateDeleteUserDlg = function() {
        var list = $("#delete-user-list");
        $.each($("input[name='sel-user-id']:checked"), function() {
            var info = $(this).data("info");
            list.append(info + "<br>");
        });
    };

    addDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": addUserFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    updateGroupDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": updateGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    removeGroupDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": removeGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    deleteUserDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 500,
        buttons: { "Ok": deleteUserFn, "Cancel": function() { $(this).dialog("close"); } }
    });

    $("#bulk-add-btn").click(function() { submitBulkUser(defaultHandler); });
    $("#add-btn").click(function() { addDlg.dialog("open"); });
    $("#update-group-btn").click(function() { updateGroupDlg.dialog("open"); });
    $("#remove-group-btn").click(function() { removeGroupDlg.dialog("open"); });
    $("#reset-password-btn").click(function() { submitPasswordReset(defaultHandler); });
    $("#delete-user-btn").click(function() { populateDeleteUserDlg(); deleteUserDlg.dialog("open"); ; });
    //$("#set-admin-btn").click(function() { submitUserAdmin(function(e){console.log(e);}); window.location = "manage_user.php"; });
    $("#set-admin-btn").click(function() { submitUserAdmin(function(e){console.log(e);}); });
});

</script>


<?php

function count_jobs($email, $est, $gnt, $diagrams, $shortbred) {
    $num_jobs = 0;
    if (isset($est[$email]))
        $num_jobs += $est[$email];
    if (isset($gnt[$email]))
        $num_jobs += $gnt[$email];
    if (isset($diagrams[$email]))
        $num_jobs += $diagrams[$email];
    if (isset($shortbred[$email]))
        $num_jobs += $shortbred[$email];

    return $num_jobs;
}
?>

<?php require_once("inc/footer.inc.php"); ?>


