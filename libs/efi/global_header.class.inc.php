<?php
namespace efi;
require_once(__DIR__ . "/../../init.php");

use \efi\global_settings;


class global_header {

    public static function get_global_citation() {
        $citation = global_settings::get_global_citation();
        return <<<CITATION
            <div class="citation-message">
                <div id="clipboard-citation">$citation</div>
            </div>
CITATION;
    }

    public static function check_for_maintenance_mode() {
        if (global_settings::get_website_enabled())
            return "";

        $SiteUrlPrefix = get_site_url_prefix();
        $web_path = __WEB_PATH__;
        $BannerImagePath = "$SiteUrlPrefix/images/efi_logo.png";
        $title = "EFI Tools are Undergoing Maintenance";

        $UpdateMsg = __GLOBAL_UPDATE_MESSAGE__;
        $DisabledMsg = global_settings::get_website_enabled_message();

        $is_dev_site = global_settings::advanced_options_enabled();

        $html = <<<HTML
<!doctype html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="<?php echo $SiteUrlPrefix; ?>/css/global.css?v=15">
    <link rel="shortcut icon" href="<?php echo $SiteUrlPrefix; ?>/images/favicon_efi.ico" type="image/x-icon">
    <title><?php echo $title; ?></title>
</head>

<body>
    <div id="container">
        <div class="header">
            <div class="header-logo">
                <div class="logo-left"><a href="<?php echo $web_path; ?>"><img src="<?php echo $BannerImagePath; ?>" height="75" alt="Enzyme Function Initiative Logo"></a></div>
                <div class="logo-right"><a href="http://igb.illinois.edu"><img src="<?php echo $SiteUrlPrefix; ?>/images/illinois_igb_full.png" height="75" alt="Institute for Genomic Biology at University of Illinois at Urbana-Champaign logo"></a></div>
            </div>
        </div>

        <div class="content-holder">
            <h1 class="ruled"><?php echo $title; ?></h1>
            <div class="funding-source funding-citation">This web resource is supported by a Research Resource from the National Institute of General Medical Sciences (R24GM141196-01).</div>
            <div class="funding-source" style="margin-bottom: 25px">The tools are available without charge or license to both academic and commercial users.</div>
            <div id="update-message" class="update-message"><?php echo $DisabledMsg; ?></div>
        </div> <!-- content-holder -->
        <div class="footer-container">
            <div class="footer">
                <div class="address inline">
                    Carl R. Woese Institute for Genomic Biology<br>
                    University of Illinois at Urbana-Champaign<br>
                    1206 W. Gregory Drive Urbana, IL 61801<br>
                    <a href="mailto:efi@enzymefunction.org">efi@enzymefunction.org</a>
                </div>
                <div class="footer-logo inline">
                    <img alt="Enzyme Function Initiative logo" src="<?php echo $SiteUrlPrefix; ?>/images/efi_logo.png" height="38"></a>
                </div>
                <div class="footer-logo inline">
                    <a href="http://www.nigms.nih.gov/" target="_blank"><img alt="NIGMS" src="<?php echo $SiteUrlPrefix; ?>/images/nighnew.png" style="width: 201px; height: 30px;"></a>
                </div>
            </div>

        </div>
    </div> <!-- container -->
</body>
</html>
HTML;

        echo $html;
        exit(0);
    }

}

