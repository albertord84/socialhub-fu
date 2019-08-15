<?php

namespace Plugins\chatrr;


// Disable direct access
if (!defined('APP_VERSION'))
    die("Yo, what's up?");


// Define the constants in the current namespace
$config = include 'config.php';
define(__NAMESPACE__. "\IDNAME", $config["idname"]);

define(__NAMESPACE__. "\IDFULLNAME", $config["plugin_name"]);
/**
 * Event: plugin.install
 */
function install($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

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
                <?= __('Chatrr') ?>
                </span>
        </label>
    </div>
    <?php
}

\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');


/**
 * Event: navigation.add_special_menu
 */
function navigation($Nav, $AuthUser)
{
    $idname = IDNAME;
    $Settings = \Controller::model("GeneralData", "plugin-" . IDNAME . "-settings");
    include __DIR__ . "/views/fragments/navigation.fragment.php";
}

\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation');



/**
 * Event: chatrr.load
 */
function chatrrLoader($User){

    $idname = IDNAME;
    $Settings = \Controller::model("GeneralData", "plugin-" . IDNAME . "-settings");
    if($Settings->get("data.chatrr.chathandler") == "fbmessenger"){
        include __DIR__ . "/views/fragments/fbchatscript.fragment.php";
    }else if($Settings->get("data.chatrr.chathandler") == "tidio"){
        include __DIR__ . "/views/fragments/tidioscript.fragment.php";
    }else{

    }

}
\Event::bind("chatrr.load", __NAMESPACE__ . '\chatrrLoader');

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


