<?php
namespace Plugins\AutoFollow;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Log Controller
 */
class FaqController extends \Controller
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
        $this->setVariable("Settings", namespace\settings());

        // View
        $this->view(PLUGINS_PATH."/".self::IDNAME."/views/faq.php", null);
    }
}