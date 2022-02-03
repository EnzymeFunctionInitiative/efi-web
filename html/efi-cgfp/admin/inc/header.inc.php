<!DOCTYPE html>
<html lang='en'>
<head>
<title>EFI/ShortBRED Statistics</title>
    <script type="text/javascript" src="../../vendor/components/jquery/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="../../vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
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
            <a class='navbar-brand' href='../'>EFI-CGFP</a>
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

