<?php if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}
?>

<div class="skeleton skeleton--full">
    <div class="clearfix">
        <aside class="skeleton-aside hide-on-medium-and-down">

                    <form action="<?= APPURL."/e/".$idname ?>" class="search-box" method="GET" autocomplete="off">
                    <div class="pos-r">
                        <input type="text" class="input input--small leftpad rightpad" name="q" placeholder="Search..." value="">
                        <span class="small field-icon--right processing">
                            <img src="<?= APPURL ?>/assets/img/round-loading.svg" alt="Searching...">
                        </span>
                        <span class="sli sli-magnifier small field-icon--left search-icon"></span>
                        <a href="javascript:void(0)" class="mdi mdi-arrow-left small field-icon--left cancel-icon"></a>
                    </div>

                    <div class="pos-r mt-5" style="text-align:right;">
                    <span>
                    <?= __("Sort By") ?> 

                    <?php if ($Order == "date"): ?>

                    <a href="<?=  APPURL."/e/".$idname . "/sort/" . "byName"  ?>"><?= __("Name") ?> </a>
                    <?= __("or") ?>
                    <a href="<?=  APPURL."/e/".$idname . "/sort/" . "byStatus"  ?>"><?= __("Status") ?></a>

                    <?php endif; ?>

                    <?php if ($Order == "status"): ?>

                    <a href="<?=  APPURL."/e/".$idname . "/sort/" . "byName"  ?>"><?= __("Name") ?> </a>
                    <?= __("or") ?>
                    <a href="<?=  APPURL."/e/".$idname  ?>"><?= __("Date") ?></a>

                    <?php endif; ?>

                    <?php if ($Order == "name"): ?>

                    <a href="<?=  APPURL."/e/".$idname . "/sort/" . "byStatus"  ?>"><?= __("Status") ?> </a>
                     <?= __("or") ?>
                    <a href="<?=  APPURL."/e/".$idname  ?>"><?= __("Date") ?></a>

                    <?php endif; ?>
                    </span>
                    </div>
                </form>
            <div class="js-search-results">
            <div class="aside-list js-loadmore-content" data-loadmore-id="1"></div>

            <div class="loadmore pt-20 mb-20 none">
                <a class="fluid button button--light-outline js-loadmore-btn js-autoloadmore-btn" data-loadmore-id="1" href="<?=APPURL . "/e/" . $idname . "?aid=" . $Account->get("id") . "&ref=schedule"?>">
                    <span class="icon sli sli-refresh"></span>
                    <?=__("Load More")?>
                </a>
            </div>

            </div>
        </aside>

        <section class="skeleton-content">
            <form class="js-auto-follow-schedule-form"
                  action="<?=APPURL . "/e/" . $idname . "/" . $Account->get("id")?>"
                  method="POST">

                <input type="hidden" name="action" value="save">

                <div class="section-header clearfix">
                    <h2 class="section-title">
                        <?=htmlchars($Account->get("username"))?>
                        <?php if ($Account->get("login_required")): ?>
                            <small class="color-danger ml-15">
                                <span class="mdi mdi-information"></span>
                                <?=__("Re-login required!")?>
                            </small>
                        <?php endif?>
                    </h2>
                </div>

                <div class="af-tab-heads clearfix">
                    <a href="<?=APPURL . "/e/" . $idname . "/" . $Account->get("id")?>" class="active"><?=__("Target & Settings")?></a>
                    <a href="<?=APPURL . "/e/" . $idname . "/" . $Account->get("id") . "/log"?>"><?=__("Activity Log")?></a>
                    <?php if ($Settings->get("data.video_url") && $Settings->get("data.video_url") != ""): ?>          
                        <a href="<?= APPURL."/e/".$idname."/".$Account->get("id")."/help" ?>"><?= __("Help") ?></a>
                    <?php endif ?>
                </div>

                <div class="section-content">
                    <div class="form-result mb-25" style="display:none;"></div>

                    <div class="clearfix">
                        <div class="col s12 m12 l8">
                            <div class="mb-5 clearfix">
                                <label class="inline-block mr-50 mb-15">
                                    <input class="radio" name='type' type="radio" value="hashtag" checked>
                                    <span>
                                        <span class="icon"></span>
                                        #<?=__("Hashtags")?>
                                    </span>
                                </label>

                                <label class="inline-block mr-50 mb-15">
                                    <input class="radio" name='type' type="radio" value="location">
                                    <span>
                                        <span class="icon"></span>
                                        <?=__("Places")?>
                                    </span>
                                </label>

                                <label class="inline-block mb-15">
                                    <input class="radio" name='type' type="radio" value="people">
                                    <span>
                                        <span class="icon"></span>
                                        <?=__("People")?>
                                    </span>
                                </label>
                            </div>

                            <div class="clearfix mb-20 pos-r">
                                <label class="form-label"><?=__('Search')?></label>

                                    <a class="small button button--light-outline pull-right mb-5 remove-tags" href="javascript:void(0)"><?=__("Reset")?></a>
                                    <a class="small button button--light-outline pull-right mb-5 mr-5 import-tags" href="javascript:void(0)"><?=__("Import")?></a> 
                                    
                                    <select class="input input--small pull-right mr-5 mb-5" id="myTargets" style="display: inline-block;width: auto;padding: 4px 8px;height:30px;font-size: 12px;">
                                        <option value="null" selected>
                                                Select Targets
                                        </option>

                                        <?php foreach ($Targets as $a): ?>
                                            <option value='<?= $a->targets ?>'>
                                                <?= htmlchars($a->module . " " . "(". $a->count . ")"); ?>
                                            </option>
                                        <?php endforeach ?>
                                    </select> 

                                    <input class="input rightpad" name="search" type="text" value=""
                                        data-url="<?=APPURL . "/e/" . $idname . "/" . $Account->get("id")?>"
                                        <?=$Account->get("login_required") ? "disabled" : ""?>>

                                    <span class="field-icon--right pe-none none js-search-loading-icon">
                                        <img src="<?=APPURL . "/assets/img/round-loading.svg"?>" alt="Loading icon">
                                    </span>
                            </div>

                            <div class="tags clearfix mt-20 mb-20">

                                <?php
                                    $targets = $Schedule->isAvailable()
                                    ? json_decode($Schedule->get("target"))
                                    : [];
                                    $icons = [
                                        "hashtag" => "mdi mdi-pound",
                                        "location" => "mdi mdi-map-marker",
                                        "people" => "mdi mdi-instagram",
                                    ];
                                ?>

                                <?php foreach ($targets as $t): ?>

                                    <span class="tag pull-left"
                                          data-type="<?=htmlchars($t->type)?>"
                                          data-id="<?=htmlchars($t->id)?>"
                                          data-value="<?=htmlchars($t->value)?>"
                                          style="margin: 0px 2px 3px 0px;">

                                          <?php if (isset($icons[$t->type])): ?>
                                              <span class="<?=$icons[$t->type]?>"></span>
                                          <?php endif?>

                                          <?=htmlchars($t->value)?>
                                          <span class="mdi mdi-close remove"></span>
                                    </span>

                                <?php endforeach?>

                            </div>

                            <div class="clearfix mb-20">
                                <div class="col s6 m6 l6">
                                    <label class="form-label"><?=__("Speed")?></label>

                                    <select class="input" name="speed">

                                        <?php if ($AuthUser->get("package_id") == 0 && $Settings->get("data.trial_limitspeed")) { ?>   
                                            <option value="auto" <?=$Schedule->get("speed") == "auto" ? "selected" : ""?>><?=__("Auto") . " (" . __("Recommended") . ")"?></option>
                                            <option value="very_slow" <?=$Schedule->get("speed") == "very_slow" ? "selected" : ""?>><?=__("Very Slow")?></option>
                                        <?php }else{ ?>                                                        
                                            <option value="auto" <?=$Schedule->get("speed") == "auto" ? "selected" : ""?>><?=__("Auto") . " (" . __("Recommended") . ")"?></option>
                                            <option value="very_slow" <?=$Schedule->get("speed") == "very_slow" ? "selected" : ""?>><?=__("Very Slow")?></option>
                                            <option value="slow" <?=$Schedule->get("speed") == "slow" ? "selected" : ""?>><?=__("Slow")?></option>
                                            <option value="medium" <?=$Schedule->get("speed") == "medium" ? "selected" : ""?>><?=__("Medium")?></option>
                                            <option value="fast" <?=$Schedule->get("speed") == "fast" ? "selected" : ""?>><?=__("Fast")?></option>
                                            <option value="very_fast" <?=$Schedule->get("speed") == "very_fast" ? "selected" : ""?>><?=__("Very Fast")?></option>
                                        <?php }; ?>  
                                    </select>
                                </div>

                                <div class="col s6 s-last m6 m-last l6 l-last">
                                    <label class="form-label"><?=__("Status")?></label>

                                    <select class="input" name="is_active">
                                        <option value="0" <?=$Schedule->get("is_active") == 0 ? "selected" : ""?>><?=__("Deactive")?></option>
                                        <option value="1" <?=$Schedule->get("is_active") == 1 ? "selected" : ""?>><?=__("Active")?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="clearfix mb-20">
                                <div class="col s6 m6 l6">
                                    <label class="form-label"><?=__("Gender")?></label>

                                    <select class="input" name="filter_gender">
                                      <option value="both" <?=$Schedule->get("data.filter_gender") == "both" ? "selected" : ""?>><?=__("Both")?></option>
                                      <option value="male" <?=$Schedule->get("data.filter_gender") == "male" ? "selected" : ""?>><?=__("Male")?></option>
                                      <option value="female" <?=$Schedule->get("data.filter_gender") == "female" ? "selected" : ""?>><?=__("Female")?></option>
                                    </select>
                                </div>

                                <div class="col s6 s-last m6 m-last l6 l-last">
                                    <label class="form-label"><?=__("No Private Profiles")?></label>
                                    <select class="input" name="filter_privat">
                                      <option value="0" <?=$Schedule->get("data.filter_privat") == false ? "selected" : ""?>><?=__("Deactive")?></option>
                                      <option value="1" <?=$Schedule->get("data.filter_privat") == true ? "selected" : ""?>><?=__("Active")?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="clearfix mb-20">
                                <div class="col s6 m6 l6">
                                    <label class="form-label"><?=__("Has Profile Picture")?></label>

                                    <select class="input" name="filter_picture">
                                      <option value="0" <?=$Schedule->get("data.filter_picture") == false ? "selected" : ""?>><?=__("Deactive")?></option>
                                      <option value="1" <?=$Schedule->get("data.filter_picture") == true ? "selected" : ""?>><?=__("Active")?></option>
                                    </select>
                                </div>

                                <div class="col s6 s-last m6 m-last l6 l-last">
                                    <label class="form-label"><?=__("Ignore Unfollowed Profiles")?></label>

                                    <select class="input" name="filter_unfollowed">
                                      <option value="0" <?=$Schedule->get("data.filter_unfollowed") == false ? "selected" : ""?>><?=__("Deactive")?></option>
                                      <option value="1" <?=$Schedule->get("data.filter_unfollowed") == true ? "selected" : ""?>><?=__("Active")?></option>
                                    </select>
                                </div>

                            </div>

                            <?php if ($Settings->get("data.advanced_filters") && $AuthUser->get("package_id") != 0 ||
                                      $Settings->get("data.advanced_filters") && !$Settings->get("data.trial_advanced")) { ?>

                            <div class="clearfix mb-5">
                                <div class="col s12 m12 l12">
                                    <label class="form-label"><?=__("Blacklist")?></label>
                                    <textarea name="filter_blacklist" placeholder="" style="width:100%;" rows="5"><?=$Schedule->get("data.filter_blacklist")?></textarea>
                                    <ul class="field-tips mb-20">
                                        <li><?=__("Add comma seperated keywords to not interact with.")?></li>
                                        <li><?=__("Blank fields will be ignored.")?></li>
                                    </ul>
                                </div>
                            </div>

                            <?php }; ?>

                            <?php if ($Settings->get("data.powerlike") && $AuthUser->get("package_id") != 0 ||
                                      $Settings->get("data.powerlike") && !$Settings->get("data.trial_powerlike")) { ?>

                            <div style="border:solid 1px #9b9b9b;padding: 15px 15px 0px 15px;margin-bottom: 15px;">
                                <div class="clearfix mb-5">
                                    <div class="col s6 m6 l6">
                                        <label class="form-label"><?=__("Like + Follow")?></label>

                                        <select class="input" name="powerlike">
                                        <option value="0" <?=$Schedule->get("data.powerlike") == false ? "selected" : ""?>><?=__("Deactive")?></option>
                                        <option value="1" <?=$Schedule->get("data.powerlike") == true ? "selected" : ""?>><?=__("Active")?></option>
                                        </select>
                                    </div>

                                    <div class="col s6 s-last m6 m-last l6 l-last">
                                        <label class="form-label"><?=__("Like Count")?></label>

                                        <select class="input" name="powerlike_count">
                                            <option value="1" <?=$Schedule->get("data.powerlike_count") == 1 ? "selected" : ""?>>1</option>
                                            <option value="2" <?=$Schedule->get("data.powerlike_count") == 2 ? "selected" : ""?>>2</option>
                                            <option value="3" <?=$Schedule->get("data.powerlike_count") == 3 ? "selected" : ""?>>3</option>
                                        </select>
                                    </div>

                                    <div class="col s12 m12 l12 mt-15">

                                        <label>
                                            <input type="checkbox"
                                                class="checkbox"
                                                name="powerlike_random"
                                                value="1"
                                                <?=$Schedule->get("data.powerlike_random") ? "checked" : ""?>>
                                            <span>
                                                <span class="icon unchecked">
                                                    <span class="mdi mdi-check"></span>
                                                </span>
                                                <?=__('Randomize Like count')?>
                                            </span>
                                        </label>

                                    <ul class="field-tips" style="color:#9b9b9b;">
                                        <li><?=__("Like + Follow Feature likes Posts of the Target befor following.")?></li>
                                        <li><?=__("Keep in mind that this feature increases the requests made to instagram.")?></li>
                                        <li><?=__("This feature automatically disables when Instagram complains because of Spam.")?></li>
                                    </ul>
                                    </div>
                                </div>
                            </div>

                            <?php }; ?>
                            <?php if ($Settings->get("data.advanced_filters") && $AuthUser->get("package_id") != 0 ||
                                      $Settings->get("data.advanced_filters") && !$Settings->get("data.trial_advanced")) { ?>
                           <div style="border:solid 1px red;padding: 15px 15px 0px 15px;margin-bottom: 15px;">

<div class="clearfix mb-20">
    <div class="col s6 m6 l6">
        <label class="form-label"><?=__("Media Min Amount")?></label>
        <input class="input rightpad" name="filter_media_min" type="number" value="<?=$Schedule->get("data.filter_media_min")?>">
    </div>

    <div class="col s6 s-last m6 m-last l6 l-last">
        <label class="form-label"><?=__("Has posted withing last XX days.")?></label>
        <input class="input rightpad" name="filter_media_days" type="number" value="<?=$Schedule->get("data.filter_media_days")?>">
    </div>
</div>

<div class="clearfix mb-20">
    <div class="col s6 m6 l6">
        <label class="form-label"><?=__("Followers Min Amount")?></label>
        <input class="input rightpad" name="filter_followed_min" type="number" value="<?=$Schedule->get("data.filter_followed_min")?>">
    </div>

    <div class="col s6 s-last m6 m-last l6 l-last">
        <label class="form-label"><?=__("Followers Max Amount")?></label>
        <input class="input rightpad" name="filter_followed_max" type="number" value="<?=$Schedule->get("data.filter_followed_max")?>">
    </div>
</div>

<div class="clearfix mb-5">
    <div class="col s6 m6 l6">
        <label class="form-label"><?=__("Following Min Amount")?></label>
        <input class="input rightpad" name="filter_following_min" type="number" value="<?=$Schedule->get("data.filter_following_min")?>">
    </div>

    <div class="col s6 s-last m6 m-last l6 l-last">
        <label class="form-label"><?=__("Following Max Amount")?></label>
        <input class="input rightpad" name="filter_following_max" type="number" value="<?=$Schedule->get("data.filter_following_max")?>">
    </div>

    <div class="col s12 m12 l12 mt-15">

        <label class="form-label"><?=__("Accuracy")?></label>
        <select class="input" name="filter_accuracy">
        <option value="25" <?=$Schedule->get("data.filter_accuracy") == 25 ? "selected" : ""?>><?=__("25%")?></option>
        <option value="50" <?=$Schedule->get("data.filter_accuracy") == 50 ? "selected" : ""?>><?=__("50%")?></option>
        <option value="75" <?=$Schedule->get("data.filter_accuracy") == 75 ? "selected" : ""?>><?=__("75%")?></option>
        <option value="100" <?=$Schedule->get("data.filter_accuracy") == 100 ? "selected" : ""?>><?=__("100%")?></option>
        </select>

       <ul class="field-tips" style="color:red;">
            <li><?=__("As more filters you set, as harder it will be to find matches.")?></li>
            <li><?=__("Lower the accuracy if your desired filter settings fail to often, default is 50%. Means if you set 4 of the above filters 2 are allowed to fail.")?></li>
            <li><?=__("Set filter value to 0 to disable it.")?></li>
       </ul>
    </div>
</div>
</div>

                            <?php }
