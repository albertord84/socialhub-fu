<?php
namespace Plugins\AutoFollow;

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
    const IDNAME = 'auto-follow';


    /**
     * Process
     */
    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $this->setVariable("idname", self::IDNAME);
        $Route = $this->getVariable("Route");

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
    			$order = "DESC";

    			$this->setVariable("Order", "status");
    			$this->setVariable("OrderL", "e/auto-follow");

        }else{
          $ordertable = "accounts.";
    			$orderBy = "id";
    			$order = "DESC";
    			$this->setVariable("Order", "date");
    			$this->setVariable("OrderL", "e/auto-follow");
        }
        

        if(\Input::get("q") != null){

            if (!preg_match("/^([a-z0-9_][a-z0-9_\.]{1,28}[a-z0-9_])$/", \Input::get("q"))) {
                $this->resp->msg = __("Please include a valid username.");
                exit();
            }            

            $uname = preg_replace('/[^A-Za-z0-9\-]/', '', \Input::get("q"));
            
            $q4 = "SELECT ".TABLE_PREFIX."accounts.*, ".TABLE_PREFIX."auto_follow_schedule.is_active FROM ".TABLE_PREFIX."accounts LEFT JOIN ".TABLE_PREFIX."auto_follow_schedule ON ".TABLE_PREFIX."accounts.id = ".TABLE_PREFIX."auto_follow_schedule.account_id WHERE ".TABLE_PREFIX."accounts.user_id = " . $AuthUser->get("id") . " AND ".TABLE_PREFIX."accounts.username LIKE '%".trim((string)\Input::get("q"))."%' ORDER BY ".TABLE_PREFIX.$ordertable . $orderBy . " " .$order;
            
            $query = \DB::query($q4);
            $accounts =  $query->get();
            $this->setVariable("Accounts", $accounts);             

        }else{

            $q4 = "SELECT ".TABLE_PREFIX."accounts.*, ".TABLE_PREFIX."auto_follow_schedule.is_active FROM ".TABLE_PREFIX."accounts LEFT JOIN ".TABLE_PREFIX."auto_follow_schedule ON ".TABLE_PREFIX."accounts.id = ".TABLE_PREFIX."auto_follow_schedule.account_id WHERE ".TABLE_PREFIX."accounts.user_id = " . $AuthUser->get("id") . " ORDER BY ".TABLE_PREFIX.$ordertable . $orderBy . " " .$order;
            $query = \DB::query($q4);
            $accounts =  $query->get();
            $this->setVariable("Accounts", $accounts);

        }

        $accpics = (array) $AuthUser->get("data.accpics");

        $changed = false;
        
        foreach($accounts as $acc){

            if(!isset($accpics[$acc->username])){
                $accpics[$acc->username] = $this->getAccountPicture($acc->username);
                $changed = true;
            }

        }

        if($changed){
            $AuthUser->set("data.accpics", $accpics)->save();
        }

        $this->setVariable("AccountPics", $accpics); 
        $this->setVariable("Accounts", $accounts); 
        $this->setVariable("Settings", namespace\settings());

        $this->view(PLUGINS_PATH."/".self::IDNAME."/views/index.php", null);
    }

    private function getAccountPicture($accountname)
    {

        $instagramname = $accountname;

        $curl = curl_init();

        $s = array(
            CURLOPT_URL => "https://www.instagram.com/" . $instagramname,
            CURLOPT_REFERER => "https://google.com",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36",
            CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true,
        );

        curl_setopt_array($curl, $s);
        $response = curl_exec($curl);
        curl_close($curl);

        $regex = '@<meta property="og:image" content="(.*?)"@si';
        preg_match_all($regex, $response, $return);

        if(isset($return[1][0])){

            $ret = $return[1][0];
        
        }else{
            $ret = null;
        }
        
        return $ret;

    }
}
