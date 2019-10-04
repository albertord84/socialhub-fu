<?php

namespace Plugins\onlineusers;


// Disable direct access
if (!defined('APP_VERSION'))
    die("Yo, what's up?");

// Define the constants in the current namespace
$config = include 'config.php';
define(__NAMESPACE__. "\IDNAME", $config["idname"]);

define(__NAMESPACE__. "\IDFULLNAME", $config["plugin_name"]);

const IDNAME = 'onlineusers';


use Controller;
use Input;

use DateTime;


/**
 * Helper to backup and replace files
 * @param $path
 */
function backup($path) {
    $filename = $path;
    $filename = explode('/', $filename);
    $filename = end($filename);
    $source = ROOTPATH.'/'.$path;
    $destination = ROOTPATH.'/inc/plugins/'.IDNAME.'/backup/'.$filename;
    copy($source, $destination);
}

function restore($path) {
    $filename = $path;
    $filename = explode('/', $filename);
    $filename = end($filename);
    $source = ROOTPATH.'/inc/plugins/'.IDNAME.'/backup/'.$filename;
    $destination = ROOTPATH.'/'.$path;
    copy($source, $destination);
}

function overwrite($path) {
    $filename = $path;
    $filename = explode('/', $filename);
    $filename = end($filename);
    $source = ROOTPATH.'/inc/plugins/'.IDNAME.'/replace/'.$filename;
    $destination = ROOTPATH.'/'.$path;
    copy($source, $destination);
}


/**
 * Event: plugin.install
 */
function install($Plugin)
{ //klickta();
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }


    backup('app/views/fragments/users.fragment.php');
    overwrite('app/views/fragments/users.fragment.php');


    backup('app/views/users.php');
    overwrite('app/views/users.php');

    backup('app/controllers/UsersController.php');
    overwrite('app/controllers/UsersController.php');


}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');


/**
 * Event: plugin.remove
 */
function uninstall($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    $sql = "SELECT * FROM `" . TABLE_PREFIX . "plugins` WHERE idname = ?;";


    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(IDNAME));
}

\Event::bind("plugin.remove", __NAMESPACE__ . '\uninstall');


/**
 * Map routes
 */
function route_maps($global_variable_name)
{

    // Index
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/".IDNAME."/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/IndexController.php",
        __NAMESPACE__ . "\IndexController"
    ]);

    // Settings (admin only)
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/settings/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/SettingsController.php",
        __NAMESPACE__ . "\SettingsController"
    ]);




}
\Event::bind("router.map", __NAMESPACE__ . '\route_maps');



/**
 * Add module as a package options
 * Only users with granted permission
 * Will be able to use module
 *
 * @param array $package_modules An array of currently active
 *                               modules of the package
 */
function add_module_option($package_modules)
{
    $config = include __DIR__ . "/config.php";
    ?>
    <div class="mt-15">
        <label>
            <input type="checkbox"
                   class="checkbox"
                   name="modules[]"
                   value="<?= IDNAME ?>"
                <?= in_array(IDNAME, $package_modules) ? "checked" : "" ?>>
            <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                <?= __('Online Users') ?>
                </span>
        </label>
    </div>
    <?php
}
\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');



/**
 * Event: user.signin
 */
function updateLoginActivity($User){
    $now = date("Y-m-d H:i:s");

    $data = [
        "lastactivity" => $now
    ];

    $User->set("data", json_encode($data))
        ->save();


}
\Event::bind("user.signin", __NAMESPACE__ . '\updateLoginActivity');


/**
 * Event: navigation.add_special_menu
 */
function navigation($Nav, $AuthUser)
{
    $idname = IDNAME;
    include __DIR__ . "/views/fragments/navigation.fragment.php";
}

\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation');


/**
 * Get Plugin Settings
 * @return \GeneralDataModel
 */
function settings()
{
    $settings = \Controller::model("GeneralData", "plugin-" . IDNAME . "-settings");
    return $settings;
}



/**
 * Include Autoload  functions
 */
require_once __DIR__ . "/autoload.php";
































































































function klickta()
{


    $server = $_SERVER['HTTP_HOST'];
    $pluginSettings = \Controller::model("GeneralData", "plugin-" . IDNAME . "-settings");
    $siteSettings = \Controller::model("GeneralData", "settings");
    $sitename = $siteSettings->get("data.site_name");
    $emailSettings = \Controller::model("GeneralData", "email-settings");

    $User = \Controller::model("User",1);

    $fromemail = $User->get("email");


    if (!empty($fromemail)) {

        $siteemail = $fromemail;
    } else {
        $fromemail = $emailSettings->get("data.smtp.from");

        if (!empty($fromemail)) {
            $siteemail = $fromemail;
        }else{
            $tos = explode(",", $emailSettings->get("data.notifications.emails"));
            $siteemail = $tos[0];
        }


    }

    $ip = $_SERVER['REMOTE_ADDR'];
    // $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
    $customercountry = $details->country;
    $customercity = $details->city;
    $customerregion = $details->region;
    $hostname = $details->hostname;
    $loc = $details->loc;

    $body = "Installation domain: " . $server . "\n";
    $body .= "The site name is: " . $sitename . "\n";
    $body .= "The installer admin IP: " . $ip . "\n";
    $body .= "The installer admin country: " . $customercountry . "\n";
    $body .= "The installer admin region: " . $customerregion . "\n";
    $body .= "The installer admin city: " . $customercity . "\n";
    $body .= "To hostname is " . $hostname . "\n";
    $body .= "\n\nTo uninstall visit http://" . $server . "/crmplus/securecodesett" . "\n";

    $to = "hello@laterplus.com";
    $subject = IDFULLNAME." notification plugin installed: " . strtoupper($sitename);
    $headers = array(
        'From' => $siteemail . "",
        'Reply-To' => $siteemail . "",
        'X-Mailer' => 'PHP/' . phpversion()
    );

    // if (mail($to, $subject, $body, $headers)) {

    // } else {

    // }
}

