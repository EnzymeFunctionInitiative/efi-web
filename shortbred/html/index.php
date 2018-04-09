<?php

require_once "../../libs/ui.class.inc.php";
require_once "../includes/main.inc.php";

$userEmail = "Enter your e-mail address";

$IsLoggedIn = false;
$showPreviousJobs = false;
$batchJobs = array();
$userGroups = array();
$IsAdminUser = false;

//if (settings::is_recent_jobs_enabled() && user_jobs::has_token_cookie()) {
//    $userJobs = new user_jobs();
//    $userJobs->load_jobs($db, user_jobs::get_user_token());
//    $batchJobs = $userJobs->get_jobs();
//    $jobEmail = $userJobs->get_email();
//    if ($jobEmail)
//        $userEmail = $jobEmail;
//    $showPreviousJobs = count($batchJobs) > 0;
//    if ($userEmail)
//        $IsLoggedIn = $userEmail;
//    $userGroups = $userJobs->get_groups();
//    $IsAdminUser = $userJobs->is_admin();
//}

$showJobGroups = $IsAdminUser && global_settings::get_job_groups_enabled();

$updateMessage = functions::get_update_message();

require_once "inc/header.inc.php";

?>


<p></p>
<p>
</p>

<div id="update-message" class="update_message initial-hidden">
<?php if (isset($updateMessage)) echo $updateMessage; ?>
</div>

<!--A listing of new features and other information pertaining to GNT is available on the <a href="notes.php">release notes page</a>. -->

<div class="tabs">
    <ul class="tab-headers">
<?php if ($showPreviousJobs) { ?>
        <li class="active"><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li><a href="#create">Run ShortBRED</a></li>
        <li <?php if (! $showPreviousJobs) echo "class=\"active\""; ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div class="tab-content">
<?php if ($showPreviousJobs) { ?>
        <div id="jobs" class="tab active">
<?php } ?>
<?php if (count($batchJobs) > 0) { ?>
            <h4>ShortBRED Jobs</h4>
            <table class="pretty">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
for ($i = 0; $i < count($batchJobs); $i++) {
    $key = $batchJobs[$i]["key"];
    $id = $batchJobs[$i]["id"];
    $name = $batchJobs[$i]["filename"];
    $dateCompleted = $batchJobs[$i]["completed"];
    $isActive = $dateCompleted == "PENDING" || $dateCompleted == "RUNNING";

    $linkStart = $isActive ? "" : "<a href=\"stepc.php?id=$id&key=$key\">";
    $linkEnd = $isActive ? "" : "</a>";

    if (array_key_exists("diagram", $batchJobs[$i]))
        $linkStart = "<a href=\"stepc.php?upload-id=$id&key=$key\">";

    echo <<<HTML
                    <tr>
                        <td>$linkStart${id}$linkEnd</td>
                        <td>$linkStart${name}$linkEnd</td>
                        <td>$dateCompleted</td>
                    </tr>
HTML;
}
?>
                </tbody>
            </table>
<?php } ?>
            
<?php if ($showPreviousJobs) { ?>
        </div>
<?php } ?>

        <div id="create" class="tab">
            <p>
            <strong class="blue">Upload the Sequence Similarity Network (SSN) for which you want to run ShortBRED.</strong>
            </p>
    
            <p>
            </p>
    
            <form name="upload_form" id='upload_form' method="post" action="" enctype="multipart/form-data">
    
                <p>
                <?php echo ui::make_upload_box("<b>Select a File to Upload:</b><br>", "ssn_file", "progress_bar", "progress_number", "The acceptable format is uncompressed or zipped xgmml.", $SiteUrlPrefix); ?>
                </p>
    
<?php showAdminCode("ssn_job_group", $userGroups, $showJobGroups); ?>
                <p>
                    E-mail address: 
                    <input name='ssn_email' id='ssn_email' type="text" value="<?php echo $userEmail; ?>" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;"><br>
                    When the file has been uploaded and processed, you will receive an e-mail containing a link
                    to download the data.
                </p>
    
                <div id='ssn_message' style="color: red">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" id='ssn_submit' name="ssn_submit" class="dark"
                            onclick="uploadFile('ssn_file','upload_form','progress_number','progress_bar','ssn_message','ssn_email','ssn_submit','ssn_job_group')">
                                Upload SSN
                        </button></div>
                    <div><progress id='progress_bar' max='100' value='0'></progress></div>
                    <div id="progress_number"></div>
                </center>
            </form>
        </div>

        <div id="tutorial" class="tab <?php if (!$showPreviousJobs) echo "active"; ?>">
            <h3>ShortBRED Tool Overview</h3>
    
            <p>
            </p>
    
            <p>
            </p>
    
            <h3>ShortBRED acceptable input</h3>
    
            <p>
            The sequence datasets are generated from an SSN produced by the EFI-Enzyme Similarity Tool (EFI-EST).
<!--Acceptable 
            SSNs are generated for an entire Pfam and/or InterPro protein family (from Option B of EFI-EST), a focused region of a 
            family (from Option A of EFI-EST), a set of protein sequence that can be identified from FASTA headers (from option C of 
            EFI-EST with header reading) or a list of recognizable UniProt and/or NCBI IDs (from option D of EFI-EST). A manually 
            modified SSN within Cytoscape that originated from any of the EST options is also acceptable. SSNs that have been 
            colored using the "Color SSN Utility" of EFI-EST and that originated from any of acceptable Options are also acceptable.
-->
            </p>
    
            <h3>Principle of ShortBRED</h3>
    
            <p>
            </p>
    
            <h3>ShortBRED output</h3>
    
            <p>
            </p>
    
            <p>
            </p>
    
            <p>
            </p>
    
            <!--<p class="center"><a href='tutorial.php'><button class="light">Continue Tutorial</button></a></p>-->
    
        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->


<div align="center">
    <?php if (settings::is_beta_release()) { ?>
    <h4><b><span style="color: red">BETA</span></b></h4>
    <?php } ?>

    <p>
    UniProt Version: <b><?php echo settings::get_uniprot_version(); ?></b><br>
    </p>
</div>

<script>
    $(document).ready(function() {
        $(".tabs .tab-headers a").on("click", function(e) {
            var curAttrValue = $(this).attr("href");
            $(".tabs " + curAttrValue).fadeIn(300).show().siblings().hide();
            $(this).parent("li").addClass("active").siblings().removeClass("active");
            e.preventDefault();
        });

        $(".advanced-toggle").click(function () {
            $header = $(this);
            //getting the next element
            $content = $header.next();
            //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
            $content.slideToggle(100, function () {
                if ($content.is(":visible")) {
                    $header.find("i.fa").addClass("fa-minus-square");
                    $header.find("i.fa").removeClass("fa-plus-square");
                } else {
                    $header.find("i.fa").removeClass("fa-minus-square");
                    $header.find("i.fa").addClass("fa-plus-square");
                }
            });
        
        });
    });
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>

<?php require_once('inc/footer.inc.php'); ?>


<?php

function showAdminCode($id, $userGroups, $showJobGroups) {
    if (!$showJobGroups)
        return;

    $func = function($val) { return "<option>$val</option>"; };
    $groupList = implode("", array_map($func, $userGroups));
    if ($groupList) {
        echo <<<HTML
<div style="margin:20px 0">
    Optional job group: <select id="$id">$groupList</select>
</div>
HTML;
    }
}

?>

