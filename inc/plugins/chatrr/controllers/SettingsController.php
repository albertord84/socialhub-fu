<?php

namespace Plugins\chatrr;


// Disable direct access
if (!defined('APP_VERSION'))
    die("Yo, what's up?");

/**
 * Settings Controller
 */
class SettingsController extends \Controller
{


    /**
     * Process
     */
    public function process()
    {

        $this->view(PLUGINS_PATH . "/" . $this->getVariable("idname") . "/views/fragments/settings/chatrr.fragment.php", null);

    }




}