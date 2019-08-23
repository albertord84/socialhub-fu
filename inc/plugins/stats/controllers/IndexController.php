<?php
namespace Plugins\StatsModule;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Index Controller
 */
class IndexController extends \Controller
{
  
  public $plugins = [];

    /**
     * Process
     */
    public function process()
    {
      $Route  = $this->getVariable("Route");
      $action = isset($Route->params->id) ? $Route->params->id : 0;
      $time   = (int) \Input::get('time');
      $time   = $time == 0 || $time > 30 ? 7 : $time;
      
      $this->setVariable("idname", IDNAME);
      $this->setVariable("baseUrl", APPURL."/e/".$this->getVariable('idname'));
      $this->setVariable("hasData", false);
      $this->setVariable("time", $time);
      $this->setVariable("statsBuild", 50079); //cache control
      
      $this->_defaultPlugins();
      
      switch($action)
      {
        case 1:
          $this->cron();
          break;
        case 2:
          $this->setAction();
          break;
        default:
          $this->dashboard();
      }
    }
  
 /**
 * Run cron
 * @return void       
 */
  protected function cron($id = false)
  {
    if (\Input::get('build')) {
      echo $this->getVariable('statsBuild') . ' - ' . $GLOBALS['_PLUGINS_']['stats']['config']['version'];
      exit;
    }
    \Event::trigger("stats.cron", $id);
  }
  

  
 /**
 * Enable/disable actions
 * @return string json
 */
  protected function setAction()
  {

    header('Content-Type: application/json');
    $module     = str_replace('_switch', '', \Input::post('action'));
    $status     = (int) \Input::post('status');
    $accountId  = (int) \Input::post('accountId');
    $userId     = (int) $this->getVariable("AuthUser")->get('id');
    
    $result = [
      'status' => false,
      'msg' => ''
    ];
    
        
    if (! $this->_checkAccess(true, false, true, true))
    {
      $result['msg'] = __('You dont have access to this module.');
      echo json_encode($result);
      return;
    }
    
    if (!$module)
    {
      $result['msg'] = __('Invalid module');
      echo json_encode($result);
      return;
    }
    
    if (!isset($this->plugins[$module]['checkTable']) || ! $this->plugins[$module]['checkTable'])
    {
      $result['msg'] = __('Invalid module') . '!';
      echo json_encode($result);
      return;
    }
    
    $table = $this->plugins[$module]['checkTable'];
    if (!$this->_checkPluginInstall($module))
    {
      $result['msg'] = __('Module not available');
      echo json_encode($result);
      return;
    }
    
    
    try {
      \DB::table($table)->where('account_id', '=', $accountId)->where('user_id', '=', $userId)->update(['is_active' => $status]);
          $result = [
            'status' => true,
            'msg' => __('Changes saved successfully')
          ];
    } catch(Exception $e) {
      $result['msg'] = __('Error: ' . $e->getMessage());
    }
    
    echo json_encode($result);
    return;
  }
  
