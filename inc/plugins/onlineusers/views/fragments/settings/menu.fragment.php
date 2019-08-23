<div>

    <div class="aside-list js-loadmore-content" data-loadmore-id="1">
        <?php
        $config = $GLOBALS["_PLUGINS_"][$idname]["config"];
        foreach ($Plugins->getDataAs("Plugin") as $a): ?>
            <div class="aside-list-item js-list-item active">
                <div class="clearfix">
                    <?php $title = empty($config["plugin_name"]) ? $idname : $config["plugin_name"]; ?>
                    <span class="circle">
                                    <span><?= textInitials($title, 2); ?></span>
                                </span>

                    <div class="inner">
                        <div class="title"><?= $title ?></div>
                        <div class="sub">
                            <?= __("Nextpost Module") ?>

                            <?php if ($a->get("login_required")): ?>
                                <span class="color-danger ml-5">
                                                <span class="mdi mdi-information"></span>
                                    <?= __("Re-login required!") ?>
                                            </span>
                            <?php endif ?>
                        </div>
                    </div>

                    <?php
                    //  $url = APPURL."/e/".$idname."/". "/settings/";
                    $url = APPURL . "/e/" . $idname . "/settings";
                    switch (\Input::get("ref")) {
                        case "log":
                            $url .= "/log";
                            break;

                        case "messages":
                            $url .= "/messages";
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