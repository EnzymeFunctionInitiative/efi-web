<?php




?>


<html>
<style>
.color {
width: 200px;
height: 50px;
}
</style>
<body>
<?php
if (false) {//!isset($_POST["colors"])) {
?>
<form action="" method="POST">
<textarea name="colors" rows="20" cols="80">
</textarea>
<br>
<button type="submit">Submit</button>
</form>
<?php
} else {
    //    $colors = preg_split('/[\r\n]+/', $_POST["colors"]);
    $lines = file("/home/groups/efi/databases/support/colors.tab");
    for ($i = 0; $i < count($lines); $i++) {
        $parts = explode("\t", $lines[$i]);
        echo "<div class='color' style='background-color: $parts[1]'>$parts[0] $parts[1] </div>";
    }
}

?>
</body>
</html>

