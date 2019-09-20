<?php
namespace Plugins\Inbox;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Index Controller
 */
class IndexController extends \Controller
{
    /**
     * idname of the plugin for internal use
     */
    const IDNAME = 'inbox';


    /**
     * Process
     */
    public function process()
    {
        //\InstagramAPI
        $AuthUser = $this->getVariable("AuthUser");
        $this->setVariable("idname", self::IDNAME);
      
        $buildVersion = '3011';

        // Auth
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        } else if ($AuthUser->isExpired()) {
            header("Location: ".APPURL."/expired");
            exit;
        }
      
        if (\Input::get('build')) {
          echo $buildVersion . ' - ' . $GLOBALS['_PLUGINS_']['inbox']['config']['version'];
          exit;
        }

        $user_modules = $AuthUser->get("settings.modules");
        if (!is_array($user_modules) || !in_array(self::IDNAME, $user_modules)) {
            // Module is not accessible to this user
            header("Location: ".APPURL."/post");
            exit;
        }


        // Get accounts
        $Accounts = \Controller::model("Accounts");
        $Accounts->setPageSize(20)
                 ->setPage(\Input::get("page"))
                 ->where("user_id", "=", $AuthUser->get("id"))
                 ->orderBy("id","DESC")
                 ->fetchData();

        $this->setVariable("Accounts", $Accounts);
      

        $this->view(PLUGINS_PATH."/".self::IDNAME."/views/index.php", null);
    }
}
