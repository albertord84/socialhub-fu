<?php

namespace Plugins\onlineusers;




// Disable direct access
if (!defined('APP_VERSION'))
    die("Yo, what's up?");

use Controller;
use Input;

// const IDNAME = 'onlineusers';
// require_once PLUGINS_PATH . "/" . IDNAME . "/lib/onlineusers.php";

/**
 * Index Controller
 */
class IndexController extends \Controller
{
    public static $emailSettings;

    public static $siteSettings;


    public static $pluginSettings;

    /**
     * Process
     */
    public function process()
    {

        $api = new LicenseBoxAPI();
        $res = $api->verify_license();

        $AuthUser = $this->getVariable("AuthUser");

        $this->setVariable("idname", IDNAME);

        $this->setVariable("idfullname", IDFULLNAME);

        $this->setVariable("res", $res);

        // Auth
        if (!$AuthUser) {
            header("Location: " . APPURL . "/login");
            exit;
        } else if ($AuthUser->isExpired()) {
            header("Location: " . APPURL . "/expired");
            exit;
        }


        //To show only our module
        $Plugins = \Controller::model("Plugins");
        $Plugins->setPageSize(20)
            ->where("is_active", 1)
            ->where("idname", "onlineusers")
            ->setPage(\Input::get("page"))
            ->orderBy("id", "DESC")
            ->fetchData();
        $this->setVariable("Plugins", $Plugins);


        // Get Accounts
        $Accounts = \Controller::model("Accounts");
        $Accounts->setPageSize(20)
            ->setPage(\Input::get("page"))
            ->where("user_id", "=", $AuthUser->get("id"))
            ->orderBy("id", "DESC")
            ->fetchData();



        // Set variables
        $active_plugins = "";
        $this->setVariable("active_plugins", $active_plugins)
            ->setVariable("Plugins", $Plugins)
            ->setVariable("Settings", \Controller::model("GeneralData", "settings"))
            ->setVariable("Integrations", \Controller::model("GeneralData", "integrations"));

        // Plugin settings
        $this->setVariable("Settings", namespace\settings());

        if (\Input::post("action") == "activate"){
            $codesettKey= \Input::post("nextpost-marketplace-key");
            $this->activatelicenseOnlineusers($api,$codesettKey);
        }elseif (\Input::post("action") == "deactivate") {
            $this->deactivatelicenseOnlineusers($api);
        }elseif (\Input::post("action") == "save") {
            $this->saveOnlineusers();
        }

        $action = Input::get("a");
        if (empty($action)) {
            $action = Input::post("action");
            if (empty($action)) {
                $action = Input::post("a");
            }

        }
        $action = $action ? $action : 'composemail';
        $this->setVariable('action', $action);
        $this->setVariable('info', []);

        //define action
        switch ($action) {
            case 'updateuseractivity' :
                $this->updateUserActivity();
                break;
            case 'saveswitch' :
                $itemId = Input::post("itemId");
                $status = Input::post("status");
                $this->setAction($itemId, $status);
                break;
            case 'fetchuserstatus' :
                $this->fetchUserStatus();
                break;
        }

        $this->view(PLUGINS_PATH . "/" . IDNAME . "/views/index.php", null);

    }

    public function updateUserActivity()
    {

        $AuthUser = $this->getVariable("AuthUser");
        $lastactivity = $AuthUser->get('data.lastactivity');

        $now = date("Y-m-d H:i:s");
        if (!$lastactivity) {

            $data = [
                "lastactivity" => $now
            ];

            $AuthUser->set("data", json_encode($data))
                ->save();

        } else {

            $data = [
                "lastactivity" => $now
            ];

            $AuthUser->set("data", json_encode($data))
                ->update();
        }


    }

    public static function fetchOnlineUserCount()
    {

        $Users = Controller::model("Users");
        $Users->search(Input::get("q"))
            ->orderBy("id", "DESC")
            ->fetchData();

        $count = 0;
        foreach ($Users->getDataAs("User") as $u) {
            $now = date("Y-m-d H:i:s");

            $lastactivity = $u->get("data.lastactivity");

            $datediff = strtotime($now) - strtotime($lastactivity);
            // $datediff = abs(round($datediff / 86400));

            if ($datediff <= 20 and $datediff >= 0) {
                $count++;
            }
        }

        return $count;

    }

