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
        <div class='navbar-header'>
            <a class='navbar-brand' href='../'><?php echo __TITLE__; ?></a>
        </div>    
        <div id='navbar' class='collapse navbar-collapse'>
            <ul class='nav navbar-nav'>
                <li><a href='index.php'>Generate Stats</a></li>
                <li><a href='index.php?job-type=analysis'>Analysis Stats</a></li>
                <li><a href='jobs.php?job-type=generate'>Generate Jobs</a></li>
                <li><a href='jobs.php?job-type=analysis'>Analysis Jobs</a></li>
                <li><a href='queue.php'>Queue Status<?php echo $queue_text; ?></a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='container-fluid'>
<div class='span12'>

