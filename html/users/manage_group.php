<?php
require_once(__DIR__."/../../init.php");

use \efi\user_auth;
use \efi\users\user_manager;


require_once(__BASE_DIR__."/includes/login_check.inc.php");


//$group_mgr = new group_manager($db);
$user_mgr = new user_manager($db);
//$est_job_mgr = new job_manager($db, __EFI_EST_DB_NAME__, __EFI_EST_TABLE__);
//$gnn_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_GNN_TABLE__);
//$gnd_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_DIAGRAM_TABLE__);

$user_ids = $user_mgr->get_user_ids();
$group_names = $user_mgr->get_group_names();


require_once(__DIR__."/inc/header.inc.php");

?>

<p style="margin-top: 30px">
</p>

<table class="pretty">
    <thead>
        <th>Group Name</th>
        <th>Status</th>
        <th>Time Open</th>
        <th>Time Closed</th>
        <th>Actions</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < count($group_names); $i++) {
    $group_name = $group_names[$i];
    $group = $user_mgr->get_group($group_name);
    $group_status = $group["status"];
    $time_open = $group["time_open"];
    $time_closed = $group["time_closed"];
    $toggle_class = $group_status == group_status::Active ? "toggle-on" : "toggle-off";
    echo <<<HTML
        <tr>
            <td>$group_name</td>
            <td>$group_status</td>
            <td>$time_open</td>
            <td>$time_closed</td>
            <td><i class="fas fa-$toggle_class toggle-group-status clicker" data-group="$group_name" data-status="$group_status"></i></td>
        </tr>

HTML;
}

?>
    </tbody>
</table>

<button id="add-btn" class="ui-button ui-widget ui-corner-all">Add Group</button>

<div id="add-group-dlg" class="hidden" title="Add Group">

<div>Name: <input type="text" name="new-group-name" id="new-group-name"></div>
<div>Time Open: <input type="text" name="new-group-open" id="new-group-open"> (optional)</div>
<div>Time Closed: <input type="text" name="new-group-closed" id="new-group-closed"> (optional)</div>

<div style="color: red" id="new-group-msg"></div>

</div>

<script>

$(document).ready(function() {
    var addDlg = $("#add-group-dlg");

    var defaultHandler = function(json) {
        if (json.valid) {
            window.location = "manage_group.php";
        }
    };

    var addGroupFn = function() {
        var handler = function(json) {
            if (json.valid) {
                addDlg.dialog("close");
                window.location = "manage_group.php";
            } else {
                $("#new-group-msg").text(json.message);
            }
        };
        submitNewGroup(handler);
    };

    addDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": addGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });

    $("#new-group-open").datepicker();
    $("#new-group-closed").datepicker();

    $("#add-btn").click(function() {
        addDlg.dialog("open");
    });

    $(".toggle-group-status").click(function() {
        var group = $(this).data("group");
        submitToggleGroupStatus(defaultHandler, group);
    });
});

</script>


<?php require_once(__DIR__."/inc/footer.inc.php"); ?>


