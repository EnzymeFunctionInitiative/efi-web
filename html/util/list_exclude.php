

<?php

$cola = $colb = $colc = "";

if (isset($_POST["cola"]) && isset($_POST["colb"])) {
    $cola = $_POST["cola"];
    $colb = $_POST["colb"];
    $rowa_split = preg_split("/\r\n|\n|\r/", $_POST["cola"]);
    $rowb_split = preg_split("/\r\n|\n|\r/", $_POST["colb"]);
    foreach ($rowa_split as $i => $v) {
        $rowa[$v] = $i;
    }
    foreach ($rowb_split as $i => $v) {
        $rowb[$v] = $i;
    }
    $rowc = array();
    foreach ($rowa as $k => $v) {
        if (!isset($rowb[$k]))
            array_push($rowc, $k);
    }
    $colc = implode("\n", $rowc);
}

?>
<!doctype html>
<html>
<head>
    <title>List Exclude</title>
<style>
div { margin: 20px; }
</style>
</head>

<body>

<form action="list_exclude.php" method="POST">

<div>
<div style="float: left;">
<h4>Column A - Items to Keep</h4>
<textarea name="cola" cols="20" rows="100"><?php echo $cola; ?></textarea>
</div>
<div style="float: left;">
<h4>Column B - Items to Remove from A</h4>
<textarea name="colb" cols="20" rows="100"><?php echo $colb; ?></textarea>
</div>
<div style="float: left; vertical-align: top;">
<h4>Column C: A - B</h4>
<textarea name="colc" id="colc" cols="20" rows="100"><?php echo $colc; ?></textarea>
<button type="button" onclick="copyList()" style="float: right">Copy</button>
</div>
</div>

<br><br>

<button type="submit">Submit</button>

</form>

<script>
    function copyList() {
        var el = document.getElementById("colc");
        el.select();
        document.execCommand('copy');
        clearSelection();
    }
function clearSelection() {
    var sel;
    if ( (sel = document.selection) && sel.empty ) {
        sel.empty();
    } else {
        if (window.getSelection) {
            window.getSelection().removeAllRanges();
        }
        var activeEl = document.activeElement;
        if (activeEl) {
            var tagName = activeEl.nodeName.toLowerCase();
            if ( tagName == "textarea" ||
                    (tagName == "input" && activeEl.type == "text") ) {
                // Collapse the selection to the end
                activeEl.selectionStart = activeEl.selectionEnd;
            }
        }
    }
}


</script>
</body>

</html>


