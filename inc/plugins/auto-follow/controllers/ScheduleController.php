<?php
namespace Plugins\AutoFollow;

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
    const IDNAME = 'auto-follow';


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

        if(isset($Route->params->action) && $Route->params->action == "byName"){
            $ordertable = "accounts.";
                  $orderBy = "username";
                  $order = "ASC";
  
                  $this->setVariable("Order", "name");
                  $this->setVariable("OrderL", "e/auto-follow");
  
              }else if(isset($Route->params->action) && $Route->params->action == "byStatus"){
  
            $ordertable = "auto_follow_schedule.";
            $orderBy = "is_active";
                  $order = "ASC";
  
                  $this->setVariable("Order", "status");
                  $this->setVariable("OrderL", "e/auto-follow");
  
          }else{
            $ordertable = "accounts.";
                  $orderBy = "id";
                  $order = "DESC";
                  $this->setVariable("Order", "date");
                  $this->setVariable("OrderL", "e/auto-follow");
          }

          $Accounts = \Controller::model("Accounts");
          $Accounts->where("user_id", "=", $AuthUser->get("id"))
                   ->orderBy("id","DESC")
                   ->fetchData();
    
            // Get account
            $Account = \Controller::model("Account", $Route->params->id);
            if (!$Account->isAvailable() ||
                $Account->get("user_id") != $AuthUser->get("id"))
            {
                header("Location: ".APPURL."/e/".self::IDNAME);
                exit;
            }

                $targets = [];         		
        
                try{	
                    $q4 = "SELECT target,id FROM ".TABLE_PREFIX."auto_repost_schedule WHERE user_id =" . $AuthUser->get("id");
                    $query = \DB::query($q4);
                    $schedules =  $query->get();

                        foreach($schedules as $sc){
                            $target = new \stdClass();
                            $target->module = "Repost Module";
                            $target->id = $sc->id;
                            $target->count = count(json_decode($sc->target));
                            $target->targets = $sc->target;

                            array_push($targets, $target);
                        }
                    }catch(\Exception $e){}

                try{	
                        $q4 = "SELECT target,id FROM ".TABLE_PREFIX."auto_like_schedule WHERE user_id =" . $AuthUser->get("id");
                        $query = \DB::query($q4);
                        $schedules =  $query->get();
        
                        foreach($schedules as $sc){
                            $target = new \stdClass();
                            $target->module = "Like Module";
                            $target->id = $sc->id;
                            $target->count = count(json_decode($sc->target));
                            $target->targets = $sc->target;  
                            array_push($targets, $target);
                        }
                }catch(\Exception $e){}

                try{	
                        $q4 = "SELECT target,id FROM ".TABLE_PREFIX."auto_comment_schedule WHERE user_id =" . $AuthUser->get("id");
                        $query = \DB::query($q4);
                        $schedules =  $query->get();
        
                        foreach($schedules as $sc){
                            $target = new \stdClass();
                            $target->module = "Comment Module";
                            $target->id = $sc->id;
                            $target->count = count(json_decode($sc->target));
                            $target->targets = $sc->target;  
                            array_push($targets, $target);
                        }
                }catch(\Exception $e){}

                try{	
                        $q4 = "SELECT target,id FROM ".TABLE_PREFIX."welcomedm_schedule WHERE user_id =" . $AuthUser->get("id");
                        $query = \DB::query($q4);
                        $schedules =  $query->get();
        
                        foreach($schedules as $sc){
                            $target = new \stdClass();
                            $target->module = "DM Module";
                            $target->id = $sc->id;
                            $target->count = count(json_decode($sc->target));
                            $target->targets = $sc->target;  
                            array_push($targets, $target);
                        }
                }catch(\Exception $e){}
				
                try{	
                        $q4 = "SELECT target,id FROM ".TABLE_PREFIX."auto_follow_schedule WHERE user_id =" . $AuthUser->get("id");
                        $query = \DB::query($q4);
                        $schedules =  $query->get();
        
                        foreach($schedules as $sc){
                            $target = new \stdClass();
                            $target->module = "Follow Module";
                            $target->id = $sc->id;
                            $target->count = count(json_decode($sc->target));
                            $target->targets = $sc->target;  
                            array_push($targets, $target);
                        }
                }catch(\Exception $e){}

                $this->setVariable("Targets", $targets);                   

        $this->setVariable("Accounts", $Accounts);
        $this->setVariable("Account", $Account);
        $this->setVariable("Settings", namespace\settings());

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
     * Search hashtags, people, locations
     * @return mixed
     */
    private function search()
    {
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");
        $Account = $this->getVariable("Account");

        $query = \Input::request("q");
        if (!$query) {
            $this->resp->msg = __("Missing some of required data.");
            $this->jsonecho();
        }

        $type = \Input::request("type");
        if (!in_array($type, ["hashtag", "location", "people"])) {
            $this->resp->msg = __("Invalid parameter");
            $this->jsonecho();
        }

        // Login
        try {
            $Instagram = \InstagramController::login($Account);
        } catch (\Exception $e) {
            $this->resp->msg = $e->getMessage();
            $this->jsonecho();
        }

        $this->resp->items = [];

        // Get data
        try {
            if ($type == "hashtag") {
                $search_result = $Instagram->hashtag->search($query);
                if ($search_result->isOk()) {
                    foreach ($search_result->getResults() as $r) {
                        $this->resp->items[] = [
                            "value" => $r->getName(),
                            "data" => [
                                "sub" => n__("%s public post", "%s public posts", $r->getMediaCount(), $r->getMediaCount()),
                                "id" => str_replace("#", "", $r->getName())
                            ]
                        ];
                    }
                }
            } else if ($type == "location") {
                $search_result = $Instagram->location->findPlaces($query);
                if ($search_result->isOk()) {
                    foreach ($search_result->getItems() as $r) {
                        $this->resp->items[] = [
                            "value" => $r->getLocation()->getName(),
                            "data" => [
                                "sub" => false,
                                "id" => $r->getLocation()->getFacebookPlacesId()
                            ]
                        ];
                    }
                }
            } else if ($type == "people") {
                $search_result = $Instagram->people->search($query);
                if ($search_result->isOk()) {
                    foreach ($search_result->getUsers() as $r) {
                        $this->resp->items[] = [
                            "value" => $r->getUsername(),
                            "data" => [
                                "sub" => $r->getFullName(),
                                "id" => $r->getPk()
                            ]
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->resp->msg = $e->getMessage();
            $this->jsonecho();
        }


        $this->resp->result = 1;
        $this->jsonecho();
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

        $targets = @json_decode(\Input::post("target"));
        if (!$targets) {
            $targets = [];
        }

        $valid_targets = [];
        foreach ($targets as $t) {
            if (isset($t->type, $t->value, $t->id) &&
                in_array($t->type, ["hashtag", "location", "people"]))
            {
                $valid_targets[] = [
                    "type" => $t->type,
                    "id" => $t->id,
                    "value" => $t->value
                ];
            }
        }
        $target = json_encode($valid_targets);

        $end_date = count($valid_targets) > 0
                  ? "2030-12-12 23:59:59" : date("Y-m-d H:i:s");

        $daily_pause = (bool)\Input::post("daily_pause");

        $Schedule->set("user_id", $AuthUser->get("id"))
                 ->set("account_id", $Account->get("id"))
                 ->set("target", $target)
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

        $powerlike = (bool)\Input::post("powerlike");
        $powerlike_count = (int)\Input::post("powerlike_count");
        $powerlike_random = (bool)\Input::post("powerlike_random");

        $filter_blacklist = \Input::post("filter_blacklist");
        $filter_gender = \Input::post("filter_gender");
        $filter_privat = (bool)\Input::post("filter_privat");
        $filter_picture = (bool)\Input::post("filter_picture");
        $filter_business = (bool)\Input::post("filter_business");
        $filter_media_min = (Int)\Input::post("filter_media_min");
        $filter_unfollowed = (bool)\Input::post("filter_unfollowed");
        $filter_followed_min = (Int)\Input::post("filter_followed_min");
        $filter_followed_max = (Int)\Input::post("filter_followed_max");
        $filter_following_min = (Int)\Input::post("filter_following_min");
        $filter_following_max = (Int)\Input::post("filter_following_max");
        $filter_media_days = (Int)\Input::post("filter_media_days");
        $filter_accuracy = (Int)\Input::post("filter_accuracy");

        $Schedule->set("data.powerlike", $powerlike)
        ->set("data.powerlike_count", $powerlike_count)
        ->set("data.powerlike_random", $powerlike_random)
        ->set("data.filter_blacklist", $filter_blacklist)
        ->set("data.filter_gender", $filter_gender)
        ->set("data.filter_privat", $filter_privat)
        ->set("data.filter_picture", $filter_picture)
        ->set("data.filter_business", $filter_business)
        ->set("data.filter_media_min", $filter_media_min)
        ->set("data.filter_unfollowed", $filter_unfollowed)
        ->set("data.filter_followed_min", $filter_followed_min)
        ->set("data.filter_followed_max", $filter_followed_max)
        ->set("data.filter_following_min", $filter_following_min)
        ->set("data.filter_following_max", $filter_following_max)
        ->set("data.filter_media_days", $filter_media_days)
        ->set("data.filter_accuracy", $filter_accuracy)
        ->save();

        $Schedule->save();

        $this->resp->msg = __("Changes saved!");
        $this->resp->result = 1;
        $this->jsonecho();
    }
}
