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

$db_modules = global_settings::get_database_modules();

$showJobGroups = $IsAdminUser && global_settings::get_job_groups_enabled();

$update_message = functions::get_update_message();

require_once "inc/header.inc.php";

?>


<p></p>
<p>
</p>

<?php if ($update_message) { ?>
<div id="update-message" class="update_message initial-hidden">
<?php echo $update_message; ?>
</div>
<?php } ?>

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
$allow_cancel = true;
show_jobs($jobs, $allow_cancel);
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
$allow_cancel = false;
show_jobs($training_jobs, $allow_cancel);
?>
                </tbody>
            </table>
<?php } ?>
            
<?php if ($show_previous_jobs) { ?>
        </div>
<?php } ?>

        <div id="create" class="tab">
            <p>
            <strong class="blue">Upload the Sequence Similarity Network (SSN) for which you want to run CGFP/ShortBRED.</strong>
            </p>
    
            <p>
            </p>
    
            <form name="upload_form" id="upload_form" method="post" action="" enctype="multipart/form-data">
    
                <p>
                <?php echo ui::make_upload_box("<b>Select a File to Upload:</b><br>", "ssn_file", "progress_bar", "progress_number", "The acceptable format is uncompressed or zipped xgmml.", $SiteUrlPrefix); ?>
                </p>

                <div class="advanced-toggle">Advanced Options <i class="fas fa-plus-square" aria-hidden="true"></i></div>
                <div style="display:none;" class="advanced-options">
                    <div>
                        Minimum sequence length (default none): <input name="ssn_min_seq_len" id="ssn_min_seq_len" type="text" />
                        <a class="question" title="If the uploaded SSN was generated using the UniRef90 option in EST, then it is helpful to specify a minimum sequence length, in order to eliminate fragments that may be included in UniRef90 clusters.">?</a>
                    </div>
                    <div>
                        Maximum sequence length (default none): <input name="ssn_max_seq_len" id="ssn_max_seq_len" type="text" />
                        <a class="question" title="If the uploaded SSN was generated using the UniRef90 option in EST, then it is helpful to specify a maximum sequence length, in order to eliminate certain sequences that may be included in UniRef90 clusters.">?</a>
                    </div>
                    <div>
                        Reference database: <select name="ssn_ref_db" id="ssn_ref_db"><option value="uniprot">Full UniProt</option><option value="uniref90" selected>UniRef 90</option><option value="uniref50">UniRef 50</option></select>
                        <a class="question" title="ShortBRED can use the full UniProt database or UniRef90 or UniRef50 to determine markers. The default is UniRef90.">?</a>
                    </div>
                    <div>
                        CD-HIT sequence identity (default 85%): <input type="text" name="ssn_cdhit_sid" id="ssn_cdhit_sid" value="">
                        <a class="question" title="This is the sequence identity parameter that will be used for determining the ShortBRED consensus sequence families.">?</a>
                    </div>

