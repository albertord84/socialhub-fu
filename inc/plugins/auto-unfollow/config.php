<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php 
    return [
        "idname" => "auto-unfollow",
        "plugin_name" => "Auto Unfollow",
        "plugin_uri" => "http://getnextpost.io",
        "author" => "Nextpost",
        "author_uri" => "http://getnextpost.io",
        "version" => "4.1",
        "desc" => "Save time and let the system unfollow your followers regularly just one click.",
        "icon_style" => " color: #fff;",
        "settings_page_uri" => APPURL."/e/auto-unfollow/settings"
    ];
