

<!doctype html>
<html>
<head>
    <title>ID list</title>
</head>

<body>

<h2>Results</h2>

<textarea id="output" rows="50" cols="30">
<?php

if (!isset($_POST["list"])) {
    die("Required parameter not present");
}

$fraction = $_POST["fraction"];

$list = preg_replace("/ /", "", $_POST["list"]);
$list = preg_replace("/\r\n|\n|\r/", ",", $list);
$lines = preg_split("/,/", $list, -1, PREG_SPLIT_NO_EMPTY);

$taken = 0;
for ($i = 0; $i < count($lines); $i++) {
    if (!$lines[$i])
        continue;
    if (!($i % $fraction)) {
        echo $lines[$i] . "\n";
        $taken++;
    }
}

?>
</textarea>
<br>
<?php echo "$taken IDs left"; ?><br>
<button type="button" id="copy" onclick="copyList()">Copy to clipboard</button>

<script>
function copyList() {
    var copyField = document.getElementById("output");
    copyField.select();
    document.execCommand("copy");
    copyField.selectionEnd = copyField.selectionStart;
    copyField.blur();
}
</script>

</body>

</html>


