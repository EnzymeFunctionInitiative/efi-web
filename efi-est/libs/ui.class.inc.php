<?php


class ui {
    public static function make_upload_box($title, $fileId, $progressBarId, $progressNumId) {
        global $maxFileSize;
        if (!isset($maxFileSize))
            $maxFileSize = ini_get('post_max_size');

        return <<<HTML
                <div style="margin-top: 10px">
                    <b>$title</b><br>
                    <input type='file' name='$fileId' id='$fileId' data-url='server/php/' class="input_file">
                    <label for="$fileId" class="file_upload"><img src="images/upload.svg" /> <span>Choose a file&hellip;</span></label>
                    <progress id='$progressBarId' max='100' value='0'></progress> <a class="question" title="Maximum size is $maxFileSize">?</a>
                </div>
                <br><div id="$progressNumId"></div>
HTML;
    }

    public static function make_pfam_size_box($parentId, $tableId, $showUniref90, $showUniref50) {
        $uniref90 = $showUniref90 ? "<th id=\"$tableId-ur-hdr\">UniRef 90 Size</th>" : "";
        $uniref50 = $showUniref50 ? "<th id=\"$tableId-ur-hdr\">UniRef 50 Size</th>" : "";
        return <<<HTML
                <center>
                        <div style="width:85%;display:none" id="$parentId">
                            <table border="0" width="100%" class="family">
                                <thead>
                                    <th>Family</th>
                                    <th>Family Name</th>
                                    <th>Full Size</th>
                                    $uniref90
                                    $uniref50
                                </thead>
                                <tbody id="$tableId"></tbody>
                            </table>
                        </div>
                </center>
HTML;
    }
}

?>

