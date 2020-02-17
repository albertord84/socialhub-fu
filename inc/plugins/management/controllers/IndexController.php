<?php
namespace Plugins\ManagementModule;

// Disable direct access
if (!defined('APP_VERSION')) 
    die("Yo, what's up?");

/**
 * Index Controller
 */
class IndexController extends \Controller
{

    private $tbUsers    = TABLE_PREFIX . TABLE_USERS;
    private $tbPacks    = TABLE_PREFIX . TABLE_PACKAGES;
    private $tbAccounts = TABLE_PREFIX . TABLE_ACCOUNTS;
    private $tbPosts    = TABLE_PREFIX . TABLE_POSTS;
    private $tbAffiliate= TABLE_PREFIX . "affiliate";
    private $tbPlugins  = TABLE_PREFIX . "plugins";
    private $pluginComment;
    private $pluginFollow;
    private $pluginUnfollow;
    private $pluginLike;
    private $pluginPost;
    private $pluginWelcome;
    private $pluginRepost;

    /**
     * Process
     */
    public function process()
    {

        //global vars
        $this->setVariable("idname", "management");
        $this->setVariable("baseUrl", APPURL."/e/".$this->getVariable('idname'));

        $action = \Input::get("a");
        $action = $action ? $action : 'dashboard';
        $this->setVariable('action', $action);
        $this->setVariable('info', []);

        //define action
        switch ($action)
        {
            case 'dashboard' :
                $_SESSION['UriManagement'] = 'dashboard';
                $this->getDashboard();
                break;
            case 'users' :
                $_SESSION['UriManagement'] = 'users';
                $this->getUsers();
                break;
            case 'usersAjax' :
                header('Content-Type: application/json');
                echo json_encode($this->getUsers(true));
                exit;
                break;
            case 'loginAs' :
                $this->loginAs(\Input::get('id'));
                break;
            case 'backadm' :
                $this->reloginAdmin();
                break;
            case 'accounts' :
                $_SESSION['UriManagement'] = 'accounts';
                $this->getAccounts();
                break;
            case 'accountsAjax' :
                header('Content-Type: application/json');
                echo json_encode($this->getAccounts(true));
                exit;
                break;
            case 'updateProxy' :
                $this->updateProxy();
                break;
            case 'reports' :
                $_SESSION['UriManagement'] = 'reports';
                $this->getReports();
                break;
            case 'maintenance' :
                $_SESSION['UriManagement'] = 'maintenance';
                $this->maintenanceSystem();
                break;
            default:
                header("Location: ".APPURL."/post");
                $this->getDashboard();
        }

        $this->view(PLUGINS_PATH."/".$this->getVariable("idname")."/views/index.php", null);
    }

    //list all users
    protected function getUsers($ajaxData = false)
    {
        if ($ajaxData) {
            if (!$this->_checkAccess(true, true, true, true)) {
                return ['data' => []];
            }
        } else {
            $this->_checkAccess(true, true, true);
            $this->setVariable('ajaxUrl', $this->getVariable('baseUrl') . '?a=usersAjax');
        }

        $query = "
            SELECT
                U.*,
                IF(U.expire_date < NOW(), 'yes', 'no') AS expired,
                P.title AS package,
                COUNT(A.id) AS nr_accounts
            FROM {$this->tbUsers} U
            LEFT JOIN {$this->tbPacks} P ON U.package_id=P.id
            LEFT JOIN {$this->tbAccounts} A ON U.id=A.user_id
            GROUP BY U.id

        ";

        $res = \DB::query($query)->get();
        if ( ! $ajaxData) {
            $this->setVariable('info', $res ? $res : []);
            return ['data' => []];
        }

        if ( ! $res) {
            return ['data' => []];
        }

        $data = [];
        foreach ($res as $r)
        {
            $btn =  '<a class="small button button--light-outline button--oval" href="' . APPURL . '/users/' . $r->id . '">' . __('edit') . '</a>';
            $btn .=  '<a class="small button button--light-outline button--oval" href="' . ($this->getVariable('AuthUser')->get('id') == $r->id ? 'javascript:void(0)' : ($this->getVariable('baseUrl') . '?a=loginAs&id=' . $r->id)) . '">' . __('login') . '</a>';

            $delta = 1 * 24 * 60 *60;
            $start_time = strtotime($r->date);
            $addOneDay  = date("Y-m-d H:i:s", $start_time + $delta);
            
            if ($addOneDay < date("Y-m-d H:i:s")) {
                $msg = "Hi,%20".$r->firstname.".%20Did%20you%20enjoy%20using%20our%20tool,%20Instarget?";
            } else {
                $msg = "Hi,%20".$r->firstname.".%20Welcome%20to%20Instarget.%20If%20you%20need%20support%20and%20configuration%20tips%20I'm%20here.";
            }

            if (isset($r->phone_number)) {
                if ($r->phone_number != '000000.0000') {
                    $whatsapp = "<br /><a href='http://api.whatsapp.com/send?1=en_US&phone=55".str_replace(['(', ')', '.', ' '], ['', '','',''], $r->phone_number)."&text=".$msg."' target='_blank'>".$r->phone_number."</a>";
                }                
            } else {
               $whatsapp = "";
            }
            
            $row = [
                $r->id,
                $r->firstname . ' ' . $r->lastname,
                $r->email.$whatsapp,
                $r->expired == 'no' ? ('<span class="small button button--light-outline btn-no button--oval">' . __('no') . '</span>') : ('<span class="small button button--light-outline btn-yes button--oval">' . __('yes') . '</span>'),
                $r->account_type,
                $r->nr_accounts,
                $r->package,
                $btn
            ];
            $data[] = $row;
        }
        return ['data' => $data];
    }