    private function fetchUserStatus()
    {


        $this->resp->result = 0;
        $now = date("Y-m-d H:i:s");
        $Users = Controller::model("Users");
        $Users->search(Input::get("q"))
            ->orderBy("id", "DESC")
            ->fetchData();

        $count = 0;
        foreach ($Users->getDataAs("User") as $u) {
            $now = date("Y-m-d H:i:s");

            $lastactivity = $u->get("data.lastactivity");

            $datediff = strtotime($now) - strtotime($lastactivity);
            //  $datediff = abs(round($datediff / 86400));

            if ($datediff > 0 and $datediff <= 20) {
                $count++;
            }
        }

        $this->resp->result = 1;
        $this->resp->msg = $count;
        $this->jsonecho();

    }

    private function saveOnlineusers()
    {


        $Settings = $this->getVariable("Settings");
        if (!$Settings->isAvailable()) {
            $Settings->set("name", "plugin-" . IDNAME . "-settings");
        }


        $codesettKey= \Input::post("nextpost-marketplace-key");


        $Settings->set("data.nextpost-marketplace-key", $codesettKey)
            ->save();

        $this->resp->result = 1;
        $this->resp->msg = __("Online Users Settings updated correctly!");
        $this->jsonecho();

        return $this;
    }

    private function updateOnlineusers($api){



    }

    private function checkUpdateOnlineusers($api){


        $response =  $api->check_update();

        if(empty($response))
        {
            $message='Server is unavailable.';
        }
        else
        {
            if ($response['status'] == 'true') {
                $message = $response['message'];
                $this->resp->result = 1;

                $this->resp->msg = __("". $message. "\n\nPlease refresh this page");

            }else{
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }

    private function savelicenseOnlineusers($codesettKey)
    {
        $Settings = $this->getVariable("Settings");
        if (!$Settings->isAvailable()) {
            $Settings->set("name", "plugin-" . IDNAME . "-settings");
        }

        $Settings->set("data.nextpost.marketplace.licensekey", $codesettKey)
            ->save();


        return $this;
    }

    private function activatelicenseOnlineusers($api,$licensekey)
    {
        $this->resp->result = 0;
        $siteSettings = self::getSiteSettings();

        $response = $api->activate_license($licensekey, $siteSettings->get("data.site_name"));

        if(empty($response))
        {
            $message='Server is unavailable.';
        }
        else
        {
            if ($response['status'] == 'true') {
                $message = $response['message'];
                $this->resp->result = 1;

                $this->resp->msg = __("". $message. "\n\nPlease refresh this page");

                $this->savelicenseOnlineusers($licensekey);
            }else{
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }

    private function deactivatelicenseOnlineusers($api)
    {
        $this->resp->result = 0;
        $siteSettings = self::getSiteSettings();
        $pluginSettings = self::getPluginSettings();
        $licensekey = $pluginSettings->get("data.nextpost.marketplace.licensekey");
        $response = $api->deactivate_license($licensekey, $siteSettings->get("data.site_name"));

        if(empty($response))
        {
            $message='Server is unavailable.';
        }
        else
        {
            if ($response['status'] == 'true') {
                $message = $response['message'];
                $this->resp->result = 1;

                $this->resp->msg = __("". $message. "\n\nPlease refresh this page");

                $this->savelicenseOnlineusers($licensekey);
            }else{
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }

    public static function getEmailSettings()
    {
        if (is_null(self::$emailSettings)) {
            self::$emailSettings = \Controller::model("GeneralData", "email-settings");
        }

        return self::$emailSettings;
    }


    public static function getSiteSettings()
    {
        if (is_null(self::$siteSettings)) {
            self::$siteSettings = \Controller::model("GeneralData", "settings");
        }

        return self::$siteSettings;
    }


    public static function getPluginSettings()
    {
        if (is_null(self::$pluginSettings)) {
            self::$pluginSettings = \Controller::model("GeneralData", "plugin-" . IDNAME . "-settings");
        }

        return self::$pluginSettings;
    }




}