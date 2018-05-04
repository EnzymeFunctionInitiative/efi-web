<?php
require_once("../includes/main.inc.php");
require_once("../../libs/user_auth.class.inc.php");
require_once("../../includes/login_check.inc.php");


//$group_mgr = new group_manager($db);
$user_mgr = new user_manager($db);
//$est_job_mgr = new job_manager($db, __EFI_EST_DB_NAME__, __EFI_EST_TABLE__);
//$gnn_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_GNN_TABLE__);
//$gnd_job_mgr = new job_manager($db, __EFI_GNT_DB_NAME__, __EFI_GNT_DIAGRAM_TABLE__);

$show_max_ids = 15;
$user_ids = $user_mgr->get_user_ids();
$group_names = $user_mgr->get_group_names();

$job_mgr = new job_manager($db, __MYSQL_AUTH_DATABASE__, __EFI_EST_DB_NAME__, __EFI_GNT_DB_NAME__);
$est_ids = $job_mgr->get_grouped_est_job_ids();
$gnt_ids = $job_mgr->get_grouped_gnt_job_ids();

require_once("inc/header.inc.php");

?>

<p style="margin-top: 30px">
</p>

<h3>Groups</h3>

<table class="pretty">
    <thead>
        <th>Group Name</th>
        <th>Status</th>
        <th>Time Open</th>
        <th>Time Closed</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < min($show_max_ids, count($group_names)); $i++) {
    $group_name = $group_names[$i];
    $group = $user_mgr->get_group($group_name);
    $group_status = $group["status"];
    $time_open = $group["time_open"];
    $time_closed = $group["time_closed"];
    echo "        <tr>\n";
    echo "            <td>$group_name</td>\n";
    echo "            <td>$group_status</td>\n";
    echo "            <td>$time_open</td>\n";
    echo "            <td>$time_closed</td>\n";
    echo "        </tr>\n";
}

?>
    </tbody>
</table>


<h3>Users</h3>

<table class="pretty">
    <thead>
        <th class="id-col">Email</th>
        <th>Group(s)</th>
        <th>Status</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < min($show_max_ids, count($user_ids)); $i++) {
    $user_id = $user_ids[$i];
    $user = $user_mgr->get_user($user_id);
    $user_email = $user["email"];
    $user_status = $user["status"];
    $groups = implode(", ", $user["group"]);
    echo "        <tr>\n";
    echo "            <td>$user_email</td>\n";
    echo "            <td>$groups</td>\n";
    echo "            <td>$user_status</td>\n";
    echo "        </tr>\n";
}

?>
    </tbody>
</table>

<?php if (count($user_ids) > $show_max_ids) { ?>
<a href="manage_group.php">View All</a>
<?php } ?>



<h3>Jobs</h3>

<h4>EST Jobs</h4>

<table class="pretty">
    <thead>
        <th>Generate ID</th>
        <th>Job Info</th>
        <th>Job Status</th>
        <th>Email Address</th>
        <th>Group(s)</th>
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
        </tr>

HTML;
}

?>
    </tbody>
</table>

<h4>GNT Jobs</h4>

<table class="pretty">
    <thead>
        <th>GNN ID</th>
        <th>Job Info</th>
        <th>Job Status</th>
        <th>Email Address</th>
        <th>Group(s)</th>
    </thead>
    <tbody>
<?php

for ($i = 0; $i < count($gnt_ids); $i++) {
    $id = $gnt_ids[$i];
    $job = $job_mgr->get_gnt_job_by_id($id);

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
        </tr>

HTML;
}

?>
    </tbody>
</table>



<?php require_once("inc/footer.inc.php"); ?>