    //login into user account
    protected function loginAs($id = 0)
    {
        $this->_checkAccess(true, true, true);

        $AuthUser = $this->getVariable("AuthUser");

        $User = \Controller::model("User", $id);

        if (!$User->isAvailable())
        {
            $this->setVariable('loginError', true);
            return;
        }

        $remember = false;

        $exp = $remember ? time()+86400*30 : 0;
        setcookie("nplh", $User->get("id").".".md5($User->get("password")), $exp, "/");

        if($remember) {
            setcookie("nplrmm", "1", $exp, "/");
        } else {
            setcookie("nplrmm", "1", time() - 30*86400, "/");
        }

        $_SESSION['nprl'] = $AuthUser->get('id');

        if (!isset($_SESSION['isAdmin'])) {
            $_SESSION['isAdmin'] = true;
        }

        header("Location: ".APPURL."/post");
        exit;
    }

    //login back to admin
    protected function reloginAdmin()
    {
        $this->_checkAccess();
        $id = isset($_SESSION['nprl']) ? $_SESSION['nprl'] : null;
        if(!$id)
        {
            echo _('Invalid user');
            return;
        }

        $User = \Controller::model("User", $id);
        if (! $User || ! $User->isAdmin())
        {
            echo _('You cannot relogin now');
            return;
        }
        $remember = true;

        $exp = $remember ? time()+86400*30 : 0;
        setcookie("nplh", $User->get("id").".".md5($User->get("password")), $exp, "/");

        if($remember) {
            setcookie("nplrmm", "1", $exp, "/");
        } else {
            setcookie("nplrmm", "1", time() - 30*86400, "/");
        }

        unset($_SESSION['nprl']);

        if (isset($_SESSION['isAdmin'])) {
            $_SESSION['isAdmin'] = null; unset($_SESSION['isAdmin']);
        }

        header("Location: ".$this->getVariable('baseUrl')."?a=".$_SESSION['UriManagement']);
        exit;
    }


    //list all accounts
    protected function getAccounts($ajaxData = false)
    {

        if (\Input::get('id')) {
            $this->_checkAccess(true, true, true);
            $this->getDetailAccount(\Input::get('id'));
            $_SESSION['UriManagement'] = "accounts&id=".\Input::get('id');
            return;
        }

        if ($ajaxData) {
            if (!$this->_checkAccess(true, true, true, true)) {
                return ['data' => []];
            }
        } else {
            $this->_checkAccess(true, true, true);
            $this->setVariable('ajaxUrl', $this->getVariable('baseUrl') . '?a=accountsAjax');
        }

        $this->setVariable('itemDetail', false);

        $query = "
            SELECT
                A.*,
                U.firstname,
                U.lastname,
                U.is_active AS user_is_active,
                IF(U.expire_date < NOW(), 'yes', 'no') AS expired,
                P.title AS package
            FROM {$this->tbAccounts} A
            INNER JOIN {$this->tbUsers} U ON U.id=A.user_id
            LEFT JOIN {$this->tbPacks} P ON U.package_id=P.id
            GROUP BY A.id

        ";

        $res = \DB::query($query)->get();
        if ( ! $ajaxData) {
            $this->setVariable('info', $res ? $res : []);
            return ['data' => []];
        }

        if ( ! $res) {
            return ['data' => []];
        }

        $data = [];
        foreach ($res as $r)
        {
            $btn =  '<a class="small button button--light-outline button--oval" href="'. $this->getVariable('baseUrl') . "?a=accounts&id=" . $r->id.'">'.__('Details').'</a>';
            $row = [
                $r->id,
                '<a href="https://www.instagram.com/'.$r->username.'" target="_blank" title="'.__('See on Instagram').'">@'.$r->username.'</a><br /><a style="color: #AFC4DE;" href="'.$this->getVariable('baseUrl').'?a=accounts&id='.$r->id.'">'.$r->proxy.'</a>',
                '<a href="'.$this->getVariable('baseUrl').'?a=loginAs&id='.$r->user_id.'" title="'.__('Click here to login').'">'.$r->firstname." ".$r->lastname.'</a>',
                $r->expired == 'no' ? ('<span class="small button button--light-outline btn-yes button--oval">' . __('no') . '</span>') : ('<span class="small button button--light-outline btn-no button--oval">' . __('yes') . '</span>'),
                ! $r->login_required ? ('<span class="small button button--light-outline btn-yes button--oval">' . __('no') . '</span>') : ('<span class="small button button--light-outline btn-no button--oval">' . __('yes') . '</span>'),
                $btn
            ];
            $data[] = $row;
        }
        return ['data' => $data];
    }

