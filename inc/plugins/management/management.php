<?php 
// Defining a name space is not required,
// But it's a good practise.
namespace Plugins\ManagementModule;
const IDNAME = "management";
// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?"); 



function install($Plugin)
{
	return true;
}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');


function uninstall($Plugin)
{
	return true;
}
\Event::bind("plugin.remove", __NAMESPACE__ . '\uninstall');


function add_module_option($package_modules)
{
    ?>
        <div class="mt-15">
            <label>
                <input type="checkbox" 
                       class="checkbox" 
                       name="modules[]" <?php // input name must be modules[] ?>
                       value="management"
                       <?= in_array("management", $package_modules) ? "checked" : "" ?>>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?= __('Management') ?>
                </span>
            </label>
        </div>
    <?php
}
\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');




function route_maps($global_variable_name)
{
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/management/?", [
        PLUGINS_PATH . "/management/controllers/IndexController.php",
        __NAMESPACE__ . "\IndexController"
    ]);
}
\Event::bind("router.map", __NAMESPACE__ . '\route_maps');




function navigation($Nav, $AuthUser)
{
    $idname = IDNAME;
    include __DIR__."/views/fragments/navigation.fragment.php";
}
\Event::bind("navigation.add_menu", __NAMESPACE__ . '\navigation');