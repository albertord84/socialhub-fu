<?php 
namespace Plugins\ViewStory;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Schedules model
 *
 * @version 1.0
 * 
 */
class SchedulesModel extends \DataList
{	
	/**
	 * Initialize
	 */
	public function __construct()
	{
		$this->setQuery(\DB::table(TABLE_PREFIX."auto_viewstory_schedule"));
	}
}
