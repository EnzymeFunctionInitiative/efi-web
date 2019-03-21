<?php


class est_ui {
    public static function make_pfam_size_box($parentId, $tableId) {
        return <<<HTML
                <center>
                        <div style="width:85%;display:none" id="$parentId">
                            <table border="0" width="100%" class="family">
                                <thead>
                                    <th>Family</th>
                                    <th>Family Name</th>
                                    <th>Full Size</th>
                                    <th id="$tableId-ur-hdr">UniRef90 Size</th>
                                    <th id="$tableId-ur-hdr">UniRef50 Size</th>
                                </thead>
                                <tbody id="$tableId"></tbody>
                            </table>
                        </div>
                </center>
HTML;
    }
}

?>

