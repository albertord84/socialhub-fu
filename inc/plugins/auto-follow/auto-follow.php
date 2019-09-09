<?php
namespace Plugins\AutoFollow;

const IDNAME = "auto-follow";

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Event: plugin.install
 */
function install($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "auto_follow_schedule` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `user_id` INT NOT NULL ,
                `account_id` INT NOT NULL ,
                `target` TEXT NOT NULL ,
                `speed` VARCHAR(20) NOT NULL ,
                `daily_pause` BOOLEAN NOT NULL,
                `daily_pause_from` TIME NOT NULL,
                `daily_pause_to` TIME NOT NULL,
                `is_active` BOOLEAN NOT NULL ,
                `schedule_date` DATETIME NOT NULL ,
                `end_date` DATETIME NOT NULL ,
                `last_action_date` DATETIME NOT NULL ,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`user_id`),
                INDEX (`account_id`)
            ) ENGINE = InnoDB;";

    $sql .= "CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "auto_follow_log` (
                `id` INT NOT NULL AUTO_INCREMENT ,
                `user_id` INT NOT NULL ,
                `account_id` INT NOT NULL ,
                `status` VARCHAR(20) NOT NULL,
                `followed_user_pk` VARCHAR(50) NOT NULL,
                `data` TEXT NOT NULL ,
                `date` DATETIME NOT NULL ,
                PRIMARY KEY (`id`),
                INDEX (`user_id`),
                INDEX (`account_id`),
                INDEX (`followed_user_pk`)
            ) ENGINE = InnoDB;";

    $sql .= "ALTER TABLE `" . TABLE_PREFIX . "auto_follow_schedule`
                ADD CONSTRAINT `" . uniqid("ibfk_") . "` FOREIGN KEY (`user_id`)
                REFERENCES `" . TABLE_PREFIX . "users`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $sql .= "ALTER TABLE `" . TABLE_PREFIX . "auto_follow_schedule`
                ADD CONSTRAINT `" . uniqid("ibfk_") . "` FOREIGN KEY (`account_id`)
                REFERENCES `" . TABLE_PREFIX . "accounts`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $sql .= "ALTER TABLE `" . TABLE_PREFIX . "auto_follow_log`
                ADD CONSTRAINT `" . uniqid("ibfk_") . "` FOREIGN KEY (`user_id`)
                REFERENCES `" . TABLE_PREFIX . "users`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $sql .= "ALTER TABLE `" . TABLE_PREFIX . "auto_follow_log`
                ADD CONSTRAINT `" . uniqid("ibfk_") . "` FOREIGN KEY (`account_id`)
                REFERENCES `" . TABLE_PREFIX . "accounts`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE;";

    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');

/**
 * Event: plugin.remove
 */
function uninstall($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    // Remove plugin settings
    $Settings = \Controller::model("GeneralData", "plugin-auto-follow-settings");
    $Settings->remove();

    // Remove plugin tables
    $sql = "DROP TABLE `" . TABLE_PREFIX . "auto_follow_schedule`;";
    $sql .= "DROP TABLE `" . TABLE_PREFIX . "auto_follow_log`;";

    $pdo = \DB::pdo();
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}
\Event::bind("plugin.remove", __NAMESPACE__ . '\uninstall');

/**
 * Add module as a package options
 * Only users with correct permission
 * Will be able to use module
 *
 * @param array $package_modules An array of currently active
 *                               modules of the package
 */
function add_module_option($package_modules)
{
    $config = include __DIR__ . "/config.php";
    ?>
        <div class="mt-15">
            <label>
                <input type="checkbox"
                       class="checkbox"
                       name="modules[]"
                       value="<?=IDNAME?>"
                       <?=in_array(IDNAME, $package_modules) ? "checked" : ""?>>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?=__('Auto Follow')?>
                </span>
            </label>
        </div>
    <?php
}
\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');

/**
 * Map routes
 */
function route_maps($global_variable_name)
{
    // Settings (admin only)
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/settings/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/SettingsController.php",
        __NAMESPACE__ . "\SettingsController",
    ]);

    // Index
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/IndexController.php",
        __NAMESPACE__ . "\IndexController",
    ]);

    // Index
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/sort/[:action]?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/IndexController.php",
        __NAMESPACE__ . "\IndexController",
    ]);

    // Schedule
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/[i:id]/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/ScheduleController.php",
        __NAMESPACE__ . "\ScheduleController",
    ]);

    // Log
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/[i:id]/log/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/LogController.php",
        __NAMESPACE__ . "\LogController",
    ]);

    // Video
    $GLOBALS[$global_variable_name]->map("GET|POST", "/e/" . IDNAME . "/[i:id]/help/?", [
        PLUGINS_PATH . "/" . IDNAME . "/controllers/FaqController.php",
        __NAMESPACE__ . "\FaqController",
    ]);

}
\Event::bind("router.map", __NAMESPACE__ . '\route_maps');

/**
 * Event: navigation.add_special_menu
 */
function navigation($Nav, $AuthUser)
{
    $idname = IDNAME;
    include __DIR__ . "/views/fragments/navigation.fragment.php";
}
\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation');

/**
 * Add cron task to follow new users
 */
function addCronTask()
{
    require_once __DIR__ . "/models/SchedulesModel.php";
    require_once __DIR__ . "/models/LogModel.php";

    require_once __DIR__ . "/../../../app/controllers/AccountsController.php";

    // Get auto follow schedules
    $Schedules = new SchedulesModel;
    $Schedules->where("is_active", "=", 1)
        ->where("schedule_date", "<=", date("Y-m-d H:i:s"))
        ->where("end_date", ">=", date("Y-m-d H:i:s"))
        ->orderBy("last_action_date", "ASC")
        ->setPageSize(10) // required to prevent server overload
        ->setPage(1)
        ->fetchData();

    if ($Schedules->getTotalCount() < 1) {
        return false;
    }

    $settings = namespace\settings();
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

    $as = [__DIR__ . "/models/ScheduleModel.php", __NAMESPACE__ . "\ScheduleModel"];

    foreach ($Schedules->getDataAs($as) as $sc) {
        $Log = new LogModel;
        $Account = \Controller::model("Account", $sc->get("account_id"));
        $User = \Controller::model("User", $sc->get("user_id"));

        if (isset($speeds[$sc->get("speed")]) && (int) $speeds[$sc->get("speed")] > 0) {
            $speed = (int) $speeds[$sc->get("speed")];
            $delta = round(3600 / $speed);

            if ($settings->get("data.random_delay")) {
                $delay = rand(0, 700);
                $delta += $delay;
            }
        } else {
            $delta = rand(720, 7200);
        }

        // TODO Alberto
        $next_schedule = date("Y-m-d H:i:s", time() + $delta);

        // make no delay
        // $next_schedule = date("Y-m-d H:i:s", time());

        if ($sc->get("daily_pause")) {
            $pause_from = date("Y-m-d") . " " . $sc->get("daily_pause_from");
            $pause_to = date("Y-m-d") . " " . $sc->get("daily_pause_to");
            if ($pause_to <= $pause_from) {

                $pause_to = date("Y-m-d", time() + 86400) . " " . $sc->get("daily_pause_to");
            }

            if ($next_schedule > $pause_to) {
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

        $Log->set("user_id", $User->get("id"))
            ->set("account_id", $Account->get("id"))
            ->set("status", "error");

        if (!$Account->isAvailable() || $Account->get("login_required")) {

            $sc->set("is_active", 0)->save();

            $Log->set("data.error.msg", "Activity has been stopped")
                ->set("data.error.details", "Re-login is required for the account.")
                ->save();
            continue;
        }

        if (!$User->isAvailable() || !$User->get("is_active") || $User->isExpired()) {

            $sc->set("is_active", 0)->save();

            $Log->set("data.error.msg", "Activity has been stopped")
                ->set("data.error.details", "User account is either disabled or expred.")
                ->save();
            continue;
        }

        if ($User->get("id") != $Account->get("user_id")) {
            $sc->set("is_active", 0)->save();
            continue;
        }

        $targets = @json_decode($sc->get("target"));
        if (!$targets) {
            $sc->set("is_active", 0)->save();
            continue;
        }

        $i = rand(0, count($targets) - 1);
        $target = $targets[$i];

        if (empty($target->type) ||
            empty($target->id) ||
            !in_array($target->type, ["hashtag", "location", "people"])) {
            continue;
        }

        try {
            $Instagram = \InstagramController::login($Account);
        } catch (\Exception $e) {

            $Account->refresh();

            if ($Account->get("login_required")) {

                $error_count = $sc->get("data.error_count");

                if ($error_count == null) {
                    $error_count = 0;
                }

                $error_count++;
                $sc->set("data.error_count", $error_count);
                if ($error_count >= 3) {
                    $sc->set("is_active", 0)->save();
                    $sc->set("data.error_count", 0);
                }
                $Log->set("data.error.msg", "Activity Relogin/Connection Problems " . $error_count . "/3");
            } else {
                $Log->set("data.error.msg", "Action re-scheduled");
            }

            $Log->set("data.error.details", $e->getMessage())
                ->save();

            continue;
        }

        $Log->set("data.trigger", $target);

        $follow_pk = null;
        $follow_username = null;
        $follow_full_name = null;
        $follow_profile_pic_url = null;

        $turns = 1;
        $turns_max = 10;

        $rank_token = \InstagramAPI\Signatures::generateUUID();

        if ($target->type == "hashtag") {
            try {
                $feed = $Instagram->hashtag->getFeed(
                    str_replace("#", "", $target->id),
                    $rank_token);
            } catch (\Exception $e) {

                $Log->set("data.error.msg", "Couldn't get the feed")
                    ->set("data.error.details", $e->getMessage())
                    ->save();
                continue;
            }

            if (count($feed->getItems()) < 1) {
                continue;
            }

            $turns = 1;
            $turns_max = 10;
            $runden = 1;

            $feedItems = $feed->getItems();
            shuffle($feedItems);

            foreach ($feedItems as $item) {
                if ($item->getUser() != null && $item->getUser()->getFriendshipStatus() != null && empty($item->getUser()->getFriendshipStatus()->getFollowing()) &&
                    empty($item->getUser()->getFriendshipStatus()->getOutgoingRequest()) &&
                    $item->getUser()->getPk() != $Account->get("instagram_id")) {

                    $runden++;

                    if ($turns > $turns_max) {
                        break 2;
                    }

                    $_log = new LogModel([
                        "user_id" => $User->get("id"),
                        "account_id" => $Account->get("id"),
                        "followed_user_pk" => $item->getUser()->getPk(),
                        "status" => "success",
                    ]);

                    if (!$_log->isAvailable()) {

                        $filter = namespace\customFilterFast($item->getUser(), $sc, $User, $Account, $Log);

                        if (!$filter) {
                            continue;
                        }

                        $filter2 = namespace\customFilter($item->getUser(), $sc, $User, $Account, $Log);

                        if (!$filter2 && $turns <= $turns_max) {
                            $turns++;
                            continue;
                        }

                        $follow_pk = $item->getUser()->getPk();
                        $follow_username = $item->getUser()->getUsername();
                        $follow_full_name = $item->getUser()->getFullName();
                        $follow_profile_pic_url = $item->getUser()->getProfilePicUrl();
                        $follow_is_private = (bool) $item->getUser()->getIsPrivate();
                        break;
                    } else {
                        // already followed
                    }
                }
            }
        } else if ($target->type == "location") {
            try {
                $feed = $Instagram->location->getFeed($target->id, $rank_token);
                // TODO Alberto
                $feedItems = array();
                foreach ($feed->getSections() as $items) {
                    foreach ($items->getLayoutContent()->getMedias() as $item) {
                        array_push($feedItems, $item->getMedia());
                        $mediaCounter += 1;
                    }
                }
            } catch (\Exception $e) {

                $Log->set("data.error.msg", "Couldn't get the feed")
                    ->set("data.error.details", $e->getMessage())
                    ->save();
                continue;
            }

            if (count($feedItems) < 1) {
                continue;
            }

            $turns = 1;
            $turns_max = 10;

            shuffle($feedItems);

            foreach ($feedItems as $item) {
                if ($item->getUser() != null && $item->getUser()->getFriendshipStatus() != null && empty($item->getUser()->getFriendshipStatus()->getFollowing()) &&
                    empty($item->getUser()->getFriendshipStatus()->getOutgoingRequest()) &&
                    $item->getUser()->getPk() != $Account->get("instagram_id")) {

                    if ($turns > $turns_max) {
                        break 2;
                    }

                    $_log = new LogModel([
                        "user_id" => $User->get("id"),
                        "account_id" => $Account->get("id"),
                        "followed_user_pk" => $item->getUser()->getPk(),
                        "status" => "success",
                    ]);

                    if (!$_log->isAvailable()) {

                        $filter = namespace\customFilterFast($item->getUser(), $sc, $User, $Account, $Log);

                        if (!$filter) {
                            continue;
                        }

                        $filter2 = namespace\customFilter($item->getUser(), $sc, $User, $Account, $Log);

                        if (!$filter2 && $turns <= $turns_max) {
                            $turns++;
                            continue;
                        }

                        $follow_pk = $item->getUser()->getPk();
                        $follow_username = $item->getUser()->getUsername();
                        $follow_full_name = $item->getUser()->getFullName();
                        $follow_profile_pic_url = $item->getUser()->getProfilePicUrl();
                        $follow_is_private = (bool) $item->getUser()->getIsPrivate();
                        break;
                    }
                }
            }
        } else if ($target->type == "people") {
            $round = 1;
            $loop = true;
            $next_max_id = null;

            while ($loop) {
                try {
                    $feed = $Instagram->people->getFollowers($target->id, $rank_token, null, $next_max_id);
                } catch (\Exception $e) {

                    $loop = false;

                    if ($round == 1) {

                        $Log->set("data.error.msg", "Couldn't get the feed")
                            ->set("data.error.details", $e->getMessage())
                            ->save();
                    }

                    continue 2;
                }

                if (count($feed->getUsers()) < 1) {

                    $loop = false;
                    continue 2;
                }

                $feedUsers = $feed->getUsers();
                shuffle($feedUsers);

                $turns = 1;
                $turns_max = 10;

                foreach ($feedUsers as $userItem) {
                    if (empty($userItem->getFriendshipStatus()) &&
                        $userItem->getPk() != $Account->get("instagram_id")) {
                        if ($turns > $turns_max) {
                            break 2;
                        }

                        $_log = new LogModel([
                            "user_id" => $User->get("id"),
                            "account_id" => $Account->get("id"),
                            "followed_user_pk" => $userItem->getPk(),
                            "status" => "success",
                        ]);

                        if (!$_log->isAvailable()) {

                            $filter = namespace\customFilterFast($userItem, $sc, $User, $Account, $Log, $round);

                            if (!$filter) {
                                continue;
                            }

                            $filter2 = namespace\customFilter($userItem, $sc, $User, $Account, $Log);

                            if (!$filter2 && $turns <= $turns_max) {
                                $turns++;
                                continue;
                            }

                            $follow_pk = $userItem->getPk();
                            $follow_username = $userItem->getUsername();
                            $follow_full_name = $userItem->getFullName();
                            $follow_profile_pic_url = $userItem->getProfilePicUrl();
                            $follow_is_private = (bool) $userItem->getIsPrivate();

                            break 2;
                        }
                    }
                }

                $round++;
                $next_max_id = empty($feed->getNextMaxId()) ? null : $feed->getNextMaxId();
                if ($round >= 5 || !empty($follow_pk) || $next_max_id) {
                    $loop = false;
                }
            }
        }

        if (empty($follow_pk)) {
            $Log->set("data.error.msg", "Couldn't find new user to follow, add more Targets.")
                ->save();
            continue;
        }

        try {

            $power_count = $sc->get("data.powerlike_count");
            $power_like = $sc->get("data.powerlike");
            $power_random = $sc->get("data.powerlike_random");
            $likedmedia = [];

            if ($power_count > 3) {
                $power_count = 3; //max value
            }
            ;

            if ($power_like && !$follow_is_private) {

                try {
                    $feed = $Instagram->timeline->getUserFeed($follow_pk);

                    $items = $feed->getItems();

                    if ($power_random) {
                        $power_count = mt_rand(1, intval($power_count));
                    }

                    $temp_count = $power_count;

                    foreach ($items as $item) {

                        $media = new \stdClass();

                        if (!empty($item->getId()) && !$item->getHasLiked()) {

                            if ($temp_count == 0) {
                                break;
                            } else {
                                $temp_count = $temp_count - 1;
                            }

                            $media->media_id = $item->getId();
                            $media->media_code = $item->getCode();
                            $media->media_type = $item->getMediaType();
                            $media->media_thumb = namespace\_get_media_thumb_igitem($item);
                            $media->user_pk = $item->getUser()->getPk();
                            $media->user_username = $item->getUser()->getUsername();
                            $media->user_full_name = $item->getUser()->getFullName();

                            try {
                                $resp = $Instagram->media->like($item->getId());
                            } catch (\Exception $e) {

                                if ($e->getResponse() !== null && $e->getResponse()->getMessage() == "feedback_required") {
                                    $sc->set("data.powerlike", 0)->save();
                                }

                                continue;
                            }

                            if (!$resp->isOk()) {
                                continue;
                            } else {
                                array_push($likedmedia, $media);
                            }
                        }

                    }

                } catch (\Exception $e) {
                    // no feed
                }

            } else {
                $power_count = 0;
            }

            $resp = $Instagram->people->follow($follow_pk);

        } catch (\InstagramAPI\Exception\FeedbackRequiredException $e) {
            // Remove previous session folder to make guarantee full relogin
            $session_dir = SESSIONS_PATH . "/" . $Account->get("user_id") . "/" . $Account->get("username");
            $cookiesFile = $session_dir . '/' . $Account->get("username") . '-cookies.dat';
            if (file_exists($cookiesFile)) {
                $resp = file_put_contents($cookiesFile, "");
                // @delete($session_dir);
            }
            // Alberto: Try reconntect
            global $GLOBALS;
            $GLOBALS['_POST']['id'] = $Account->get("id");
            $AccountsController = new \AccountsController();
            $AccountsController->setVariable("AuthUser", $User);
            $reconect = $AccountsController->reconnect(true);
            if ($reconect->resp->result == 1) {
                // $Log->set("status", "Reconnect success!!! [Alberto]")->save();
                $Log->set("data.error.msg", "FeedbackRequiredException")
                    ->set("data.error.details", "FeedbackRequiredException [Reconnect success!!!]")
                    ->save();
                return;
            }
            else {
                // @delete($session_dir);
            }
            // end reconnect
            $Log->set("data.error.msg", "FeedbackRequiredException")
                ->set("data.error.details", "FeedbackRequiredException")
                ->save();
            $next_schedule = date("Y-m-d H:i:s", time() + 24 * 60 * 60);
            $sc->set("schedule_date", $next_schedule)
                ->set("last_action_date", date("Y-m-d H:i:s"))
                ->save();
            // continue;
        } catch (\Exception $e) {
            $Log->set("data.error.msg", "Couldn't follow the user")
                ->set("data.error.details", $e->getMessage())
                ->save();
            $next_schedule = date("Y-m-d H:i:s", time() + 24 * 60 * 60);
            $sc->set("schedule_date", $next_schedule)
                ->set("last_action_date", date("Y-m-d H:i:s"))
                ->save();
            // continue;
        }

        if (!$resp->isOk()) {
            $Log->set("data.error.msg", "Couldn't follow the user")
                ->set("data.error.details", "Something went wrong")
                ->save();

            // Alberto: Try reconntect
            $reconect = (new \AccountsController())->reconnect($Account);
            if ($reconect->resp->result == 1) {
                $Log->set("status", "Reconnect success!!! [Alberto]")->save();
            }
            return;

            // continue;
        }

        $Log->set("status", "success")
            ->set("data.followed", [
                "pk" => $follow_pk,
                "username" => $follow_username,
                "full_name" => $follow_full_name,
                "profile_pic_url" => $follow_profile_pic_url,
            ])
            ->set("data.powerlike", [
                "count" => count($likedmedia),
                "posts" => $likedmedia,
            ])
            ->set("followed_user_pk", $follow_pk)
            ->save();
    }
}
\Event::bind("cron.add", __NAMESPACE__ . "\addCronTask");

function settings()
{
    $settings = \Controller::model("GeneralData", "plugin-auto-follow-settings");
    return $settings;
}

function _get_media_thumb_igitem($item)
{
    $media_thumb = null;

    $media_type = empty($item->getMediaType()) ? null : $item->getMediaType();

    if ($media_type == 1 || $media_type == 2) {

        $candidates = $item->getImageVersions2()->getCandidates();
        if (!empty($candidates[0]->getUrl())) {
            $media_thumb = $candidates[0]->getUrl();
        }
    } else if ($media_type == 8) {

        $carousel = $item->getCarouselMedia();
        $candidates = $carousel[0]->getImageVersions2()->getCandidates();
        if (!empty($candidates[0]->getUrl())) {
            $media_thumb = $candidates[0]->getUrl();
        }
    }

    return $media_thumb;
}

function customFilterFast($usr, $sc, $User, $Account, $log, $round = 0)
{

    $debug = false;
    $debug_msg = "";
    $Settings = namespace\settings();

    $filter_dont_follow_twice = (bool) $sc->get("data.filter_unfollowed");
    $filter_gender = $sc->get("data.filter_gender");
    $filter_profil_private = (bool) $sc->get("data.filter_privat");
    $filter_profil_picture = (bool) $sc->get("data.filter_picture");
    $filter_blacklist = explode(",", $sc->get("data.filter_blacklist"));

    if ($filter_dont_follow_twice) {

        //$filter_count++;
        try {
            $q4 = "SELECT * FROM " . TABLE_PREFIX . "auto_unfollow_log WHERE user_id = " . intval($User->get("id")) . " AND account_id = " . intval($Account->get("id")) . " AND unfollowed_user_pk = " . intval($usr->getPk()) . " AND status = 'success'";
            $query = \DB::query($q4);
            $logs = $query->get();

            if (count($logs) > 0) {
                return false;
            }
        } catch (\Exception $e) {

        }
    }

    if ($filter_gender == "male" || $filter_gender == "female") {
        //$filter_count++;
        $gender_check = namespace\Gender($filter_gender, $usr->getFullName());
        if (!$gender_check) {
            $log->set("data.error.filter_detail", " gender ")
                ->save();
            return false;
        }
    }

    if ($filter_profil_private) {
        //$filter_count++;
        if ($usr->getIsPrivate()) {
            $log->set("data.error.filter_detail", " private ")
                ->save();
            return false;
        }
    }

    if ($filter_profil_picture) {
        //$filter_count++;
        if ($usr->getProfilePicUrl() == "") {
            $log->set("data.error.filter_detail", " pic ")
                ->save();
            return false;
        }
    }

    if (count($filter_blacklist) > 0) {
        $tempString = $usr->getUsername() . " ";
        $tempString .= $usr->getFullName() . " ";

        if (!empty($tempString)) {
            foreach ($filter_blacklist as $key => $value) {

                if (!empty($value)) {
                    $pos = strpos(strtolower($tempString), strtolower($value));

                    if ($pos === false) {
                        // failed
                    } else {
                        $log->set("data.error.filter_detail", " blacklist")->save();
                        return false;
                    }
                }
            }
        }
    }

    return true;
}

function customFilter($usr, $sc, $User, $Account, $log, $round = 0)
{

    $debug = false;
    $debug_msg = "";
    $filter_msg = "";
    $debug_values = "";
    $Settings = namespace\settings();

    $filter_media_min = (int) $sc->get("data.filter_media_min");
    $filter_media_days = (int) $sc->get("data.filter_media_days");
    $filter_follower_min = (int) $sc->get("data.filter_followed_min");
    $filter_follower_max = (int) $sc->get("data.filter_followed_max");
    $filter_following_min = (int) $sc->get("data.filter_following_min");
    $filter_following_max = (int) $sc->get("data.filter_following_max");
    $filter_accuracy = (int) $sc->get("data.filter_accuracy");
    $filter_blacklist = explode(",", $sc->get("data.filter_blacklist"));

    $filter_count = 0;
    $filter_fail = 0;

    if ($filter_accuracy === null || $filter_accuracy == 0) {
        $filter_accuracy = 50;
    }

    if ($Settings->get("data.advanced_filters")) {

        $userinfo = namespace\getWebUserInfo($usr->getUsername(), $Account->get('proxy'));

        if ($userinfo->error) {
            $userinfo = namespace\getWebUserInfo($usr->getUsername(), null);
            if ($userinfo->error) {
                $log->set("data.error.filter_msg", "Proxy Error")
                    ->save();
                return false;
            }
        }

        if ($filter_media_days > 0) {
            $filter_count++;
            if ($userinfo->last_post_days != false && intval($userinfo->last_post_days) >= intval($filter_media_days)) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # D " . $userinfo->last_post_days . " >= " . $filter_media_days . "#";
                $filter_msg .= " media_days ";
            }
        }

        if (count($filter_blacklist) > 0) {

            //$filter_count++;
            $tempString = $userinfo->username . " ";
            $tempString .= $userinfo->full_name . " ";
            $tempString .= $userinfo->biography . " ";

            if (!empty($tempString)) {
                foreach ($filter_blacklist as $key => $value) {

                    if (!empty($value)) {
                        $pos = strpos(strtolower($tempString), strtolower($value));

                        if ($pos === false) {
                            //$debug_msg .= $value . ", ";
                        } else {
                            return false;
                            $filter_fail++;
                            $debug_msg .= " # BLACK " . $value . " #";
                        }
                    }

                }
            }

        }

        // Media
        if ($filter_media_min > 0) {
            $filter_count++;
            if ($userinfo->media <= $filter_media_min) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # M " . $userinfo->media . "#";
                $filter_msg .= " media_min ";
            }
        }

        // Followers
        if ($filter_follower_max > 0) {
            $filter_count++;
            if ($userinfo->followed >= $filter_follower_max) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # F1 " . $userinfo->followed . "#";
                $filter_msg .= " follower_max ";
            }
        }

        if ($filter_follower_min > 0) {
            $filter_count++;
            if ($userinfo->followed <= $filter_follower_min) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # F2 " . $userinfo->followed . "#";
                $filter_msg .= " follower_min ";
            }
        }

        //Followings
        if ($filter_following_max > 0) {
            $filter_count++;
            if ($userinfo->follow >= $filter_following_max) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # FW1 " . $userinfo->follow . "#";
                $filter_msg .= " following_max ";
            }
        }

        if ($filter_following_min > 0) {
            $filter_count++;
            if ($userinfo->follow <= $filter_following_min) {
                //return false;
                $filter_fail++;
                $debug_msg .= " # FW2 " . $userinfo->follow . "#";
                $filter_msg .= " following_min ";
            }
        }

        if ($filter_count > 0) {
            $filter_result = 100 - ($filter_fail * 100 / $filter_count);
        } else {
            $filter_result = 100;
        }

        if ($debug) {
            echo "# " . $filter_count . "/" . $filter_fail . " | " . $filter_result . "/" . $filter_accuracy . " ";
            echo "" . $debug_msg . " ";
        }

        if ($filter_result >= $filter_accuracy) {
            if ($debug) {
                echo "# ACCEPTED <br>";
            }
            return true;
        } else {

            $highestMatch = $log->get("data.error.filter_result");

            if ($highestMatch < $filter_result) {
                $log->set("data.error.filter_result", round($filter_result, 1))
                    ->set("data.error.filter_msg", "No Match found this round, the best match got " . round($filter_result, 1) . "% accuracy with " . $filter_fail . " of " . $filter_count . " filters failed.<br> Adjust your filters or lower the accuracy below " . round($filter_result, 1) . "%")
                    ->set("data.error.filter_detail", $filter_msg)
                    ->save();
            }

            if ($debug) {
                echo "# DECLINED <br>";
            }

            return false;
        }
    }

    return true;
}

function Gender($gender, $fullname)
{

    $Settings = namespace\settings();

    if ($fullname == "") {
        return false;
    }

    $firstname = strtolower(explode(" ", $fullname)[0]);
    $firstname = preg_replace('~[^a-zA-Z]+~', '', $firstname);

    if (strlen($firstname) <= 2) {
        return false;
    }

    if ($gender == "female") {
        $names = file_get_contents(PLUGINS_PATH . "/" . IDNAME . "/assets/female.json");

        $array_names = json_decode($names, true);

        if (in_array($firstname, array_map('strtolower', $array_names['female']))) {
            return true;
        }

    } else {
        $names = file_get_contents(PLUGINS_PATH . "/" . IDNAME . "/assets/male.json");

        $array_names = json_decode($names, true);

        if (in_array($firstname, array_map('strtolower', $array_names['male']))) {
            return true;
        }

    }

    if ($Settings->get("data.gender_log")) {

        $file = PLUGINS_PATH . "/" . IDNAME . "/assets/" . $gender . '_fails.txt';
        $myfile = file_put_contents($file, $firstname . PHP_EOL, FILE_APPEND | LOCK_EX);

    }

    return false;
}

function getWebUserInfo($username, $proxy)
{

    $url = "https://www.instagram.com/" . $username . "/";
    $debug = false;

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:52.0) Gecko/20100101 Firefox/52.0');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    if ($proxy !== null) {

        $foo_str = str_replace("https://", "", $proxy);
        $foo_str = str_replace("http://", "", $foo_str);
        $proxyData = explode('@', $foo_str);

        if (count($proxyData) > 1) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyData[0]);

            $ipport = explode(':', $proxyData[1]);
            curl_setopt($ch, CURLOPT_PROXY, $ipport[0]);
            curl_setopt($ch, CURLOPT_PROXYPORT, $ipport[1]);
        } else {
            $ipport = explode(':', $foo_str);

            if (count($ipport) > 1) {
                curl_setopt($ch, CURLOPT_PROXY, $ipport[0]);
                curl_setopt($ch, CURLOPT_PROXYPORT, $ipport[1]);
            }
        }
    }

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $resp = curl_exec($ch);

    $error = false;

    if (curl_error($ch)) {

        if ($debug) {
            echo "!!" . curl_error($ch) . "!!";
        }

        $error = curl_error($ch);

    }

    curl_close($ch);

    $regex = '@<script type="text/javascript">window._sharedData = (.*?);</script>@si';
    preg_match($regex, $resp, $d);

    $info = new \stdClass();

    if (isset($d[1])) {

        $instaData = json_decode($d[1]);

        if (!isset($instaData->entry_data->ProfilePage[0])) {
            print_r(json_encode($instaData));
            die();
        }

        $userinfo = $instaData->entry_data->ProfilePage[0]->graphql->user;

        $info->biography = $userinfo->biography;
        $info->followed = $userinfo->edge_followed_by->count;
        $info->follow = $userinfo->edge_follow->count;
        $info->media = $userinfo->edge_owner_to_timeline_media->count;
        $info->full_name = $userinfo->full_name;
        $info->username = $userinfo->username;

        if (isset($userinfo->edge_owner_to_timeline_media->edges[0])) {
            $start = date_create("@" . $userinfo->edge_owner_to_timeline_media->edges[0]->node->taken_at_timestamp);
            $end = date_create();
            $diff = date_diff($start, $end);
            $info->last_post_days = $diff->days;
        } else {
            $info->last_post_days = false;
        }
    }

    $info->error = $error;

    return $info;

}