 /**
 * Get dashboard data
 * @return void       
 */
  protected function dashboard()
  {
        $this->_checkAccess(true, false, true, false);
        $daysAgo = $this->getVariable('time');
    
        // Get accounts
        $Accounts = \Controller::model("Accounts");
        $Accounts->where("user_id", "=", $this->getVariable("AuthUser")->get("id"))
                 ->orderBy("id","DESC")
                 ->fetchData();
        $this->setVariable("Accounts", $Accounts);

        // Get Active Account
        $ActiveAccount = \Controller::model("Account", (int) \Input::get("account"));
        $foreRefresh   = (bool) \Input::get("forceRefresh");

        if (!$ActiveAccount->isAvailable() || $ActiveAccount->get("user_id") != $this->getVariable("AuthUser")->get("id"))
        {   
            $data = $Accounts->getDataAs("Account");
          
            if (isset($data[0]))
            {
                $ActiveAccount = $data[0];
            }
        }

        
        if ($foreRefresh)
        {
          $this->cron($ActiveAccount->get('id'));
          header("Location: " . $this->getVariable('baseUrl') . '?account='.$ActiveAccount->get('id'));
          exit;
        }
    
        $this->setVariable("plugins", $this->plugins);
        $this->setVariable("ActiveAccount", $ActiveAccount);
        $this->setVariable("activeAccountId", $ActiveAccount->get('id'));
        $this->setVariable("hasData", false);
        $this->setVariable("hasStats", false);
        $this->setVariable("latestStats", []);
        $this->setVariable("statsData", []);
        $this->setVariable("profileInfo", json_decode(''));
        $this->setVariable("dateFormat", $this->getVariable('AuthUser')->get("preferences.dateformat"));
        $this->setVariable("lastUpdate", false);
    
        //check if has stats
        if ( ! $this->_checkStats($ActiveAccount->get('id')))
        {
            //run stats for the first time
            $this->cron($ActiveAccount->get('id'));

            //try again
            if ( ! $this->_checkStats($ActiveAccount->get('id')))
            {
              $this->view(PLUGINS_PATH."/".$this->getVariable("idname")."/views/index.php", null);
              return;
            }
        }
    $this->setVariable("hasData", true);
        //get stats
        $stats = $this->_getStats($ActiveAccount->get('id'), $daysAgo);
        if ($stats)
        {
            $this->setVariable("hasStats", true);
            $this->setVariable("latestStats", $stats[0]);
            $this->setVariable("statsData", array_reverse($stats));
          
            $profileInfo = json_decode($stats[0]['ig_data']);
            foreach($profileInfo->feed as $k => $v)
            {
              $profileInfo->feed[$k]->embed = $this->getIgEmbed($v->media_id);
            }
          
            $this->setVariable("profileInfo", $profileInfo);
  
            $date = new \DateTime();
            $dt = $date->modify($stats[0]['date'])
              ->setTimezone(new \DateTimeZone($this->getVariable('AuthUser')->get('preferences.timezone')))
              ->format($this->getVariable('dateFormat') . ($this->getVariable('AuthUser')->get("preferences.timeformat") == "24" ? " H:i:s" : " h:i A"));
            $this->setVariable("lastUpdate", $dt);
        }
    
        //check install
        $this->_checkPluginInstall();
    
        
        // count actions and check status
        foreach($this->plugins as $k => $v)
        {
          if ($v['installed'] && $v['countTable'])
          {
            if (isset($v['columnDate']))
            {
              $this->plugins[$k]['count'] = $this->_countPluginActions($v['countTable'], 'published', $daysAgo, $v['columnDate']);
            }
            else
            {
              $this->plugins[$k]['count'] = $this->_countPluginActions($v['countTable'], 'success', $daysAgo);
            }
          }
          
          //status
          if ($v['installed'] && $v['countTable'] && ! $v['isCore'])
          {
            $this->plugins[$k]['active'] = $this->_checkPluginStatus($v['checkTable'], $ActiveAccount->get('id'));
          }
          
        }

        $this->setVariable("plugins", $this->plugins);
        $this->view(PLUGINS_PATH."/".$this->getVariable("idname")."/views/index.php", null);
  }


 /**
 * Check access
 * @return bool|void       
 */
  protected function _checkAccess($checkExpired = false, $checkAdmin = false, $checkModule = false, $return = false)
  {
      // Auth
      if (!$this->getVariable('AuthUser')){
        if ($return) {
          return false;
        }
        header("Location: ".APPURL."/login");
        exit;
      }

      if ($checkExpired && $this->getVariable('AuthUser')->isExpired()) {
          if ($return) {
            return false;
          }
          header("Location: ".APPURL."/expired");
          exit;
      }

      if ($checkAdmin && !$this->getVariable('AuthUser')->isAdmin()) {
          if ($return) {
            return false;
          }
          header("Location: ".APPURL."/post");
          exit;
      }

      if ($checkModule) {
          // Get the list of modules which is accessible for this authenticated user
          $user_modules = $this->getVariable('AuthUser')->get("settings.modules");
          if (!is_array($user_modules) || !in_array($this->getVariable("idname"), $user_modules)) {
            if ($return) {
              return false;
            }
              // Module is not accessible to this user
              header("Location: ".APPURL."/post");
              exit;
          }
      }
      return true;

  }
  
