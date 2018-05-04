<?php
require_once("../includes/main.inc.php");
require_once("../../libs/user_auth.class.inc.php");
require_once("../../includes/login_check.inc.php");


//$user_mgr = new user_manager($db);
//$est_job_mgr = new job_manager($db, __EFI_EST_DB_NAME__, __EFI_EST_TABLE__);
//$gnn_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_GNN_TABLE__);
//$gnd_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_DIAGRAM_TABLE__);

$user_mgr = new user_manager($db);
$user_ids = $user_mgr->get_user_ids();
$all_groups = $user_mgr->get_group_names();

$job_mgr = new job_manager($db, __MYSQL_AUTH_DATABASE__, __EFI_EST_DB_NAME__, __EFI_GNT_DB_NAME__);

$est_ids = $job_mgr->get_all_est_job_ids();
$gnt_ids = $job_mgr->get_all_gnt_job_ids();

require_once("inc/header.inc.php");

?>

<p style="margin-top: 30px">
</p>

<h3>EST Jobs</h3>

<table class="pretty">
    <thead>
        <th>Generate ID</th>
        <th>Job Info</th>
        <th>Job Status</th>
        <th>Email Address</th>
        <th>Group(s)</th>
        <th>Actions</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < count($est_ids); $i++) {
    $id = $est_ids[$i];
    $job = $job_mgr->get_est_job_by_id($id);

    $info = $job["info"];
    $status = $job["status"];
    $email = $job["email"];
    $group = implode(", ", $job["group"]);

    echo <<<HTML
        <tr>
            <td>$id</td>
            <td>$info</td>
            <td>$status</td>
            <td>$email</td>
            <td>$group</td>
            <td><input type="checkbox" name="est-job-id" value="$id"></td>
        </tr>

HTML;
}

?>
    </tbody>
</table>

<button id="est-update-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-users-cog"></i> Add Job to Group</button>
<button id="est-remove-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-user-secret"></i> Remove Job from Group</button>


<h3>GNT Jobs</h3>

<table class="pretty">
    <thead>
        <th>GNN ID</th>
        <th>Job Info</th>
        <th>Job Status</th>
        <th>Email Address</th>
        <th>Group(s)</th>
        <th>Actions</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < count($est_ids); $i++) {
    $id = $est_ids[$i];
    $job = $job_mgr->get_est_job_by_id($id);

    $info = $job["info"];
    $status = $job["status"];
    $email = $job["email"];
    $group = implode(", ", $job["group"]);

    echo <<<HTML
        <tr>
            <td>$id</td>
            <td>$info</td>
            <td>$status</td>
            <td>$email</td>
            <td>$group</td>
            <td><input type="checkbox" name="gnt-job-id" value="$id"></td>
        </tr>

HTML;
}

?>
    </tbody>
</table>

<button id="gnt-update-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-users-cog"></i> Add Job to Group</button>
<button id="gnt-remove-group-btn" class="ui-button ui-widget ui-corner-all"><i class="fas fa-user-secret"></i> Remove Job from Group</button>










<div id="est-update-group-dlg" class="hidden" title="Add Jobs to Group">
Group:<br>
<select name="est-update-job-group" id="est-update-job-group">
<?php
foreach ($all_groups as $group) {
    if ($group != user_manager::DEFAULT_GROUP)
        echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>
<div id="est-remove-group-dlg" class="hidden" title="Remove Jobs from Group">
Group:<br>
<select name="est-remove-job-group" id="est-remove-job-group">
<?php
foreach ($all_groups as $group) {
    if ($group != user_manager::DEFAULT_GROUP)
        echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>












<div id="gnt-update-group-dlg" class="hidden" title="Add Jobs to Group">
Group:<br>
<select name="gnt-update-job-group" id="gnt-update-job-group">
<?php
foreach ($all_groups as $group) {
    if ($group != user_manager::DEFAULT_GROUP)
        echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>
<div id="gnt-remove-group-dlg" class="hidden" title="Remove Jobs from Group">
Group:<br>
<select name="gnt-remove-job-group" id="gnt-remove-job-group">
<?php
foreach ($all_groups as $group) {
    if ($group != user_manager::DEFAULT_GROUP)
        echo "       <option value=\"$group\">$group</option>\n";
}
?>
</select>
</div>




<script>

$(document).ready(function() {
    var estUpdateDlg = $("#est-update-group-dlg");
    var estRemoveDlg = $("#est-remove-group-dlg");
    var gntUpdateDlg = $("#gnt-update-group-dlg");
    var gntRemoveDlg = $("#gnt-remove-group-dlg");

    var defaultHandler = function(json) {
        if (json.valid) {
            window.location = "manage_jobs.php";
        }
    };

    var estUpdateGroupFn = function() { submitUpdateJobGroup(defaultHandler, "est", 2); };
    var estRemoveGroupFn = function() { submitUpdateJobGroup(defaultHandler, "est", 1); };
    var gntUpdateGroupFn = function() { submitUpdateJobGroup(defaultHandler, "gnt", 2); };
    var gntRemoveGroupFn = function() { submitUpdateJobGroup(defaultHandler, "gnt", 1); };

    estUpdateDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": estUpdateGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    estRemoveDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": estRemoveGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    gntUpdateDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": gntUpdateGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });
    gntRemoveDlg.dialog({resizeable: false, draggable: false, autoOpen: false, height: 300, width: 400,
        buttons: { "Ok": gntRemoveGroupFn, "Cancel": function() { $(this).dialog("close"); } }
    });

    $("#est-update-group-btn").click(function() { estUpdateDlg.dialog("open"); });
    $("#est-remove-group-btn").click(function() { estRemoveDlg.dialog("open"); });
    $("#gnt-update-group-btn").click(function() { gntUpdateDlg.dialog("open"); });
    $("#gnt-remove-group-btn").click(function() { gntRemoveDlg.dialog("open"); });
});

</script>


<?php require_once("inc/footer.inc.php"); ?>


