<?php
namespace Plugins\ViewStory;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Cron Controller
 */
class CronController extends \Controller
{
    /**
     * idname of the plugin for internal use
     */
    const IDNAME = 'viewstory';


    /**
     * Process
     */
    public function process()
    {
        \Event::trigger("cron.viewstory");
    }
}
