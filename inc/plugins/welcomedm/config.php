<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php 
    return [
        "idname" => "welcomedm",
        "plugin_name" => "Auto DM (New Followers)",
        "plugin_uri" => "http://getnextpost.io",
        "author" => "Nextpost",
        "author_uri" => "http://getnextpost.io",
        "version" => "4.1.1",
        "desc" => "Module to send automated direct message to your new followers",
        "icon_style" => "color: #fff; font-size: 18px;",
        "settings_page_uri" => APPURL . "/e/welcomedm/settings"
    ];
    