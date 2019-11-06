<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

<div class="skeleton skeleton--full">
    <div class="clearfix">
        <aside class="skeleton-aside">
            <?php if (count($Accounts) > 0): ?>

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

                <?php $active_item_id = Input::get("aid"); ?>
            <div class="js-search-results">
                <div class="aside-list js-loadmore-content" data-loadmore-id="1">
                    <?php foreach ($Accounts as $a): ?>
                        <div class="aside-list-item js-list-item <?= $active_item_id == $a->id ? "active" : "" ?>">
                            <div class="clearfix">
                                <?php $title = htmlchars($a->username);$pics = (array)$AuthUser->get("data.accpics"); ?>

                                <?php             
                                    if(isset($pics[$a->username]) && $pics[$a->username] != ""){
                                ?>

                                    <img class="circle" src="<?= $pics[$a->username] ?>">

                                <?php }else{ ?>

                                    <span class="circle">
                                        <span><?= textInitials($title, 2); ?></span>
                                    </span>

                                <?php }; ?>

                                <div class="inner">
                                    <div class="title"><?= $title ?></div>
                                    <div class="sub" style="<?php if($a->is_active){ echo "color:green;";}else{echo "color:red;";}?>">
                                        <?php if($a->is_active){ echo __("Active");}else{echo __("Inactive");}?>
                                        <?php if ($a->login_required): ?>
                                            <span class="color-danger ml-5">
                                                <span class="mdi mdi-information"></span>
                                                <?= __("Re-login required!") ?>
                                            </span>
                                        <?php endif ?>
                                    </div>
                                </div>

                                <?php
                                    $url = APPURL."/e/".$idname."/".$a->id;
                                    switch (\Input::get("ref")) {
                                        case "log":
                                            $url .= "/log";
                                            break;

                                        default:
                                            break;
                                    }
                                ?>
                                <a class="full-link js-ajaxload-content" href="<?= $url ?>"></a>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <?php else: ?>
                <div class="no-data">
                    <?php if ($AuthUser->get("settings.max_accounts") == -1 || $AuthUser->get("settings.max_accounts") > 0): ?>
                        <p><?= __("You haven't add any Instagram account yet. Click the button below to add your first account.") ?></p>
                        <a class="small button" href="<?= APPURL."/accounts/new" ?>">
                            <span class="sli sli-user-follow"></span>
                            <?= __("New Account") ?>
                        </a>
                    <?php else: ?>
                        <p><?= __("You don't have a permission to add any Instagram account.") ?></p>
                    <?php endif; ?>
                </div>
            <?php endif ?>
        </aside>

        <section class="skeleton-content hide-on-medium-and-down">
            <div class="no-data">
                <span class="no-data-icon sli sli-social-instagram"></span>
                <p><?= __("Please select an account from left side list.") ?></p>
            </div>
        </section>
    </div>
</div>
