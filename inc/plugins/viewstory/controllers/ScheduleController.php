<?php
namespace Plugins\ViewStory;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Schedule Controller
 */
class ScheduleController extends \Controller
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
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $this->setVariable("idname", self::IDNAME);

        // Auth
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        } else if ($AuthUser->isExpired()) {
            header("Location: ".APPURL."/expired");
            exit;
        }

        $user_modules = $AuthUser->get("settings.modules");
        if (!is_array($user_modules) || !in_array(self::IDNAME, $user_modules)) {
            // Module is not accessible to this user
            header("Location: ".APPURL."/post");
            exit;
        }


        // Get account
        $Account = \Controller::model("Account", $Route->params->id);
        if (!$Account->isAvailable() || 
            $Account->get("user_id") != $AuthUser->get("id")) 
        {
            header("Location: ".APPURL."/e/".self::IDNAME);
            exit;
        }
        $this->setVariable("Account", $Account);


        // Get Schedule
        require_once PLUGINS_PATH."/".self::IDNAME."/models/ScheduleModel.php";
        $Schedule = new ScheduleModel([
            "account_id" => $Account->get("id"),
            "user_id" => $Account->get("user_id")
        ]);
        $this->setVariable("Schedule", $Schedule);

        if (\Input::request("action") == "search") {
            $this->search();
        } else if (\Input::post("action") == "save") {
            $this->save();
        }

        $this->view(PLUGINS_PATH."/".self::IDNAME."/views/schedule.php", null);
    }


    /**
     * Save schedule
     * @return mixed 
     */
    private function save()
    {
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");
        $Schedule = $this->getVariable("Schedule");

        
        $end_date = "2030-12-12 23:59:59";


        $daily_pause = (bool)\Input::post("daily_pause");

        $Schedule->set("user_id", $AuthUser->get("id"))
                 ->set("account_id", $Account->get("id"))
                 ->set("speed", \Input::post("speed"))
                 ->set("is_active", (bool)\Input::post("is_active"))
                 ->set("daily_pause", $daily_pause)
                 ->set("end_date", $end_date);


        $schedule_date = date("Y-m-d H:i:s", time() + 60);
        if ($daily_pause) {
            $from = new \DateTime(date("Y-m-d")." ".\Input::post("daily_pause_from"),
                                  new \DateTimeZone($AuthUser->get("preferences.timezone")));
            $from->setTimezone(new \DateTimeZone("UTC"));

            $to = new \DateTime(date("Y-m-d")." ".\Input::post("daily_pause_to"),
                                new \DateTimeZone($AuthUser->get("preferences.timezone")));
            $to->setTimezone(new \DateTimeZone("UTC"));

            $Schedule->set("daily_pause_from", $from->format("H:i:s"))
                     ->set("daily_pause_to", $to->format("H:i:s"));


            $to = $to->format("Y-m-d H:i:s");
            $from = $from->format("Y-m-d H:i:s");
            if ($to <= $from) {
                $to = date("Y-m-d H:i:s", strtotime($to) + 86400);
            }

            if ($schedule_date > $to) {
                // Today's pause interval is over
                $from = date("Y-m-d H:i:s", strtotime($from) + 86400);
                $to = date("Y-m-d H:i:s", strtotime($to) + 86400);
            }

            if ($schedule_date >= $from && $schedule_date <= $to) {
                $schedule_date = $to;
                $Schedule->set("schedule_date", $schedule_date);
            }
        }
        $Schedule->set("schedule_date", $schedule_date);
        $Schedule->save();


        $this->resp->msg = __("Changes saved!");
        $this->resp->result = 1;
        $this->jsonecho();
    }
}
