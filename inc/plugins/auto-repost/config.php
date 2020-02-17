<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php 
    return [
        "idname" => "auto-repost",
        "plugin_name" => "Auto Repost",
        "plugin_uri" => "http://getnextpost.io",
        "author" => "Nextpost",
        "author_uri" => "http://getnextpost.io",
        "version" => "4.1.1",
        "desc" => "Very useful module to re-post random temporary posts. Module will select random posts accoding to selected targets, re-post them and will remove them after specified time passes.",
        "icon_style" => "color: #fff; font-size: 18px;",
        "settings_page_uri" => APPURL . "/e/auto-repost/settings"
    ];
    