 /**
 * Check is plugin is installed
 * @param string $plugin
 * @return string|bool
 */
  protected function _checkPluginInstall($plugin = '')
  {
      
      if ($plugin)
      {
        if (! isset($this->plugins[$plugin]['checkTable']))
        {
          return false;
        }
        $pluginTable = $this->plugins[$plugin]['checkTable'];
        
        $query = "SHOW TABLES LIKE '$pluginTable'";
        
        $result = \DB::query($query)->get() ? true : false;
        $this->plugins[$plugin]['installed'] = $result;
        
        return $result;
      }

    $userModules = $this->getVariable('AuthUser')->get('settings.modules');
    foreach($this->plugins as $k => $v)
    {
      if(!in_array($k, $userModules) && !$v['isCore'])
      {
        $this->plugins[$k]['hasAccess'] = false;
        continue;
      }
      else
      {
        $this->plugins[$k]['hasAccess'] = true;
      }
      if ($v['installed'] || $v['isCore'])
      {
        continue;
      }
      
      $pluginTable = $this->plugins[$k]['checkTable'];
      
      //check if is installed
      $query = "SHOW TABLES LIKE '{$pluginTable}'";
      $result = \DB::query($query)->get() ? true : false;
      $this->plugins[$k]['installed'] = $result;  
      
    }
    
    return true;
  }
  
 /**
 * Check if has at least one stats
 * @return bool       
 */
  protected function _checkStats($accountId = 0)
  {
    if (!$accountId)
    {
      return false;
    }

    $tbStats = TABLE_PREFIX.'stats';
    $sql = "SELECT COUNT(id) AS total FROM {$tbStats} WHERE account_id={$accountId} LIMIT 1";
        
    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $res = $stmt->fetchAll();

    return isset($res[0]['total']) && $res[0]['total'];

  }
  
 /**
 * Count aactions runned by each plugin
 * @param string $plugin plugin name
 * @param string $status
 * @param int $daysAgo 
 * @param string $dateColumn 
 * @return int       
 */
  protected function _countPluginActions($plugin, $status = 'success', $daysAgo = 0, $dateColumn = '')
  {

      if ( !$plugin)
      {
          return false;
      }
    
      $dateColumn = $dateColumn ? $dateColumn : 'date';
    
      $where = ' WHERE account_id=' . $this->getVariable('ActiveAccount')->get('id').' ';

      if ($status)
      {
        $where .= " AND status='{$status}' ";
      }

      if ($daysAgo) {
          $time = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
          $where .= " AND {$dateColumn} >='{$time}'";
      }

      $sql = "SELECT COUNT(id) AS total FROM {$plugin}  {$where}";

      $pdo = \DB::pdo();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $res = $stmt->fetchAll();

      return isset($res[0]['total']) ? $res[0]['total'] : 0;

  }

 /**
 * Check if plugin is active
 * @param string $plugin
 * @param int $accountId 
 * @return void       
 */
  protected function _checkPluginStatus($plugin = '', $accountId = false)
  {

      if ( !$plugin || !$accountId)
      {
          return false;
      }

      $sql = "SELECT is_active FROM {$plugin} WHERE account_id={$accountId} limit 1";

      $pdo = \DB::pdo();
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $res = $stmt->fetchAll();

      return isset($res[0]['is_active']) && $res[0]['is_active'];

  }
  
