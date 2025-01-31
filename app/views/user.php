<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">

        <meta name="description" content="<?= site_settings("site_description") ?>">
        <meta name="keywords" content="<?= site_settings("site_keywords") ?>">

        <link rel="icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">

        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/core.css?v=".VERSION ?>">

        <title><?= $User->isAvailable() ? htmlchars($User->get("firstname") ." ". $User->get("lastname")) : __("New User") ?></title>
        <!-- ManyChat -->
        <!-- <script src="//widget.manychat.com/704984909870600.js" async="async">
        </script> -->
         <!-- BEGIN JIVOSITE CODE {literal} -->
        <script type='text/javascript'>
        (function(){ var widget_id = 'pRHc45qMSN';var d=document;var w=window;function l(){
          var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true;
          s.src = '//code.jivosite.com/script/widget/'+widget_id
            ; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}
          if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}
          else{w.addEventListener('load',l,false);}}})();
        </script>
        <!-- {/literal} END JIVOSITE CODE -->
    </head>

    <body>
        <?php 
            $Nav = new stdClass;
            $Nav->activeMenu = "users";
            require_once(APPPATH.'/views/fragments/navigation.fragment.php');
        ?>

        <?php 
            $TopBar = new stdClass;
            $TopBar->title = __("Users");
            $TopBar->btn = array(
                "icon" => "sli sli-user-follow",
                "title" => __("Add new"),
                "link" => APPURL."/users/new"
            );
            require_once(APPPATH.'/views/fragments/topbar.fragment.php'); 
        ?>

        <?php require_once(APPPATH.'/views/fragments/user.fragment.php'); ?>
        
        <script type="text/javascript" src="<?= APPURL."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <?php require_once(APPPATH.'/inc/js-locale.inc.php'); ?>
        <script type="text/javascript" src="<?= APPURL."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" charset="utf-8">
            $(function(){
                NextPost.UserForm();
            })
        </script>

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html>