;?>

                            <div class="mb-40 mt-40">
                                <div class="mb-20" style="display:inline;">
                                    <label>
                                        <input type="checkbox"
                                               class="checkbox"
                                               name="daily-pause"
                                               value="1"
                                               <?=$Schedule->get("daily_pause") ? "checked" : ""?>>
                                        <span>
                                            <span class="icon unchecked">
                                                <span class="mdi mdi-check"></span>
                                            </span>
                                            <?=__('Pause actions everyday')?>
                                        </span>
                                    </label>
                                </div>

                                <div class="clearfix js-daily-pause-range">
                                    <?php $timeformat = $AuthUser->get("preferences.timeformat") == "12" ? 12 : 24;?>

                                    <div class="col s6 m3 l3">
                                        <label class="form-label"><?=__("From")?></label>

                                        <?php
                                        $from = new \DateTime(date("Y-m-d") . " " . $Schedule->get("daily_pause_from"));
                                        $from->setTimezone(new \DateTimeZone($AuthUser->get("preferences.timezone")));
                                        $from = $from->format("H:i");
                                        ?>

                                        <select class="input" name="daily-pause-from">
                                            <?php for ($i = 0; $i <= 23; $i++): ?>
                                                <?php $time = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";?>
                                                <option value="<?=$time?>" <?=$from == $time ? "selected" : ""?>>
                                                    <?=$timeformat == 24 ? $time : date("h:iA", strtotime(date("Y-m-d") . " " . $time))?>
                                                </option>

                                                <?php $time = str_pad($i, 2, "0", STR_PAD_LEFT) . ":30";?>
                                                <option value="<?=$time?>" <?=$from == $time ? "selected" : ""?>>
                                                    <?=$timeformat == 24 ? $time : date("h:iA", strtotime(date("Y-m-d") . " " . $time))?>
                                                </option>
                                            <?php endfor;?>
                                        </select>
                                    </div>

                                    <div class="col s6 s-last m3 m-last l3 l-last">
                                        <label class="form-label"><?=__("To")?></label>

                                        <?php
                                        $to = new \DateTime(date("Y-m-d") . " " . $Schedule->get("daily_pause_to"));
                                        $to->setTimezone(new \DateTimeZone($AuthUser->get("preferences.timezone")));
                                        $to = $to->format("H:i");
                                        ?>

                                        <select class="input" name="daily-pause-to">
                                            <?php for ($i = 0; $i <= 23; $i++): ?>
                                                <?php $time = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00";?>
                                                <option value="<?=$time?>" <?=$to == $time ? "selected" : ""?>>
                                                    <?=$timeformat == 24 ? $time : date("h:iA", strtotime(date("Y-m-d") . " " . $time))?>
                                                </option>

                                                <?php $time = str_pad($i, 2, "0", STR_PAD_LEFT) . ":30";?>
                                                <option value="<?=$time?>" <?=$to == $time ? "selected" : ""?>>
                                                    <?=$timeformat == 24 ? $time : date("h:iA", strtotime(date("Y-m-d") . " " . $time))?>
                                                </option>
                                            <?php endfor;?>
                                        </select>
                                    </div>
                                </div>
                            </div>



                            <div class="clearfix">
                                <div class="col s12 m6 l6">
                                    <input class="fluid button" type="submit" value="<?=__("Save")?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>