    // maintenance
    protected function maintenanceSystem()
    {
        $this->_checkAccess(true, true, true);
        $info = [
            'action'    => \Input::post('maintenance'),
            'days'      => \Input::post('days'),
            'type'      => \Input::post('type'),
            'search'    => \Input::post('search'),
            'posts'     => \Input::post('posts'),
            'limit'     => \Input::post('limit'),
        ];

        $this->resp->result = 1;

        if ($info['days']) {
            $delta = $info['days'] * 24 * 60 *60;
            //$delta = 18 * 24 * 60 *60;    
            $start_time = strtotime("Y-m-d H:i:s");
            if ($start_time < time()) {
                $start_time = time();
            }
            $dateMinDay = date("Y-m-d H:i:s", $start_time - $delta);             
        }

        if (!$info['limit']) {
            $limit = "LIMIT 50";   
        } else {
            if ($info['limit'] == 'all') {
                $limit = "";
            } else {
                $limit = "LIMIT ".$info['limit'];
            }            
        }

        if ($info['action'] == 'users')
        {       
            $countDel = 0; 
            $countAffiliate = 0;

            $where = "WHERE DATE(expire_date) <= '{$dateMinDay}'";
            $sql = "
                SELECT * FROM {$this->tbUsers}
                {$where}
                ORDER BY DATE(date) ASC
                {$limit}
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll();

            if(count($res) <= 0) {
                $this->resp->result = 2;
                $this->resp->msg = __('No users found!');
                $this->jsonecho();
            }

            foreach ($res as $v) {
                $id = $v['id'];
                //$id = 657;
                $where = "WHERE user_id = '{$id}' OR ref_user_id = '{$id}'";
                $sql = "
                    SELECT * FROM {$this->tbAffiliate}
                    {$where}
                    ORDER BY DATE(date) ASC 
                    LIMIT 1
                ";

                $pdo = \DB::pdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $res = $stmt->fetchAll();

                if(count($res) > 0) {
                    $countAffiliate++;
                } else {
                    if (!$info['search']) {
                        $where = "WHERE id = '{$id}'";
                        $sql = "
                            DELETE FROM {$this->tbUsers} {$where}
                        ";

                        $pdo = \DB::pdo();
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute();
                    }
                    $countDel++; 
                }
                //$this->resp->msg .= $v['id'].'<br />'; 
            }

            if (!$info['search']) {
                if ($countAffiliate > 1) {
                    $msgAffiliate = $countAffiliate.__(' are affiliated.');
                } else {
                    if ($countAffiliate == 1) {
                        $msgAffiliate = $countAffiliate.__(' is affiliated.');
                    } else {
                        $msgAffiliate = __('No affiliates.');
                    }                
                }

                if ($countDel > 1) {
                    $msgUserDel = $countDel.__(' were deleted');
                } else {
                    if ($countDel == 1) {
                        $msgUserDel = $countDel.__(' user has been deleted');
                    } else {
                        $msgUserDel = __('No user');
                    }
                }   
            } else {
                $this->resp->result = 2;
                if ($countAffiliate > 1) {
                    $msgAffiliate = $countAffiliate.__(' are affiliated.');
                } else {
                    if ($countAffiliate == 1) {
                        $msgAffiliate = $countAffiliate.__(' is affiliated.');
                    } else {
                        $msgAffiliate = __('No affiliates.');
                    }                
                }

                if ($countDel > 1) {
                    $msgUserDel = $countDel.__(' users found');
                } else {
                    if ($countDel == 1) {
                        $msgUserDel = $countDel.__(' user found');
                    } else {
                        $msgUserDel = __('No users found');
                    }
                }
            }
            

            $this->resp->msg = $msgUserDel ." & ". $msgAffiliate; //__('users');
            $this->jsonecho();
        }
        else if ($info['action'] == 'logs') 
        {
            $tables = [
                'auto-follow'=>TABLE_PREFIX . 'auto_follow_log',
                'auto-unfollow'=>TABLE_PREFIX . 'auto_unfollow_log',
                'auto-like'=>TABLE_PREFIX . 'auto_like_log',
                'auto-comment'=>TABLE_PREFIX . 'auto_comment_log',
                'auto-repost'=>TABLE_PREFIX . 'auto_repost_log',
                'welcomedm'=>TABLE_PREFIX . 'welcomedm_log'
            ];

            $tb = $tables[$info['type']];
            $where = "WHERE DATE(date) <= '{$dateMinDay}'";
            $sql = "
                SELECT * FROM {$tb}
                {$where}
                ORDER BY DATE(date) ASC
                {$limit}
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll();

            if(count($res) <= 0) {
                $this->resp->result = 2;
                $this->resp->msg = __('None Log found!');
                $this->jsonecho();
            }

            if (!$info['search']) {
                $where = "WHERE DATE(date) <= '{$dateMinDay}'";
                $sql = "
                    DELETE FROM {$tb} {$where}
                ";

                $pdo = \DB::pdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $this->resp->msg = count($res).__(" logs deleted.");
            } else {
                $this->resp->result = 2;
                $this->resp->msg = count($res).__(" logs found.");
            }
            
            $this->jsonecho();
        }
        else if ($info['action'] == 'posts-posted') 
        {
            $dateHoje = date('Y-m-d H:i:s');            
            if ($info['posts'] == 'published') {
                $where = "WHERE DATE(publish_date) <= '{$dateMinDay}'";
            }else if ($info['posts'] == 'unpublished') {
                $where = "WHERE DATE(schedule_date) >= '{$dateHoje}'";
            } else {
                $this->resp->result = 2;
                $this->resp->msg = __('Select whether the post is Published or not Posted.');
                $this->jsonecho();
            }

            $sql = "
                SELECT * FROM {$this->tbPosts} 
                {$where}
                ORDER BY DATE(create_date) ASC
                {$limit}
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll();

            if(count($res) <= 0) {
                $this->resp->result = 2;
                $this->resp->msg = __('No posts found!');
                $this->jsonecho();
            }

            if (!$info['search']) {
                $sql = "
                    DELETE FROM {$this->tbPosts} {$where}
                ";

                $pdo = \DB::pdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute();

                $this->resp->msg = count($res).__(" post(s) deleted.");
            } else {
                $this->resp->result = 2;
                $this->resp->msg = count($res).__(" post(s) found.");
            }

            $this->jsonecho();

        } else {
            $plugins = ['auto-follow', 'auto-like', 'auto-unfollow', 'auto-comment', 'auto-repost', 'welcomedm'];
            $sql = "
                SELECT * FROM {$this->tbPlugins}
                ORDER BY id ASC
            ";
            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll();

            $name = ['auto-follow'=>__('Auto Follow'), 'auto-like'=>__('Auto Like'), 'auto-unfollow'=>__('Auto Unfollow'), 'auto-comment'=>__('Auto Comment'), 'auto-repost'=>__('Auto Repost'), 'welcomedm'=>__('Auto DM')];
            $modules = '';
            foreach ($res as $v) {
                if (in_array($v['idname'], $plugins)) {
                    $modules .= '<option value="'.$v['idname'].'">'.$name[$v['idname']].'</option>';
                }
            }

            $delta = 30 * 24 * 60 *60;   
            $start_time = strtotime("Y-m-d H:i:s");
            if ($start_time < time()) {
                $start_time = time();
            }
            $dateMinDay = date("Y-m-d H:i:s", $start_time - $delta);  

            // Usuários e Afiliados
            $countAffiliate = 0;
            $countDel = 0;
            $where = "WHERE DATE(expire_date) <= '{$dateMinDay}'";
            $sql = "
                SELECT * FROM {$this->tbUsers}
                {$where}
                ORDER BY DATE(date) ASC
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $res = $stmt->fetchAll();

            $countUsers = count($res);

            foreach ($res as $v) {
                $id = $v['id'];
                //$id = 657;
                $where = "WHERE user_id = '{$id}' OR ref_user_id = '{$id}'";
                $sql = "
                    SELECT * FROM {$this->tbAffiliate}
                    {$where}
                    ORDER BY DATE(date) ASC 
                    LIMIT 1
                ";

                $pdo = \DB::pdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
                $res = $stmt->fetchAll();

                if(count($res) > 0) {
                    $countAffiliate++;
                } else {
                    $countDel++; 
                }
                //$this->resp->msg .= $v['id'].'<br />'; 
            }

            if ($countDel > 1) {
                $msgUsers = $countDel.__(' users');
            } else {
                if ($countDel == 1) {
                    $msgUsers = $countDel.__(' user');
                } else {
                    $msgUsers = __('No users');
                }                
            }

            if ($countAffiliate > 1) {
                $msgAffiliate = $countAffiliate.__(' are affiliated.');
            } else {
                if ($countAffiliate == 1) {
                    $msgAffiliate = $countAffiliate.__(' is affiliated.');
                } else {
                    $msgAffiliate = __('No affiliates.');
                }                
            }

            if ($countUsers <= 0) {
                $expireUsers = $msgUsers." & ".$msgAffiliate;
            } else {
                $expireUsers = "Total: ".$countUsers.__(" being ").$msgUsers." & ".$msgAffiliate;
            }  

            // Postagens
            $dateHoje = date('Y-m-d H:i:s');

            // Publicados
            $where = "WHERE DATE(publish_date) <= '{$dateMinDay}'";
            $sql = "
                SELECT * FROM {$this->tbPosts} 
                {$where}
                ORDER BY DATE(create_date) ASC
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $postsPublished = count($stmt->fetchAll());

            // Não Publicados
            $where = "WHERE DATE(schedule_date) >= '{$dateHoje}'";
            $sql = "
                SELECT * FROM {$this->tbPosts} 
                {$where}
                ORDER BY DATE(create_date) ASC
            ";        

            $pdo = \DB::pdo();
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $postsUnpublished = count($stmt->fetchAll());

            $this->setVariable('postsUnpublished', $postsUnpublished);
            $this->setVariable('postsPublished', $postsPublished);
            $this->setVariable('expireUsers', $expireUsers);
            $this->setVariable('modules', $modules);
        }
    }

