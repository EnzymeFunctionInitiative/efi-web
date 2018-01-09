<!DOCTYPE html>
<html lang='en'>
<header>
<title>EFI-EST Statistics</title>
<link rel="stylesheet" type="text/css"
        <?php if (file_exists("../includes/bootstrap-3.3.5-dist/css/bootstrap.min.css")) {
                echo "href='../includes/bootstrap-3.3.5-dist/css/bootstrap.min.css'>";
        }
        elseif (file_exists("includes/bootstrap-3.3.5-dist/css/bootstrap.min.css")) {
                echo "href='includes/bootstrap-3.3.5-dist/css/bootstrap.min.css'>";
        }
        ?>

</header>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
        <div class='container'>

                <div class='navbar-header'>
                        <a class='navbar-brand' href='#'><?php echo __TITLE__; ?> Statistics</a>
                </div>
        </div>
</nav>

<div class='container'>
