<?php

namespace Plugins\chatrr;

require_once PLUGINS_PATH . "/" . IDNAME . "/lib/chatrr.php";
// Disable direct access
if (!defined('APP_VERSION'))
    die("Yo, what's up?");

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


        $AuthUser = $this->getVariable("AuthUser");

        $this->setVariable("idname", IDNAME);

        $this->setVariable("idfullname", IDFULLNAME);



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
            ->where("idname", "chatrr")
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


        // $page = isset($Route->params->page) ? $Route->params->page : "site";

        // Set variables
        $active_plugins = "";
        $this->setVariable("active_plugins", $active_plugins)
            ->setVariable("Plugins", $Plugins)
            ->setVariable("Settings", \Controller::model("GeneralData",  "plugin-" . IDNAME . "-settings"))
            ->setVariable("Integrations", \Controller::model("GeneralData", "integrations"));

        // Plugin settings
        $KSettings = namespace\settings();

        $api = new LicenseBoxAPI();
        $licensekey = $KSettings->get("data.chatrr.nextpost-marketplace-key");
        $res = array();
        $res['status']=True;
        if(empty($licensekey)){
            $res = $api->verify_license();

        }else{
            $this->setVariable("licensekey", $licensekey);
        }
        $this->setVariable("res", $res);
        if (\Input::post("action") == "activate"){
            $codesettKey= \Input::post("nextpost-marketplace-key");
            $this->activatelicenseChatrr($api,$codesettKey);
        }elseif (\Input::post("action") == "deactivate") {
            $this->deactivatelicenseChatrr($api);
        }elseif (\Input::post("action") == "checkupdate") {
            $this->checkUpdateChatrr($api);
        }elseif (\Input::post("action") == "doupdate") {
            $updateid = \Input::post("updateid");
            $hassql = \Input::post("hassql");
            $version = \Input::post("version");
            $this->doUpdateChatrr($api, $updateid, $hassql, $version);
        }elseif (\Input::post("action") == "save") {
            $this->saveChatrr();
        }


        $this->view(PLUGINS_PATH . "/" . IDNAME . "/views/index.php", null);

    }


    private function saveChatrr()
    {

        $Settings = $this->getVariable("Settings");
        if (!$Settings->isAvailable()) {
            $Settings->set("name", "plugin-" . IDNAME . "-settings");
        }


        $chathandler = strtolower(\Input::post("chatrr-chathandler"));
        $fbpageid = \Input::post("chatrr-fbpageid");
        $fbbubblecolour = \Input::post("chatrr-fbbubblecolour");
        $tidiobubblecolour = \Input::post("chatrr-tidiobubblecolour");
        $tidioid = \Input::post("chatrr-tidioid");


//
        $Settings->set("data.chatrr.chathandler", $chathandler)
            ->set("data.chatrr.fbpageid", $fbpageid)
            ->set("data.chatrr.fbbubblecolour", $fbbubblecolour)
            ->set("data.chatrr.tidiobubblecolour", $tidiobubblecolour)
            ->set("data.chatrr.tidioid", $tidioid);


    if($Settings->save()){
        $this->resp->result = 1;
        $this->resp->msg = __("Chatrr Settings updated correctly!");
        $this->jsonecho();
    }else{
        $this->resp->result = 0;
        $this->resp->msg = __("Error: could not save");
        $this->jsonecho();
    }


        return $this;
    }


    private function savelicenseChatrr($codesettKey)
    {
        $Settings = $this->getVariable("Settings");
        if (!$Settings->isAvailable()) {
            $Settings->set("name", "plugin-" . IDNAME . "-settings");
        }

        $Settings->set("data.chatrr.nextpost-marketplace-key", $codesettKey)
            ->save();


        return $this;
    }

    private function activatelicenseChatrr($api,$licensekey)
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

                $this->savelicenseChatrr($licensekey);
            }else{
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }

    private function deactivatelicenseChatrr($api)
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

                $this->savelicenseBluesnap($licensekey);
            }else{
                $this->resp->result = 0;
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }

    private function checkUpdateChatrr($api)
    {
        $this->resp->result = 0;

       // $api = new LicenseBoxAPI();
        $response = $api->check_update();

        if(empty($response))
        {

            $this->resp->result = 0;
            $message='Server is unavailable.';

            $this->resp->msg = __("". $message);
        }
        else
        {
            if ($response['status'] == 'true') {
                $message = $response['message'];
                $version = $response['version'];
                $changelog = $response['changelog'];
                $updateid = $response['update_id'];
                $hassql = $response['has_sql'];

                $this->resp->result = 1;

                $this->resp->msg = __("". $message);
                $this->resp->version = __("". $version);
                $this->resp->changelog = __("". $changelog);
                $this->resp->updateid = __("". $updateid);
                $this->resp->hassql = __("". $hassql);

            }else{
                $message = $response['message'];

                $this->resp->msg = __("". $message);
            }
        }

        $this->jsonecho();

        return $this;
    }


    private function doUpdateChatrr($api, $updateid, $hassql, $version)
    {


         //$api = new LicenseBoxAPI();
         $api->download_update($updateid, $hassql, $version);



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


    private function makeCurlRequest($url, $request_type, $api_key, $data = array())
    {
        if ($request_type == 'GET')
            $url .= '?' . http_build_query($data);

        $mch = curl_init();
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode('user:' . $api_key)
        );
        curl_setopt($mch, CURLOPT_URL, $url);
        curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($mch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
        curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
        curl_setopt($mch, CURLOPT_TIMEOUT, 100);
        curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection

        if ($request_type != 'GET') {
            curl_setopt($mch, CURLOPT_POST, true);
            curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data)); // send data in json
        }

        return curl_exec($mch);
    }

}