    //reports
    protected function getReports()
    {

        $this->_checkAccess(true, true, true);
        $info = [
            'action'    => \Input::post('report'),
            'groupBy'   => \Input::post('group_by'),
            'time'      => \Input::post('time'),
            'viewType'  => 'result',
            'dateFormat'=> $this->getVariable('AuthUser')->get("preferences.dateformat"),
            'dateObj'   => new \DateTime()
        ];
        $time = explode(" - ", \Input::post('time'));
      
        if ($info['action'] && (!is_array($time) || sizeof($time) != 2))
        {
          $info['data'] = [];
          $info['title'] = __('Type a Valide Day Range');
          $this->setVariable('info', $info);
          return;

        }

        if ($info['action'] == 'users')
        {
            $info['data'] = $this->getUsersByTime($info['groupBy'], $time);
            $info['title'] = $info['groupBy'] == 'day' ? __('New users by day') : __('New users by month');
            $info['title'] .= ' (' . $info['time'] . ')';
        }
        elseif ($info['action'] == 'accounts')
        {
            //$_POST ? pre($_POST) : false;
            $info['data'] = $this->getUsersByTime($info['groupBy'], $time);
            $info['title'] = $info['groupBy'] == 'day' ? __('New accounts by day') : __('New accounts by month');
            $info['title'] .= ' (' . $info['time'] . ')';
        }
        elseif ($info['action'] == 'actions')
        {
            $this->_checkPlugins();
            $plugin = $info['source'] = \Input::post('action');


            switch ($plugin)
            {
                case 'pluginComment':
                    $info['title'] = __('Comments') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginLike':
                    $info['title'] = __('Likes') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginUnfollow':
                    $info['title'] = __('Unfollows') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginFollow':
                    $info['title'] = __('Follows') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginPosts':
                    $info['title'] = __('Posts') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginRepost':
                    $info['title'] = __('Reposts') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
                case 'pluginWelcome':
                    $info['title'] = __('Welcome Message') . ' ' .  ($info['groupBy']  == 'day' ? __('by day') : __('by month'));
                    break;
            }
            $info['data'] = $this->getActionsByTime($plugin, $info['groupBy'], $time);

        }
        elseif($info['action'] == 'accounts_users')
        {
            $info['data'] = $this->getUsersAccountsByTime($info['groupBy'], $time);
            $info['title'] = $info['groupBy'] == 'day' ? __('New Users x New Accounts by Day') : __('New Users x New Accounts by Month');
            $info['title'] .= ' (' . $info['time'] . ')';
        }
        else
        {
            $this->_checkPlugins();

            $plugins = ['pluginPosts' => __('Posts')];

            $this->pluginComment    ? $plugins['pluginComment'] = __('Comments') : false;
            $this->pluginFollow     ? $plugins['pluginFollow']  = __('Auto Follow') : false;
            $this->pluginUnfollow   ? $plugins['pluginUnfollow']= __('Auto Unfollow') : false;
            $this->pluginLike       ? $plugins['pluginLike']    = __('Auto Like') : false;
            $this->pluginWelcome    ? $plugins['pluginWelcome'] = __('Welcome Message') : false;
            $this->pluginRepost     ? $plugins['pluginRepost']  = __('Auto Repost') : false;

            $info['plugins'] = $plugins;
            $info['viewType'] = 'form';
        }


        $this->setVariable('info', $info);
    }