<?php if (settings::get_diamond_enabled()) { ?>
                    <div>
                        Sequence search type: <select name="ssn_search_type" id="ssn_search_type"><option>BLAST</option><option selected>DIAMOND</option></select>
                        <a class="question" title="This is the search engine that will be used to remove false positives and identify unique markers.">?</a>
                    </div>

                    <input type="hidden" name="ssn_diamond_sens" id="ssn_diamond_sens" value="normal" />
                    <!--<div>
                        DIAMOND sensitivity: <select name="ssn_diamond_sens" id="ssn_diamond_sens"><option>sensitive</option><option>more-sensitive</option><option selected>normal</option></select>
                        <a class="question" title="This is the sentivitiy parameter that DIAMOND will use in its computations.  It defaults to sensitive in ShortBRED.">?</a>
                    </div>-->
<?php } ?>
<?php
if (count($db_modules) > 1) {
    echo <<<HTML
                    <div>
                        Database version: 
                        <select name="ssn_db_mod" id="ssn_db_mod">
HTML;
    foreach ($db_modules as $mod_info) {
        $mod_name = $mod_info[1];
        echo "                            <option value=\"$mod_name\">$mod_name</option>\n";
    }
    echo "                        </select></div>";
} ?>
                </div>

                <p>
                    E-mail address: 
                    <input name="ssn_email" id="ssn_email" type="text" value="<?php echo $user_email; ?>" class="email" onfocus="if(!this._haschanged){this.value=''};this._haschanged=true;"><br>
                    When the file has been uploaded and processed, you will receive an e-mail containing a link
                    to download the data.
                </p>
    
                <div id="ssn_message" style="color: red">
                    <?php if (isset($message)) { echo "<h4 class='center'>" . $message . "</h4>"; } ?>
                </div>
                <center>
                    <div><button type="button" id="ssn_submit" name="ssn_submit" class="dark" onclick="uploadInitialSSNFile()">
                                Upload SSN
                        </button></div>
                    <div><progress id="progress_bar" max="100" value="0"></progress></div>
                    <div id="progress_number"></div>
                </center>
            </form>
        </div>

        <div id="tutorial" class="tab <?php if (!$show_previous_jobs) echo "active"; ?>">
<?php include("tutorial.inc.php"); ?>
        </div>
    </div> <!-- tab-content -->
</div> <!-- tabs -->

<div id="cancel-confirm" title="Cancel the job?" style="display: none">
<p>
<span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>
All progress will be lost.
</p>    
</div>

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

        $(".cancel-btn").click(function() {
            var id = $(this).data("id");
            var key = $(this).data("key");
            var qid = $(this).data("quantify-id");
            if (!qid)
                qid = "";

            $("#cancel-confirm").dialog({
                resizable: false,
                height: "auto",
                width: 400,
                modal: true,
                buttons: {
                    "Stop Job": function() {
                        requestCancellation(id, key, qid);
                        $( this ).dialog("close");
                    },
                    Cancel: function() {
                        $( this ).dialog("close");
                    }
                }
            });

//            $(this).appendTo('<div id="cancel-menu" class="speech-bubble cancel-bubble">Cancel</div>');
        });

    });
</script>
<script src="<?php echo $SiteUrlPrefix; ?>/js/custom-file-input.js" type="text/javascript"></script>


<?php

function show_jobs($jobs, $allow_cancel) {
    $last_bg_color = "#eee";
    for ($i = 0; $i < count($jobs); $i++) {
        $key = $jobs[$i]["key"];
        $id = $jobs[$i]["id"];
        $name = $jobs[$i]["job_name"];
        $is_completed = $jobs[$i]["is_completed"];
        $date_completed = $jobs[$i]["date_completed"];
        $is_active = $date_completed == "PENDING" || $date_completed == "RUNNING";
        $search_type = $jobs[$i]["search_type"];
        $ref_db = $jobs[$i]["ref_db"];
    
        $link_start = "";
        $link_end = "";
        $name_style = "";
        $id_field = $id;
        $quantify_id = "";
    
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
            $name = "[Quantify $quantify_id] " . $name;
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
            $name .= " /$search_type/";
        if ($ref_db)
            $name .= " &lt;$ref_db&gt;";

        $job_action_code = "";
        if ($is_active && $allow_cancel) {
            $job_action_code = '<i class="fas fa-stop-circle cancel-btn" title="Cancel Job" data-id="' . $id . '" data-key="' . $key . '"';
            if ($quantify_id)
                $job_action_code .= ' data-quantify-id="' . $quantify_id . '"';
            $job_action_code .= '></i>';
        }

        echo <<<HTML
                    <tr style="background-color: $last_bg_color">
                        <td>$link_start${id_field}$link_end</td>
                        <td $name_style>$link_start${name}$link_end</td>
                        <td>$date_completed $job_action_code</td>
                    </tr>
HTML;
    }
}


require_once("inc/footer.inc.php");


?>

