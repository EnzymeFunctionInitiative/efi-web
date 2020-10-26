<!DOCTYPE html>
<html lang='en'>
<head>
<title>EFI/ShortBRED Statistics</title>
    <script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="/bs/css/bootstrap.min.css">
<style>
.running { font-weight: bold; color: green; }
.failed { font-weight: bold; color: red; }
.cancelled { color: orange; }
.completed { }
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
                <li><a href='index.php'>Main</a></li>
                <li><a href='jobs.php?job-type=identify'>Identify Jobs</a></li>
                <li><a href='jobs.php?job-type=quantify'>Quantify Jobs</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class='container-fluid'>

