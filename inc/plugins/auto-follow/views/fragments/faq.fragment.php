<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

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
                <a href="<?= APPURL."/e/".$idname."/".$Account->get("id")."/log" ?>"><?= __("Activity Log") ?></a>
                <a href="<?= APPURL."/e/".$idname."/".$Account->get("id")."/faq" ?>" class="active"><?= __("Help") ?></a>
            </div>

            <div class="section-content">
            
                <div class="section-header clearfix hide-on-small-only">
                    <h2 class="section-title"><?= __("Help Video") ?></h2>
                </div>

                <div class="section-content pb-0">
                    <div class="container" style="position: relative;width: 100%;height: 0;padding-bottom: 56.25%;">
                        <iframe src="<?= $Settings->get("data.video_url") ?>" frameborder="0" allowfullscreen style="position: absolute;top: 0;left: 0;width: 100%;height: 100%;"></iframe>
                    </div>
                </div>

            </div>

        </section>
    </div>
</div>
