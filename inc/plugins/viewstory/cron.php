<?php 
namespace Plugins\ViewStory;

ini_set("display_error", true);
error_reporting(E_ALL);

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?"); 



/**
 * All functions related to the cron task
 */



/**
 * Add cron task to like new posts
 */
function addCronTask()
{
    require_once __DIR__."/models/SchedulesModel.php";
    require_once __DIR__."/models/LogModel.php";


    $multipleCron   = isset($GLOBALS['multipleCron']) ? $GLOBALS['multipleCron'] : null;;
    $cronKey        = isset($GLOBALS['multipleCronKey']) ? $GLOBALS['multipleCronKey'] : null;

    // Get auto like schedules
    $Schedules = new SchedulesModel;
    if($multipleCron) {
        $Schedules->where("is_active", "=", 1)
            ->where("schedule_date", "<=", date("Y-m-d H:i:s"))
            ->where("end_date", ">=", date("Y-m-d H:i:s"))
            ->where(\DB::raw("RIGHT(account_id, 1) = " . $cronKey))
            ->orderBy("last_action_date", "ASC")
            ->setPageSize(10) // required to prevent server overload
            ->setPage(1)
            ->fetchData();
    } else {
        $Schedules->where("is_active", "=", 1)
            ->where("schedule_date", "<=", date("Y-m-d H:i:s"))
            ->where("end_date", ">=", date("Y-m-d H:i:s"))
            ->orderBy("last_action_date", "ASC")
            ->setPageSize(10) // required to prevent server overload
            ->setPage(1)
            ->fetchData();
    }

    if ($Schedules->getTotalCount() < 1) {
        // There is not any active schedule
        return false;
    }


    $settings = namespace\settings();

    // Random delays between actions
    $random_delay = 0;
    if ($settings->get("data.random_delay")) {
        $random_delay = rand(0, 300);
    }

    // Speeds
    $default_speeds = [
        "very_slow" => 1,
        "slow" => 2,
        "medium" => 3,
        "fast" => 4,
        "very_fast" => 5,
    ];
    $speeds = $settings->get("data.speeds");
    if (empty($speeds)) {
        $speeds = [];
    } else {
        $speeds = json_decode(json_encode($speeds), true);
    }
    $speeds = array_merge($default_speeds, $speeds);


    $as = [__DIR__."/models/ScheduleModel.php", __NAMESPACE__."\ScheduleModel"];
    foreach ($Schedules->getDataAs($as) as $sc) {
        $Log = new LogModel;
        $Account = \Controller::model("Account", $sc->get("account_id"));
        $User = \Controller::model("User", $sc->get("user_id"));



        // Set default values for the log (not save yet)...
        $Log->set("user_id", $User->get("id"))
            ->set("account_id", $Account->get("id"))
            ->set("status", "error");



        // Check account
        if (!$Account->isAvailable() || $Account->get("login_required")) {
            // Account is either removed (unexected, external factors)
            // Or login required for this account
            // Deactivate schedule
            $sc->set("is_active", 0)->save();

            // Log data
            $Log->set("data.error.msg", "Activity has been stopped")
                ->set("data.error.details", "Re-login is required for the account.")
                ->save();
            continue;
        }

        // Check user
        if (!$User->isAvailable() || !$User->get("is_active") || $User->isExpired()) {
            // User is not valid
            // Deactivate schedule
            $sc->set("is_active", 0)->save();

            // Log data
            $Log->set("data.error.msg", "Activity has been stopped")
                ->set("data.error.details", "User account is either disabled or expired.")
                ->save();
            continue;
        }

        if ($User->get("id") != $Account->get("user_id")) {
            // Unexpected, data modified by external factors
            // Deactivate schedule
            $sc->set("is_active", 0)->save();
            continue;
        }


        // Check user access to the module
        $user_modules = $User->get("settings.modules");
        if (!is_array($user_modules) || !in_array(IDNAME, $user_modules)) {
            // Module is not accessible to this user
            // Deactivate schedule
            $sc->set("is_active", 0)->save();

            // Log data
            $Log->set("data.error.msg", "Activity has been stopped")
                ->set("data.error.details", "Module is not accessible to your account.")
                ->save();
            continue;
        }


        // Calculate next schedule datetime...
        if (isset($speeds[$sc->get("speed")]) && (int)$speeds[$sc->get("speed")] > 0) {
            $speed = (int)$speeds[$sc->get("speed")];
            $delta = round(3600/$speed) + $random_delay;
        } else {
            $delta = rand(720, 7200);
        }

        $next_schedule = date("Y-m-d H:i:s", time() + $delta);

        if ($sc->get("daily_pause")) {
            $pause_from = date("Y-m-d")." ".$sc->get("daily_pause_from");
            $pause_to = date("Y-m-d")." ".$sc->get("daily_pause_to");
            if ($pause_to <= $pause_from) {
                // next day
                $pause_to = date("Y-m-d", time() + 86400)." ".$sc->get("daily_pause_to");
            }

            if ($next_schedule > $pause_to) {
                // Today's pause interval is over
                $pause_from = date("Y-m-d H:i:s", strtotime($pause_from) + 86400);
                $pause_to = date("Y-m-d H:i:s", strtotime($pause_to) + 86400);
            }

            if ($next_schedule >= $pause_from && $next_schedule <= $pause_to) {
                $next_schedule = $pause_to;
            }
        }
        $sc->set("schedule_date", $next_schedule)
           ->set("last_action_date", date("Y-m-d H:i:s"))
           ->save();

        // Login into the account
        try {
            $Instagram = \InstagramController::login($Account);
        } catch (\Exception $e) {
            // Couldn't login into the account
            $Account->refresh();

            // Log data
            if ($Account->get("login_required")) {
                $sc->set("is_active", 0)->save();
                $Log->set("data.error.msg", "Activity has been stopped");
            } else {
                $Log->set("data.error.msg", "Action re-scheduled");
            }
            $Log->set("data.error.details", $e->getMessage())
                ->save();

            continue;
        }

        namespace\_view_story($sc, $Instagram);
    }
}
\Event::bind("cron.add", __NAMESPACE__."\addCronTask");
//\Event::bind("cron.viewstory", __NAMESPACE__."\addCronTask");



