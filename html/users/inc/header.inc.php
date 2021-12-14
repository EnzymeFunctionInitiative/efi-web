<?php
require_once(__DIR__."/../../../conf/settings_paths.inc.php");

$title = "Enzyme Function Initiative";

require_once(__BASE_DIR__."/html/inc/global_login_button.inc.php");
require_once(__BASE_DIR__."/includes/login_check.inc.php");

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
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/vendor/components/jqueryui/themes/base/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/shared.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/tabs.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/vendor/fortawesome/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>

    <script src="<?php echo $SiteUrlPrefix; ?>/js/login.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/vendor/components/jquery/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo $SiteUrlPrefix; ?>/vendor/components/jqueryui/jquery-ui.js" type="text/javascript"></script>
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
            </ul>
        </div>
    </div>

    <div id="container">

        <div class="content-holder">
            <h1 class="ruled">EFI Training and User Tools</h1>
            <?php echo $cur_page_title; ?>

