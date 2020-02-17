<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
    <meta name="theme-color" content="#fff">

    <meta name="description" content="<?= site_settings("site_description") ?>">
    <meta name="keywords" content="<?= site_settings("site_keywords") ?>">

    <link rel="icon"
          href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL . "/assets/img/logomark.png" ?>"
          type="image/x-icon">
    <link rel="shortcut icon"
          href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL . "/assets/img/logomark.png" ?>"
          type="image/x-icon">

    <link rel="stylesheet" type="text/css" href="<?= APPURL . "/assets/css/plugins.css?v=" . VERSION ?>">
    <link rel="stylesheet" type="text/css" href="<?= APPURL . "/assets/css/core.css?v=" . VERSION ?>">

    <title><?= __("Users") ?></title>
    <!-- Start of HubSpot Embed Code -->
  <script type="text/javascript" id="hs-script-loader" async defer src="//js.hs-scripts.com/5213150.js"></script>
<!-- End of HubSpot Embed Code -->
</head>

<body>
<?php
$Nav = new stdClass;
$Nav->activeMenu = "users";
require_once(APPPATH . '/views/fragments/navigation.fragment.php');
?>

<?php




        $now = date("Y-m-d H:i:s");
        $Users = Controller::model("Users");
        $Users->search(Input::get("q"))
            ->orderBy("id", "DESC")
            ->fetchData();

        $count = 0;
        foreach ($Users->getDataAs("User") as $u) {
            $now = date("Y-m-d H:i:s");

            $lastactivity = $u->get("data.lastactivity");

            $datediff = strtotime($now) - strtotime($lastactivity);

            if ($datediff <= 20 and $datediff >= 0) {
                $count++;
            }
        }


$TopBar = new stdClass;
$TopBar->title = __("Users") . __(" | Online Users: ". $count);
$TopBar->btn = array(
    "icon" => "sli sli-user-follow",
    "title" => __("Add new"),
    "link" => APPURL . "/users/new"
);
require_once(APPPATH . '/views/fragments/topbar.fragment.php');
?>

<?php require_once(APPPATH . '/views/fragments/users.fragment.php'); ?>

<script type="text/javascript" src="<?= APPURL . "/assets/js/plugins.js?v=" . VERSION ?>"></script>
<?php require_once(APPPATH . '/inc/js-locale.inc.php'); ?>
<script type="text/javascript" src="<?= APPURL . "/assets/js/core.js?v=" . VERSION ?>"></script>
<script type="text/javascript" charset="utf-8">
    $(function () {
        NextPost.UserForm();
    })
</script>

<!-- GOOGLE ANALYTICS -->
<?php require_once(APPPATH . '/views/fragments/google-analytics.fragment.php'); ?>
</body>
</html>