/**
 * Like actions for the target feed
 * @param  __NAMESPACE__\ScheduleModel $sc        Schedule Model
 * @param  \InstagramController $Instagram Instagram Controller
 * @return null            
 */
function _view_story($sc, $Instagram)
{
    $Log = new LogModel;
    $Log->set("user_id", $sc->get("user_id"))
        ->set("account_id", $sc->get("account_id"))
        ->set("status", "error");

    // Find media to like
    $media_id       = null;
    $media_code     = null;
    $media_type     = null;
    $media_thumb    = null;
    $user_pk        = null;
    $user_username  = null;
    $user_full_name = null;
    $itemInstance   = null;

    // Generate a random rank token.
    $rank_token = \InstagramAPI\Signatures::generateUUID();
  
    try {
      $feed = $Instagram->story->getReelsTrayFeed();
    } catch (\Exception $e) {
      // Couldn't get instagram feed related to the hashtag
      // Log data
      $msg = $e->getMessage();
      $msg = explode(":", $msg, 2);
      $msg = isset($msg[1]) ? $msg[1] : $msg[0];

      $Log->set("data.error.msg", "Couldn't get the feed")
        ->set("data.error.details", $msg)
        ->save();
      return false;
    }
  
    $items = $feed->getTray();

    if (count($items) < 1) {
        // Invalid
        return false;
    }

    shuffle($items);

    foreach ($items as $tray) {
      if(!$tray->getUser()) {
        continue;
      }
      
      if($tray->getSeen()) {
        continue;
      }
      
      try {
        $user_pk        = $tray->getUser()->getPk();
        $user_username  = $tray->getUser()->getUsername();
        $user_full_name = $tray->getUser()->getFullName(); 
      } catch(\Exception $e) {
        continue;
      }
      
      if( !$tray->getItems()) {
        try {
          $sub = $Instagram->story->getUserReelMediaFeed($user_pk);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $msg = explode(":", $msg, 2);
            $msg = isset($msg[1]) ? $msg[1] : $msg[0];
          
            $Log->set("data.error.msg", "Couldn't get the feed from the user")
              ->set("data.error.details", $msg)
              ->save();
          return false;
        }
        
        if( !$sub->getItems()) {
          continue;
        }
        
        if(!$sub->getUser()) {
          continue;
        }
        
        $user_pk        = $sub->getUser()->getPk();
        $user_username  = $sub->getUser()->getUsername();
        $user_full_name = $sub->getUser()->getFullName();

        foreach($sub->getItems() as $item) {
          $media_id       = $item->getId();
          $media_code     = $item->getCode();
          $media_type     = $item->getMediaType();
          $media_thumb    = namespace\_get_media_thumb_igitem($item);
          $itemInstance   = $item;

          if($media_id) {
            break 2;
          }
        }
        
      } else {
        foreach($tray->getItems() as $item) {
          $media_id       = $item->getId();
          $media_code     = $item->getCode();
          $media_type     = $item->getMediaType();
          $media_thumb    = namespace\_get_media_thumb_igitem($item);
          $itemInstance   = $item;
          if($media_id) {
            break 2;
          }
        }
      }
      
    }
  
    if (!$media_id) {
      $Log->set("data.error.msg", "Couldn't find the new story to see")
        ->set("status", "error")
        ->save();
      
      return false;
    }
  
  
    // See it!
    try {
        $resp = $Instagram->story->markMediaSeen([$itemInstance]);
    } catch (\Exception $e) {
        $msg = $e->getMessage();
        $msg = explode(":", $msg, 2);
        $msg = isset($msg[1]) ? $msg[1] : $msg[0];
    
        $Log->set("data.error.msg", "Something went wrong")
            ->set("data.error.details", $msg)
            ->save();
        
        return false;
    }


    if (!$resp->isOk()) {
      $Log->set("data.error.msg", "Couldn't seen new story")
          ->set("data.error.details", "Something went wrong")
          ->save();
        
      return false;   
    }
  
  
    $Log->set("status", "success")
        ->set("data.story", [
            "media_id" => $media_id,
            "media_code" => $media_code,
            "media_type" => $media_type,
            "media_thumb" => $media_thumb,
            "user" => [
                "pk" => $user_pk,
                "username" => $user_username,
                "full_name" => $user_full_name
            ]
        ])
        ->set("story_media_code", $media_code)
        ->save();

      return true;
}



/**
 * Get media thumb url from the Instagram feed item
 * @param  stdObject $item Instagram feed item
 * @return string|null       
 */
function _get_media_thumb_igitem($item)
{
    $media_thumb = null;

    $media_type = empty($item->getMediaType()) ? null : $item->getMediaType();

    if ($media_type == 1 || $media_type == 2) {
        // Photo (1) OR Video (2)
        $media_thumb = $item->getImageVersions2()->getCandidates()[0]->getUrl();
    } else if ($media_type == 8) {
        // ALbum
        $media_thumb = $item->getCarouselMedia()[0]->getImageVersions2()->getCandidates()[0]->getUrl();
    }    

    return $media_thumb;
}
