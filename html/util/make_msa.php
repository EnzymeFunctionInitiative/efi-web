<?php

require_once(__DIR__."/../../init.php");
require_once(__DIR__."/../../conf/settings_utils.inc.php");
require_once(__DIR__."/../../libs/efi/send_file.class.inc.php");

use efi\send_file;

$temp_dir = __MSA_TEMP_DIR__;
$parse_script = __MSA_PARSE_SCRIPT__;
$hmmsearch = __HMMSEARCH__;

$debug = 0;


if (isset($_FILES["hmm"]["tmp_name"]) && isset($_FILES["fasta"]["tmp_name"]) && $_FILES["hmm"]["error"] == 0 && $_FILES["fasta"]["error"] == 0) {
    $suffix = md5(rand());
    $suffix = substr($suffix, 0, 8);
    $temp_dir = "$temp_dir/$suffix";

    $file_info = pathinfo($_FILES["fasta"]["name"]);
    $fasta_file_basename = $file_info["basename"];

    if (preg_match("/[\/ ]/", $fasta_file_basename)) {
        die("Bad characters in fasta file upload");
    }

    $fasta_out_name = $file_info["filename"];
    $fasta_out_path = "$temp_dir/$fasta_out_name";
    $fasta_in = "$temp_dir/$fasta_file_basename";

    $msa_name = "$fasta_out_name.msa";
    $msa_path = "$temp_dir/$msa_name";
    $csv_name = "$msa_name.csv";
    $csv_path = "$temp_dir/$csv_name";

    $hmm_name = basename($_FILES["hmm"]["name"]);
    $hmm_in = "$temp_dir/$hmm_name";

    if (preg_match("/[\/ ]/", $hmm_name)) {
        die("Bad characters in hmm file upload");
    }

    mkdir($temp_dir);

    if (!move_uploaded_file($_FILES["hmm"]["tmp_name"], $hmm_in)) {
        die("Invalid hmm file upload");
    }
    if (!move_uploaded_file($_FILES["fasta"]["tmp_name"], $fasta_in)) {
        die("Invalid fasta file upload");
    }

    $unzip_hmm = "";
    if (preg_match("/\.gz$/", $hmm_in)) {
        $unzip_hmm = "gunzip $hmm_in";
        $hmm_in = preg_replace("/\.gz$/", "", $hmm_in);
    } else if (preg_match("/\.zip$/", $hmm_in)) {
        $unzip_hmm = "unzip $hmm_in";
        $hmm_in = preg_replace("/\.zip$/", "", $hmm_in);
    }

    $unzip_fasta = "";
    if (preg_match("/\.gz$/", $fasta_in)) {
        $unzip_fasta = "gunzip $fasta_in";
        $fasta_in = preg_replace("/\.gz$/", "", $fasta_in);
    } else if (preg_match("/\.zip$/", $fasta_in)) {
        $unzip_fasta = "unzip $fasta_in";
        $fasta_in = preg_replace("/\.zip$/", "", $fasta_in);
    }

    $hmmsearch_cmd = "$hmmsearch -o $fasta_out_path.txt -A $msa_path --tblout $fasta_out_path.table --domtblout $fasta_out_path.domtable $hmm_in $fasta_in";
    $parse_cmd = "$parse_script --input $msa_path --output $csv_path --csv > $csv_path.multi";

    if ($debug) {
        print <<<DEBUG
<pre>
$unzip_hmm
$unzip_fasta
$hmmsearch_cmd 
$parse_cmd
DEBUG;
        rrmdir($temp_dir, 1);
        print "</pre>\n";
    } else {
        $script = "$temp_dir/run.sh";
        $contents = <<<SCRIPT
#!/bin/bash
$unzip_hmm
$unzip_fasta
$hmmsearch_cmd 
$parse_cmd
echo "$csv_name" > $temp_dir/name
touch $temp_dir/done
SCRIPT;
        file_put_contents($script, $contents);

        file_put_contents("$temp_dir/name", $csv_name);

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

function writeDownloadHtml() {
    var id = "<?php echo $suffix; ?>";
    document.getElementById("wait").innerHTML = '<a href="<?php echo "temp/$suffix/$csv_name"; ?>"><?php echo $csv_name; ?></a><br><br><a href="make_msa.php"><button>Start over</button></a>';
    window.document.title = "Results are available";
}

var checkFn = function() {
    var id = "<?php echo $suffix; ?>";
    var ajax = new XMLHttpRequest();
    var url = "check_msa.php?id=" + id;
    ajax.open("GET", url, true);
    ajax.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == "1") {
                //window.location = "check_msa.php?id=" + id + "&dl=1";
                writeDownloadHtml();
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
        #send_file::send($csv_path, $csv_name);
        #rrmdir($temp_dir, 0);
    }

} else {

?>

<html>
<head>
<title>MSA creator</title>
<style>
div { margin: 20px; }
body { font-size: 1.2em; }
</style>
</head>
<body>

<form enctype="multipart/form-data" action="make_msa.php" method="POST">

<div>
HMM:<br>
<input type="file" name="hmm" />
</div>

<div>
FASTA:<br>
<input type="file" name="fasta" />
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



