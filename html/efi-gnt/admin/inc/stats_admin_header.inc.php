<!DOCTYPE html>
<html lang='en'>
<head>
<title>EFI-GNT Statistics</title>
<script type="text/javascript" src="<?php echo $SiteUrlPrefix; ?>/js/jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/font-awesome/css/fontawesome-all.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/bs/css/bootstrap.min.css">
<style>
.running { font-weight: bold; color: green; }
.failed { font-weight: bold; color: red; }
.cancelled { color: orange; }
.completed { }
td.file-col {
    max-width: 450px;
    overflow-wrap: break-word;
}
</style>
</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class='container-fluid'>
        <div class='navbar-header'>
            <a class='navbar-brand' href='../'><?php echo __TITLE__; ?></a>
        </div>  
        <div id='navbar' class='collapse navbar-collapse'>
            <ul class='nav navbar-nav'>
	   	        <li><a href='index.php'>Statistics</a></li>
                <li><a href='jobs.php?job-type=gnt'>Jobs</a></li>
                <li><a href='jobs.php?job-type=diagram'>Diagrams</a></li>
            </ul>
        </div>
	</div>
</nav>

<div class='container-fluid'>

