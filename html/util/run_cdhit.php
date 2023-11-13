<?php

require_once(__DIR__."/../../init.php");
require_once(__DIR__."/../../conf/settings_utils.inc.php");
require_once(__DIR__."/../../libs/efi/send_file.class.inc.php");

if (!defined("__ENABLE_ADVANCED_OPTIONS__") || !__ENABLE_ADVANCED_OPTIONS__) {
    exit(1);
}

use efi\send_file;

$temp_dir = __MSA_TEMP_DIR__;
$cd_hit = __CD_HIT__;

$debug = 0;


if (isset($_FILES["fasta"]["tmp_name"]) && $_FILES["fasta"]["error"] == 0) {
    $suffix = md5(rand());
    $suffix = substr($suffix, 0, 8);
    $temp_dir = "$temp_dir/$suffix";

    $fasta_in_name = $_FILES["fasta"]["name"];
    $file_info = pathinfo($fasta_in_name);
    $fasta_file_basename = $file_info["basename"];
    $fasta_in = "$temp_dir/$fasta_file_basename";

    if (preg_match("/[\/ ]/", $fasta_file_basename)) {
        die("Bad characters in fasta file upload");
    }

    $pid = floatval($_POST["pid"]) / 100;
    $len = floatval($_POST["len"]) / 100;

    $fasta_out_name = $file_info["filename"] . "_ov${pid}_sid${len}.fasta";
    $fasta_out_path = "$temp_dir/$fasta_out_name";
    $info_name = $file_info["filename"] . "_ov${pid}_sid${len}.txt";
    $info_path = "$temp_dir/$info_name";

    mkdir($temp_dir);

    if (!move_uploaded_file($_FILES["fasta"]["tmp_name"], $fasta_in)) {
        die("Invalid fasta file upload");
    }

    $unzip_fasta = "";
    if (preg_match("/\.gz$/", $fasta_in)) {
        $unzip_fasta = "gunzip $fasta_in";
        $fasta_in = preg_replace("/\.gz$/", "", $fasta_in);
    } else if (preg_match("/\.zip$/", $fasta_in)) {
        $unzip_fasta = "unzip $fasta_in";
        $fasta_in = preg_replace("/\.zip$/", "", $fasta_in);
    }

    $ncore = 4;
    $cdhit_cmd = "$cd_hit -d 0 -T $ncore -n 2 -s $pid -c $len -i $fasta_in -o $fasta_out_path > $info_path";

    file_put_contents("$temp_dir/params", "in_pid=" . $_POST["pid"] . "\nin_len=" . $_POST["len"] . "\nout_pid=$pid\nout_len=$len\n\n$cdhit_cmd\n");

    if ($debug) {
        print <<<DEBUG
<pre>
$unzip_fasta
$cdhit_cmd
DEBUG;
        rrmdir($temp_dir, 1);
        print "</pre>\n";
    } else {
        $script = "$temp_dir/run.sh";
        $contents = <<<SCRIPT
#!/bin/bash
$unzip_fasta
$cdhit_cmd
touch $temp_dir/done
SCRIPT;
        file_put_contents($script, $contents);

        file_put_contents("$temp_dir/name", $info_name);

        $output = array();
        $res_code = 0;
        $status = exec("/bin/bash $script > /dev/null 2>/dev/null &", $output, $res_code);

?>
<html>
<head>
<title>Waiting for results...</title>
<style>
div { margin: 20px; }
body { font-size: 1.2em; }
</style>
</head>
<body>
Please wait (<?php echo $suffix; ?>)...
<div id="wait">
</div>
<script>

function writeDownloadHtml(resCode) {
    var id = "<?php echo $suffix; ?>";
    var inFileSize = "<?php echo get_file_size("$temp_dir/$fasta_in_name"); ?>";
    var outFileSize = resCode;
    document.getElementById("wait").innerHTML = 'Original: <a href="<?php echo "temp/$suffix/$fasta_in_name"; ?>"><?php echo $fasta_in_name; ?></a> [' + inFileSize + ']<br><br>';
    document.getElementById("wait").innerHTML += 'CD-HIT FASTA: <a href="<?php echo "temp/$suffix/$fasta_out_name"; ?>"><?php echo $fasta_out_name; ?></a> [' + outFileSize + ']<br><br>';
    document.getElementById("wait").innerHTML += 'CD-HIT Info: <a href="<?php echo "temp/$suffix/$info_name"; ?>"><?php echo $info_name; ?></a><br><br><a href="run_cdhit.php"><button>Start over</button></a>';
    window.document.title = "Results are available";
}

var checkFn = function() {
    var id = "<?php echo $suffix; ?>";
    var ajax = new XMLHttpRequest();
    var url = "check_msa.php?sz=1&id=" + id;
    ajax.open("GET", url, true);
    ajax.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText != "0") {
                //window.location = "check_msa.php?id=" + id + "&dl=1";
                writeDownloadHtml(this.responseText);
            } else {
                document.getElementById("wait").innerHTML += "...<br>";
                setTimeout(checkFn, 5000);
            }
        }
    };
    ajax.send();
};

setTimeout(checkFn, 5000);

</script>

</body>
</html>
<?php
    }

} else {

?>

<html>
<head>
<title>Run CD-HIT</title>
<style>
div { margin: 20px; }
body { font-size: 1.2em; }
</style>
</head>
<body>

<form enctype="multipart/form-data" action="run_cdhit.php" method="POST">

<div>
FASTA:<br>
<input type="file" name="fasta" />
</div>

<div>
Percent Identity (%):<br>
<input type="text" name="pid" />
</div>

<div>
Sequence Length (%):<br>
<input type="text" name="len" />
</div>

<div>
<input type="submit" value="Submit" />
</div>

</form>

</body>
</html>


<?php
}



function rrmdir($dir_path, $is_debug = 0) {
    $files = array_diff(scandir($dir_path), array('.', '..'));
    foreach ($files as $file) {
        if ($is_debug) { print "delete $dir_path/$file\n"; }
        else { unlink("$dir_path/$file"); }
    }
    if ($is_debug) { print "rmdir($dir_path);\n"; }
    else { rmdir($dir_path); }
}


function get_file_size($file_path) {
    $bytes = filesize($file_path);
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0) $sz = 'KMGT';
    $decimals = 1;
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
}