    public function setFone($val, $dest='link') {
        $val = preg_replace('%[^0-9]%iUs','',$val);
        if ($dest == 'link') {
            if (substr($val, 0, 2) == '55') {
                return $val;
            } else {
                return '55' . $val;
            }
        } else {
            $ddd = null;
            $nono= null;
            $lenght = strlen($val);

            if($lenght<8)
                return $val;

            if($lenght>12){
                $val = substr($val,2);
            }
            if(substr($val,0,1)=='0') {
                $val = substr($val, 1);
            }
            $lenght = strlen($val);
            if($lenght>9){
                $ddd = substr($val,0,2);
                $val = substr($val, 2);
                $lenght = strlen($val);
            }

            if($lenght==9){
                $val = substr($val,1,8);
                $nono = '9';
            }

            if(empty($val)){
                return $val;
            }
            return ($ddd!==null?'('.$ddd.') ':'').($nono!==null?$nono.' ':'').substr($val,0,4).'-'.substr($val,-4);
        }
    }


    //get details of selected account
    protected function getDetailAccount($id = null)
    {

        $this->_checkAccess(true, true, true);


        $query = "
            SELECT
                A.*,
                U.firstname,
                U.is_active AS user_is_active,
                IF(U.expire_date < NOW(), 'yes', 'no') AS expired,
                P.title AS package
            FROM {$this->tbAccounts} A
            INNER JOIN {$this->tbUsers} U ON U.id=A.user_id
            LEFT JOIN {$this->tbPacks} P ON U.package_id=P.id
            WHERE A.id={$id}
            GROUP BY A.id
        ";
        $res = \DB::query($query)->get();
        $this->setVariable('info', $res ? $res : []);
        $this->setVariable('itemDetail', true);
    }

