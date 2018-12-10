<?php
require_once "../libs/user_jobs.class.inc.php";
require_once "../../libs/ui.class.inc.php";
require_once "../includes/main.inc.php";

require_once "inc/header.inc.php";

?>



        <div id="create">
            <p>
            </p>
    
            <p>
            </p>

            <form method="post" action="upload_ssn.php" enctype="multipart/form-data">
    
                <p>
                Select a File to Upload:<br>
                <input type="file" name="file">
                </p>
    
                <p>
                <b>Neighborhood Size:</b>
                <input type="text" name="neighbor_size" value="10">
                <br>
                </p>
    
                <p>
                    <label for='cooccurrence_input'><b>Co-occurrence percentage lower limit:</b></label>
                    <input type='text' id='cooccurrence' name='cooccurrence' maxlength='3'><br>
                </p>
                <p>
                    E-mail address: 
                    <input name='email' id='email' type="text" value="" class="email"><br>
                </p>
    
                <p>
                <b>Sync API Key:</b>
                <input type="text" name="sync_key" value="">
                <br>
                </p>
    
                <center>
                    <div><button type="submit" id='submit' name="submit" class="dark">Generate GNN</button></div>
                </center>
            </form>
        </div>

<?php require_once('inc/footer.inc.php'); ?>


