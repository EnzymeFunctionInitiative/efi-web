<?php

require_once "../../libs/ui.class.inc.php";
require_once "../includes/main.inc.php";
require_once "../../libs/user_auth.class.inc.php";

$user_email = "Enter your e-mail address";

$IsLoggedIn = false;
$show_previous_jobs = false;
$jobs = array();
$training_jobs = array();
$IsAdminUser = false;

if (settings::is_recent_jobs_enabled() && user_auth::has_token_cookie()) {
    $user_token = user_auth::get_user_token();
    $user_email = user_auth::get_email_from_token($db, $user_token);
    $IsAdminUser = user_auth::get_user_admin($db, $user_email);

    $job_manager = new job_manager($db, job_types::Identify);
    $jobs = $job_manager->get_jobs_by_user($user_token);
    $training_jobs = $job_manager->get_training_jobs($user_token);
    $show_previous_jobs = count($jobs) > 0 || count($training_jobs) > 0;

    if ($user_email)
        $IsLoggedIn = $user_email;
}

$showJobGroups = $IsAdminUser && global_settings::get_job_groups_enabled();

$update_message = functions::get_update_message();

require_once "inc/header.inc.php";

?>


<p></p>
<p>
</p>

<div id="update-message" class="update_message initial-hidden">
<?php if (isset($update_message)) echo $update_message; ?>
</div>

<!--A listing of new features and other information pertaining to GNT is available on the <a href="notes.php">release notes page</a>. -->

<div class="tabs">
    <ul class="tab-headers">
<?php if ($show_previous_jobs) { ?>
        <li class="active"><a href="#jobs">Previous Jobs</a></li>
<?php } ?>
        <li><a href="#create">Run CGFP/ShortBRED</a></li>
        <li <?php if (! $show_previous_jobs) echo "class=\"active\""; ?>><a href="#tutorial">Tutorial</a></li>
    </ul>

    <div class="tab-content">
<?php if ($show_previous_jobs) { ?>
        <div id="jobs" class="tab active">
<?php } ?>
<?php if (count($jobs) > 0) { ?>
            <h4>ShortBRED Jobs</h4>
            <table class="pretty_nested">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
show_jobs($jobs);
?>
                </tbody>
            </table>
<?php } ?>
            
<?php if (count($training_jobs) > 0) { ?>
            <h4>Training Jobs</h4>
            <table class="pretty_nested">
                <thead>
                    <th class="id-col">ID</th>
                    <th>Filename</th>
                    <th class="date-col">Date Completed</th>
                </thead>
                <tbody>
<?php
show_jobs($training_jobs);
?>
                </tbody>
            </table>
<?php } ?>
            
<?php if ($show_previous_jobs) { ?>
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

                <p>
                    Minimum sequence length: <input name="ssn_min_seq_len" id="ssn_min_seq_len" type="text" /> (Optional)
                    <a class="question" title="If the uploaded SSN was generated using the UniRef90 option in EST, then it is helpful to specify a minimum sequence length, in order to eliminate fragments that may be included in UniRef90 clusters.">?</a>
                </p>
    
<?php if (settings::get_diamond_enabled()) { ?>
                <p>
                    Sequence search type: <select name="ssn_search_type" id="ssn_search_type"><option>BLAST</option><option>DIAMOND</option></select> (Optional)
                </p>
<?php } ?>
    
                <p>
                    E-mail address: 
                    <input name="ssn_email" id="ssn_email" type="text" value="<?php echo $user_email; ?>" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;"><br>
                    When the file has been uploaded and processed, you will receive an e-mail containing a link
                    to download the data.
                </p>
    
                <div id='ssn_message' style="color: red">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" id='ssn_submit' name="ssn_submit" class="dark"
                            onclick="uploadFile('ssn_file','upload_form','progress_number','progress_bar','ssn_message','ssn_email','ssn_submit','ssn_min_seq_len','ssn_search_type')">
                                Upload SSN
                        </button></div>
                    <div><progress id='progress_bar' max='100' value='0'></progress></div>
                    <div id="progress_number"></div>
                </center>
            </form>
        </div>

        <div id="tutorial" class="tab <?php if (!$show_previous_jobs) echo "active"; ?>">
<?php include("tutorial.inc.php"); ?>
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


<?php

function show_jobs($jobs) {
    $last_bg_color = "#eee";
    for ($i = 0; $i < count($jobs); $i++) {
        $key = $jobs[$i]["key"];
        $id = $jobs[$i]["id"];
        $name = $jobs[$i]["job_name"];
        $is_completed = $jobs[$i]["is_completed"];
        $date_completed = $jobs[$i]["date_completed"];
        $is_active = $date_completed == "PENDING" || $date_completed == "RUNNING";
        $search_type = $jobs[$i]["search_type"];
    
        $link_start = "";
        $link_end = "";
        $name_style = "";
        $id_field = $id;
    
        if ($jobs[$i]["is_quantify"]) {
            $quantify_id = $jobs[$i]["quantify_id"];
            $title_str = "title=\"" . $jobs[$i]["full_job_name"] . "\"";
            if ($is_completed) {
                $link_start = "<a href=\"stepe.php?id=$id&key=$key&quantify-id=$quantify_id\" $title_str>";
                $link_end = "</a>";
            } else {
                $link_start = "<span $title_str>";
                $link_end = "</span>";
            }
            $name_style = "style=\"padding-left: 50px;\"";
            $name = "[Quantify] " . $name;
            $id_field = "";
        } else {
            $link_start = $is_active ? "" : "<a href=\"stepc.php?id=$id&key=$key\">";
            $link_end = $is_active ? "" : "</a>";
            if ($last_bg_color == "#fff")
                $last_bg_color = "#eee";
            else
                $last_bg_color = "#fff";
        }
        if ($search_type)
            $name = $name . " /$search_type/";
    
        echo <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$link_start${id_field}$link_end</td>
                        <td $name_style>$link_start${name}$link_end</td>
                        <td>$date_completed</td>
                    </tr>
HTML;
    }
}


require_once('inc/footer.inc.php');


?>