    //updat proxy
    protected function updateProxy()
    {


        $accountId = \Input::post('account_id');
        $proxy = \Input::post('proxy');

        if ( ! $accountId) {
            header("Location: ".$this->getVariable('baseUrl') . '?a=accounts&msg=invalid_data');
            exit;

        }


        $Account = \Controller::model('Account', $accountId);

        if (!$Account->isAvailable()) {
            header("Location: ".$this->getVariable('baseUrl') . '?a=accounts&msg=invalid_account');
            exit;
        }
        var_dump($Account->isAvailable());

        if (!isValidProxy($proxy)) {
            header("Location: ".$this->getVariable('baseUrl') . '?a=accounts&msg=Proxy is not valid or active!&id=' . $accountId);
            exit;
        }

        $res = $Account->set("proxy", $proxy)->save();
        header("Location: ".$this->getVariable('baseUrl') . '?a=accounts&msg=save_success&id=' . $accountId);
        exit;
    }


    //verify access
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
/*
        if ($checkModule) {
            // Get the list of modules which is accessible for this authenticated user
            $user_modules = $this->getVariable('AuthUser')->get("settings.modules");
            if (!is_array($user_modules) || !in_array($this->getVariable("idname"), $user_modules)) {
                // Module is not accessible to this user
                if ($return) {
                    return false;
                }
                header("Location: ".APPURL."/post");
                exit;
            }
        }
*/
        return true;

    }
    
    protected function getDashboard()
    {
        $this->_checkAccess(true, true, true);
        $this->_checkPlugins();
        $daysAgo = 30;

        $result = [
            'viewType'      => 'form',
            'countComments' => $this->countPluginActions('pluginComment', 'success', $daysAgo),
            'countPosts'    => $this->countPluginActions('pluginPost', 'published', $daysAgo),
            'countLikes'    => $this->countPluginActions('pluginLike', 'success', $daysAgo),
            'countFollows'  => $this->countPluginActions('pluginFollow', 'success', $daysAgo),
            'countUnfollows'=> $this->countPluginActions('pluginUnfollow', 'success', $daysAgo),
            'countWelcome'  => $this->countPluginActions('pluginWelcome', 'success', $daysAgo),
            'countRepost'   => $this->countPluginActions('pluginRepost', 'success', $daysAgo),
            'countUsers'    => $this->countUsers($daysAgo),
            'countUsersActive'    => $this->countUsersActive($daysAgo),
            'countAccountsActive' => $this->countAccountsActive(),
            'countAccounts' => $this->countAccounts(),
            'daysAgo'       => $daysAgo,
            'usersByDay'    => array_reverse($this->getUsersByTime('day', $daysAgo, 14)),
            'accountsByDay' => array_reverse($this->getAccountsByTime('day', $daysAgo, 14)),
            'accountsActiveByDay' => array_reverse($this->getActiveAccountsByTime('day', $daysAgo, 14)), 
            'accountsUsersByDay' => array_reverse($this->getUsersAccountsByTime('day', $daysAgo, 14)),
            'colors'        => $this->randomColors(),
            'dateFormat'    => $this->getVariable('AuthUser')->get("preferences.dateformat")
        ];


        $result['totalActions'] = $result['countComments'] +
            $result['countLikes'] +
            $result['countFollows'] +
            $result['countUnfollows'] +
            $result['countWelcome'] +
            $result['countRepost'];

        $this->setVariable('info', $result);

    }

    protected function randomColors()
    {
        return [
            "rgba(255, 99, 132, 0.5)",
            "rgba(255, 159, 64, 0.5)",
            "rgba(255, 205, 86, 0.5)",
            "rgba(75, 192, 192, 0.5)",
            "rgba(54, 162, 235, 0.5)",
            "rgba(153, 102, 255, 0.5)",
            "rgba(201, 203, 207, 0.5)"
        ];
    }

    protected function getUsersAccountsByTime($time = 'day', $timeAgo = null, $limit = null)
    {
        $limit = $limit ? " LIMIT {$limit}" : "";
        if ($time == 'day') {
            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE DATE(date) >='{$time1}' AND DATE(date) <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} days")) : null;
                $where = $time ? (" WHERE DATE(date) >='{$time}'") : null;
            }
            /*$sql = "
                SELECT DATE(date) AS dt, COUNT(id) AS total
                FROM {$this->tbUsers}
                {$where}
                GROUP BY DATE(date)
                ORDER BY DATE(date) DESC
                {$limit}
            ";*/

            $sql = "
                SELECT
                  DATE(date) dt,
                  SUM(CASE WHEN `Type` = 'Accounts' THEN 1 ELSE 0 END) AS 'totalAccounts',
                  SUM(CASE WHEN `Type` = 'Users' THEN 1 ELSE 0 END) AS 'totalUsers'
                FROM
                (
                    SELECT date, 'Accounts' `Type` FROM {$this->tbAccounts}
                    UNION ALL
                    SELECT date, 'Users'        FROM {$this->tbUsers}
                ) t
                {$where}
                GROUP by dt
                ORDER by dt DESC
                {$limit}
            ";

        }
        elseif ($time = 'month')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE DATE(date) >='{$time1}' AND DATE(date) <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE DATE(date) >='{$time}'") : null;
            }

            /*$sql = "
                SELECT DATE_FORMAT(date, '%Y-%m') AS dt, COUNT(id) AS total
                FROM {$this->tbUsers}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";*/

            $sql = "
                SELECT
                  DATE_FORMAT(date, '%Y-%m') dt,
                  SUM(CASE WHEN `Type` = 'Accounts' THEN 1 ELSE 0 END) AS 'totalAccounts',
                  SUM(CASE WHEN `Type` = 'Users' THEN 1 ELSE 0 END) AS 'totalUsers'
                FROM
                (
                    SELECT date, 'Accounts' `Type` FROM {$this->tbAccounts}
                    UNION ALL
                    SELECT date, 'Users'        FROM {$this->tbUsers}
                ) t
                {$where}
                GROUP by dt
                ORDER by dt DESC
                {$limit}
            ";

        }
        else
        {
            return [];
        }


        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res;

    }


    // users by day/month
    protected function getUsersByTime($time = 'day', $timeAgo = 7, $limit = null)
    {
        $limit = $limit ? " LIMIT {$limit}" : "";
        if ($time == 'day')
        {
            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} days")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }


            $sql = "
                SELECT DATE(date) AS dt, COUNT(id) AS total
                FROM {$this->tbUsers}
                {$where}
                GROUP BY DATE(date)
                ORDER BY DATE(date) DESC
                {$limit}
            ";
        }
        elseif ($time = 'month')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }

            $sql = "
                SELECT DATE_FORMAT(date, '%Y-%m') AS dt, COUNT(id) AS total
                FROM {$this->tbUsers}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        else
        {
            return [];
        }
