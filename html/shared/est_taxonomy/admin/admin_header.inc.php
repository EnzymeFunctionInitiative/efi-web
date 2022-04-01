<?php
$queue_text = "";
if (isset($NumWaitingJobs) && isset($NumRunningJobs)) {
    $queue_text = " (NEW $NumWaitingJobs/RUN $NumRunningJobs)";
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
<title>EFI-EST Statistics</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <script type="text/javascript" src="<?php echo $SiteUrlPrefix; ?>/vendor/components/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $SiteUrlPrefix; ?>/shared/est_taxonomy/admin/stats_nav.js"></script>
<style>
.running { font-weight: bold; color: green; }
.failed { font-weight: bold; color: red; }
.cancelled { color: orange; }
.completed { }
/*.families-col {
    max-width: 200px;
    overflow: auto;
    text-overflow: ellipsis;
}
.email-col {
    max-width: 200px;
    overflow: auto;
    text-overflow: ellipsis;
}*/
</style>
</head>

<body style='padding-top: 60px;'>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class='container-fluid'>
<?php include("inc/header_nav.inc.php"); ?>
    </div>
</nav>

<div class='container-fluid'>
<div class='span12'>

