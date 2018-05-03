<?php

if (!isset($TopLevelUrl))
    $TopLevelUrl = "http://efi.igb.illinois.edu/users/";

$title = "Enzyme Function Initiative";
if (isset($GnnId))
    $title .= ": Job #$GnnId";

if (isset($Is404Page) && $Is404Page)
    $title = "Page Not Found";

if (isset($IsExpiredPage) && $IsExpiredPage)
    $title = "Expired Job";

include("../../includes/login_check.inc.php");
include("../../html/inc/global_login_button.inc.php");

$pages = array("index.php" => "User Home",
#               "create_user.php" => "Create Users",
               "manage_user.php" => "Manage Users",
#               "create_group.php" => "Create Groups",
               "manage_group.php" => "Manage Groups",
               "manage_jobs.php" => "Manage Training Jobs",
           );

$cur_page = basename($_SERVER['PHP_SELF']);

$cur_page_title = isset($pages[$cur_page]) ? "<h2>" . $pages[$cur_page] . "</h2>" : "";

?>


<!doctype html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/shared.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/js/jquery-ui-1.12.1/jquery-ui.js" type="text/javascript"></script>
    <script src="js/main.js" type="text/javascript"></script>
</head>

<body>
    <div class="system-nav-container">
        <div class="system-nav">
            <ul>
                <li><a href="<?php echo $SiteUrlPrefix; ?>/" class="est">EFI Home</a></li>
<?php
foreach ($pages as $page => $page_title) {
    $the_class = "";
    if ($page == $cur_page)
        $the_class = "class=\"user-mgmt\"";

    echo "                <li><a href=\"$page\" $the_class>$page_title</a></li>\n";
}
?>
<!--
                <li><a href="create_user.php">Create Users</a></li>
                <li><a href="manage_user.php">Manage Users</a></li>
                <li><a href="create_group.php">Create Groups</a></li>
                <li><a href="manage_group.php">Manage Groups</a></li>
                <li><a href="manage_jobs.php">Manage Training Jobs</a></li>
-->
            </ul>
        </div>
    </div>

    <div id="container">

        <div class="content_holder">
            <h1 class="ruled">EFI Training and User Tools</h1>
            <?php echo $cur_page_title; ?>