//        pre($sql);

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res;

    }

    // users by day/month
    protected function getAccountsByTime($time = 'day', $timeAgo = 7, $limit = null)
    {
        $limit = $limit ? " LIMIT {$limit}" : "";
        if ($time == 'day')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }


            $sql = "
                SELECT DATE(date) AS dt, COUNT(id) AS total
                FROM {$this->tbAccounts}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        elseif ($time = 'month')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }


            $sql = "
                SELECT DATE_FORMAT(date, '%Y-%m') AS dt, COUNT(id) AS total
                FROM {$this->tbAccounts}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        else
        {
            return [];
        }
        //pre($sql);
        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res;

    }

    // users by day/month
    protected function getActiveAccountsByTime($time = 'day', $timeAgo = 14, $limit = null)
    {
        $limit = $limit ? " LIMIT {$limit}" : "";
        if ($time == 'day')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}' AND login_required <= 0") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}' AND login_required <= 0") : null;
            }


            $sql = "
                SELECT DATE(date) AS dt, COUNT(id) AS total
                FROM {$this->tbAccounts}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        elseif ($time = 'month')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}' AND login_required <= 0") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}' AND login_required <= 0") : null;
            }


            $sql = "
                SELECT DATE_FORMAT(date, '%Y-%m') AS dt, COUNT(id) AS total
                FROM {$this->tbAccounts}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        else
        {
            return [];
        }
        //pre($sql);
        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res;

    }

    protected function countUsers($daysAgo = false)
    {
        $where = '';

        if ($daysAgo) {
            $time = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
            $where = " WHERE date >='{$time}'";
        }

        $sql = "SELECT COUNT(id) AS total FROM {$this->tbUsers} {$where}";

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return isset($res[0]['total']) ? $res[0]['total'] : 0;
    }

    protected function countUsersActive($daysAgo = false)
    {
        $where = '';

        if ($daysAgo) {
            $hoje = date('Y-m-d H:i:s');
            $time = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
            $where = " WHERE date >='{$time}' AND expire_date >= '{$hoje}'";
        }

        $sql = "SELECT COUNT(id) AS total FROM {$this->tbUsers} {$where}";

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res[0]['total'] ?? 0;
    }

    protected function countAccounts()
    {

        $sql = "SELECT COUNT(id) AS total FROM {$this->tbAccounts}";

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return isset($res[0]['total']) ? $res[0]['total'] : 0;
    }

    protected function countAccountsActive()
    {

        $sql = "SELECT COUNT(id) AS total FROM {$this->tbAccounts} WHERE `login_required` <= 0";

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res[0]['total'] ?? 0;
    }

    protected function countPluginActions($plugin, $status = 'success', $daysAgo = false)
    {

        if ( !$plugin || ! isset($this->$plugin) || ! $this->$plugin)
        {
            return false;
        }
     
        $plugin = $this->$plugin;

        $where = " status='{$status}'";

        if ($daysAgo) {
            $time = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
            $column = $plugin == $this->pluginPost ? 'create_date' : 'date';
            $where .= " AND {$column} >='{$time}'";
        }

        $sql = "SELECT COUNT(id) AS total FROM {$plugin} WHERE {$where}";

        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return isset($res[0]['total']) ? $res[0]['total'] : 0;

    }

    // users by day/month
    protected function getActionsByTime($plugin = '',$time = 'day', $timeAgo = 7, $limit = null)
    {


        if ( !$plugin || ! isset($this->$plugin) || ! $this->$plugin)
        {
            return [];
        }
        $plugin = $this->$plugin;



        $limit = $limit ? " LIMIT {$limit}" : "";
        if ($time == 'day')
        {
            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} days")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }


            $sql = "
                SELECT
                    DATE(date) AS dt,
                    COUNT(id) AS total,
                    COUNT(CASE WHEN status='error' THEN 1 END) AS total_error,
                    COUNT(CASE WHen status='success' THEN 1 END) AS total_success
                FROM {$plugin}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";
        }
        elseif ($time = 'month')
        {

            if (is_array($timeAgo)) {
                $time1 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[0])->format('Y-m-d') . ' 00:00:00';
                $time2 = \DateTime::createFromFormat($this->getVariable('AuthUser')->get("preferences.dateformat"), $timeAgo[1])->format('Y-m-d') . ' 23:59:59';

                $where = $time ? (" WHERE date >='{$time1}' AND date <='{$time2}'") : null;
            } else {
                $time = $timeAgo ? date('Y-m-d H:i:s', strtotime("-{$timeAgo} months")) : null;
                $where = $time ? (" WHERE date >='{$time}'") : null;
            }

            $sql = "
                SELECT
                    DATE_FORMAT(date, '%Y-%m') AS dt,
                    COUNT(id) AS total,
                    COUNT(CASE WHEN status='error' THEN 1 END) AS total_error,
                    COUNT(CASE WHen status='success' THEN 1 END) AS total_success
                FROM {$plugin}
                {$where}
                GROUP BY dt
                ORDER BY dt DESC
                {$limit}
            ";

        }
        else
        {
            return [];
        }


        $pdo = \DB::pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $res = $stmt->fetchAll();

        return $res ? $res : [];

    }


    protected function _checkPlugins()
    {

        //comments
        $tb = TABLE_PREFIX . 'auto_comment_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginComment = \DB::query($query)->get() ? $tb : null;

        //follow
        $tb = TABLE_PREFIX . 'auto_follow_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginFollow = \DB::query($query)->get() ? $tb : null;

        //unfollow
        $tb = TABLE_PREFIX . 'auto_unfollow_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginUnfollow = \DB::query($query)->get() ? $tb : null;

        //like
        $tb = TABLE_PREFIX . 'auto_like_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginLike = \DB::query($query)->get() ? $tb : null;

        //welcome
        $tb = TABLE_PREFIX . 'welcomedm_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginWelcome = \DB::query($query)->get() ? $tb : null;

        //repost
        $tb = TABLE_PREFIX . 'auto_repost_log';
        $query = "SHOW TABLES LIKE '$tb'";
        $this->pluginRepost = \DB::query($query)->get() ? $tb : null;

        //posts
        $this->pluginPost = $this->tbPosts;

    }

}