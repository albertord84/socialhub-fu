<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

<div class="skeleton skeleton--full " >
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
                    <a class="fluid button button--light-outline js-loadmore-btn js-autoloadmore-btn" data-loadmore-id="1" href="<?= APPURL."/e/".$idname."?aid=".$Account->get("id")."&ref=log" ?>">
                        <span class="icon sli sli-refresh"></span>
                        <?= __("Load More") ?>
                    </a>
                </div>
            </div>
        </aside>

        <section class="skeleton-content">
            <div class="section-header clearfix">
                <h2 class="section-title">
                    <?= htmlchars($Account->get("username")) ?>
                    <?php if ($Account->get("login_required")): ?>
                        <small class="color-danger ml-15 fz-">
                            <span class="mdi mdi-information"></span>
                            <?= __("Re-login required!") ?>
                        </small>
                    <?php endif ?>
                </h2>
            </div>

            <div class="af-tab-heads clearfix">
                <a href="<?= APPURL."/e/".$idname."/".$Account->get("id") ?>"><?= __("Target & Settings") ?></a>
                <a href="<?= APPURL."/e/".$idname."/".$Account->get("id")."/log" ?>" class="active"><?= __("Activity Log") ?></a>
                <?php if ($Settings->get("data.video_url") && $Settings->get("data.video_url") != ""): ?>  
                    <a href="<?= APPURL."/e/".$idname."/".$Account->get("id")."/help" ?>"><?= __("Help") ?></a>
                <?php endif ?>
            </div>

            <?php if ($ActivityLog->getTotalCount() > 0): ?>
                <div class="af-log-list js-loadmore-content" data-loadmore-id="2" data-next-id="32">
                    <?php if ($ActivityLog->getPage() == 1 && $Schedule->get("is_active")): ?>
                        <?php
                            $nextdate = new \Moment\Moment($Schedule->get("schedule_date"), date_default_timezone_get());
                            $nextdate->setTimezone($AuthUser->get("preferences.timezone"));

                            $diff = $nextdate->fromNow();
                        ?>
                        <?php if ($diff->getDirection() == "future"): ?>
                            <div class="af-next-schedule">
                                <?= __("Next request will be sent %s approximately", $diff->getRelative()) ?>
                            </div>
                        <?php elseif (abs($diff->getSeconds()) < 60*10): ?>
                            <div class="af-next-schedule">
                                <?= __("Next request will be sent in a few moments") ?>
                            </div>
                        <?php else: ?>
                            <div class="af-log-list-item error">
                                <div class="clearfix">
                                    <span class="circle">
                                        <span class="text">E</span>
                                    </span>

                                    <div class="inner clearfix">
                                        <div class="action">
                                            <div class="error-msg">
                                                <?= __("Something is wrong on the system") ?>
                                            </div>
                                            <div class="error-details"><?= __("System task error") ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
                    <?php endif ?>

                    <?php foreach ($Logs as $l): ?>
                        <div class="af-log-list-item <?= $l->get("status") ?>">
                            <div class="clearfix">
                                
                                    <?php if ($l->get("status") == "success"): ?>
                                        <span class="circle">
                                        <?php $img = $l->get("data.followed.profile_pic_url"); ?>
                                        <span class="img" style="<?= $img ? "background-image: url('".htmlchars($img)."');" : "" ?>"></span>
                                         </span>
                                    <?php else: ?>
                                        <!-- <span class="text">E</span> -->
                                    <?php endif ?>
                               

                                <div class="inner clearfix">
                                    <?php
                                        $date = new \Moment\Moment($l->get("date"), date_default_timezone_get());
                                        $date->setTimezone($AuthUser->get("preferences.timezone"));

                                        $fulldate = $date->format($AuthUser->get("preferences.dateformat")) . " "
                                                  . $date->format($AuthUser->get("preferences.timeformat") == "12" ? "h:iA" : "H:i");
                                    ?>
                                    <?php if ($l->get("data.powerlike") == true && $l->get("data.powerlike.count") != "0"): ?>
										<div class="codingmatters_powerlike">

										  <?php foreach ($l->get("data.powerlike.posts") as $post):?>
					
											<img class="img" src="<?= $post->media_thumb ?>">
											
										  <?php endforeach; ?>
										  
										  <div class="codingmatters_heart">
											<div class="icon mdi mdi-heart"></div>
											<div class="codingmatters_heart_count"><?= $l->get("data.powerlike.count") ?> <?= (intval($l->get("data.powerlike.count")) > 1) ? "likes" : "like" ?></div>
										  </div>
										</div>
                                   <?php endif ?>
                                    <div class="action">
                                        <?php if ($l->get("status") == "success"): ?>
                                            <?= __("Followed %s", "<a href='https://www.instagram.com/".htmlchars($l->get("data.followed.username"))."' target='_blank'>".htmlchars($l->get("data.followed.username"))."</a>") ?>

                                            <span class="date" title="<?= $fulldate ?>"><?= $date->fromNow()->getRelative() ?></span>
                                        <?php else: ?>
                                            <?php if ($l->get("data.error.msg") && !$l->get("data.error.filter_msg")): ?>
                                                <div class="error-msg">
                                                    <!-- <?= __($l->get("data.error.msg")) ?> -->
                                                    <span class="mdi mdi-check" style="background: #20a200;    padding: 0.25rem 0.4rem;    color: #fff;    font-size: 1.3rem;    border-radius: 1.6rem;"></span>
                                                    Requisição feita com sucesso
                                                    <span class="date" title="<?= $fulldate ?>"><?= $date->fromNow()->getRelative() ?></span>
                                                </div>
                                            <?php endif ?>
                                            <?php if ($l->get("data.error.filter_msg")): ?>
                                                <div class="error-msg">
                                                    <?= __($l->get("data.error.filter_msg")) ?>
                                                    <span class="date" title="<?= $fulldate ?>"><?= $date->fromNow()->getRelative() ?></span>
                                                </div>
                                            <?php endif ?>
                                            <?php if ($l->get("data.error.details")): ?>
                                                <div class="error-details"><!-- <?= __($l->get("data.error.details")) ?> --></div>
                                            <?php endif ?>
                                        <?php endif ?>
                                    </div>

                                    <?php if ($l->get("data.trigger")): ?>
                                        <?php $trigger = $l->get("data.trigger"); ?>
                                        <?php if ($trigger->type == "hashtag"): ?>
                                            <a class="meta" href="<?= "https://www.instagram.com/explore/tags/".htmlchars($trigger->value) ?>" target="_blank">
                                                <span class="icon mdi mdi-pound"></span>
                                                <?= htmlchars($trigger->value) ?>
                                            </a>
                                        <?php elseif ($trigger->type == "location"): ?>
                                            <a class="meta" href="<?= "https://www.instagram.com/explore/locations/".htmlchars($trigger->id) ?>" target="_blank">
                                                <span class="icon mdi mdi-map-marker"></span>
                                                <?= htmlchars($trigger->value) ?>
                                            </a>
                                        <?php elseif ($trigger->type == "people"): ?>
                                            <a class="meta" href="<?= "https://www.instagram.com/".htmlchars($trigger->value) ?>" target="_blank">
                                                <span class="icon mdi mdi-instagram"></span>
                                                <?= htmlchars($trigger->value) ?>
                                            </a>
                                        <?php endif ?>
                                    <?php endif ?>

                                            <?php if ($l->get("status") != "success" && $l->get("data.error.filter_detail") ): ?>
                                            <div style="font-size: 12px;color: #9b9b9b;display:inline;margin-left:5px;">
                                                <span class="icon mdi mdi-information" style="color:red;"></span>
                                                <span>Failed filters: </span>
                                                <span><?= $l->get("data.error.filter_detail") ?></span>
                                            </div>
                                            <?php endif ?>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="af-amount-of-action">
                    <?= __("Total %s actions", $ActivityLog->getTotalCount()) ?>
                </div>

                <?php if($ActivityLog->getPage() < $ActivityLog->getPageCount()): ?>
                    <div class="loadmore mt-20 mb-20">
                        <?php
                            $url = parse_url($_SERVER["REQUEST_URI"]);
                            $path = $url["path"];
                            if(isset($url["query"])){
                                $qs = parse_str($url["query"], $qsarray);
                                unset($qsarray["page"]);

                                $url = $path."?".(count($qsarray) > 0 ? http_build_query($qsarray)."&" : "")."page=";
                            }else{
                                $url = $path."?page=";
                            }
                        ?>
                        <a class="fluid button button--light-outline js-loadmore-btn" data-loadmore-id="2" data-next-id="32" href="<?= $url.($ActivityLog->getPage()+1) ?>">
                            <span class="icon sli sli-refresh"></span>
                            <?= __("Load More") ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-data">
                    <p><?= __("Auto Follow activity log for %s is empty",
                    "<a href='https://www.instagram.com/".htmlchars($Account->get("username"))."' target='_blank'>".htmlchars($Account->get("username"))."</a>") ?></p>
                </div>
            <?php endif ?>
        </section>
    </div>
</div>