 /**
 * List of plugins
 * @return void       
 */
  protected function _defaultPlugins()
  {
      $this->plugins = [
        'auto-comment' => [
          'title'       => __('Auto Comment'),
          'checkTable' => TABLE_PREFIX . 'auto_comment_schedule',
          'countTable' => TABLE_PREFIX . 'auto_comment_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'mdi mdi-comment-processing' 
        ],
        'auto-follow' => [
          'title'       => __('Auto Follow'),
          'checkTable' => TABLE_PREFIX . 'auto_follow_schedule',
          'countTable' => TABLE_PREFIX . 'auto_follow_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'sli sli-user-follow'
        ],
        'auto-unfollow' => [
          'title'       => __('Auto Unfollow'),
          'checkTable' => TABLE_PREFIX . 'auto_unfollow_schedule',
          'countTable' => TABLE_PREFIX . 'auto_unfollow_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'sli sli-user-unfollow'
        ],
        'auto-like' => [
          'title'       => __('Auto Like'),
          'checkTable' => TABLE_PREFIX . 'auto_like_schedule',
          'countTable' => TABLE_PREFIX . 'auto_like_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'sli sli-like'
        ],
        'auto-repost' => [
          'title'       => __('Auto Repost'),
          'checkTable' => TABLE_PREFIX . 'auto_repost_schedule',
          'countTable' => TABLE_PREFIX . 'auto_repost_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'sli sli-reload'
        ],
        'welcomedm' => [
          'title'       => __('Welcome DM'),
          'checkTable' => TABLE_PREFIX . 'welcomedm_schedule',
          'countTable' => TABLE_PREFIX . 'welcomedm_log',
          'installed' => false,
          'active' => false,
          'hasAccess' => false,
          'count'     => 0,
          'isCore'    => false,
          'iconClass' => 'sli sli-paper-plane'
        ],
        'post' => [
          'title'       => __('Posts'),
          'checkTable' => TABLE_PREFIX . TABLE_POSTS,
          'countTable' => TABLE_PREFIX . TABLE_POSTS,
          'installed' => true,
          'active' => true,
          'hasAccess' => true,
          'count'     => 0,
          'isCore'    => true,
          'columnDate' => 'create_date',
          'iconClass' => 'sli sli-plus menu-icon'
        ]
      ];
  }
  
 /**
 * Get Statistics Data
 * @param int $accountId
 * @param int $daysAgo
 * @param string $limit - mysql formmat
 * @return void       
 */
  protected function _getStats($accountId = false, $daysAgo = false, $limit = '')
  {
    if (! $accountId)
    {
      return false;
    }
    $tbStats = TABLE_PREFIX.'stats';
    $limit = $limit ? " LIMIT {$limit} " : "";
    $where = " WHERE S.account_id={$accountId} ";
    if ($daysAgo) {
      $time = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
      $where .= " AND S.date >='{$time}'";
    }

    $sql = "
      SELECT
        DATE(S.date) AS dt,
        MAX(S.id) AS id,
        MAX(S.account_id) AS account_id,
        MAX(S.followers) AS followers,
        MAX(S.followings) AS followings,
        MAX(S.posts) AS posts,
        MAX(S.followers_diff) AS followers_diff,
        MAX(S.followings_diff) AS followings_diff,
        MAX(S.posts_diff) AS posts_diff,
        MAX(S.date) AS date,
        MAX(S.ig_data) AS ig_data
      FROM {$tbStats} S
      {$where}
      GROUP BY DATE(S.date)
      ORDER BY DATE(S.date) DESC
      {$limit}
    ";
    
    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $res = $stmt->fetchAll();
       
    return $res;

  }
 
/**
 * Get Instagram embed code
 * @param string $mediaCode
 * @return string 
*/
  protected function getIgEmbed($mediaCode) {

        $url = 'https://api.instagram.com/oembed/?url=http://instagr.am/p/' . $mediaCode . '/&hidecaption=true&maxwidth=450';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($data);
        return $response->html;

    }
  
}