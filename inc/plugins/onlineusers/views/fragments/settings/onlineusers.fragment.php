<?php
if($res['status']!=True) {

?>
<form class="js-ajax-form"
      action="<?= APPURL . "/e/" . $idname . "/"?>"
      method="POST">
    <input type="hidden" name="action" value="activate">

    <div class="section-header clearfix">
        <h2 class="section-title"><?= __("Onlineusers Licensing") ?></h2>
        <div class="section-actions clearfix hide-on-large-only">
            <a class="mdi mdi-menu-down icon js-settings-menu" href="javascript:void(0)"></a>
        </div>
    </div>
        <div class="section-content">
            <div class="clearfix">
                <div class="col s12 m6 l5">
                    <div class="form-result"></div>

                    <div class="mb-20">
                        <label class="form-label"><?= __("NextPost Marketplace Key") ?></label>
                        <input class="input"
                               name="nextpost-marketplace-key"
                               type="text"
                               maxlength="100">
                    </div>

                    <input class="fluid button" type="submit" value="<?= __("License " . $idfullname) ?>">
                </div>
            </div>
        </div>

</form>

    <?php
}else {
?>


    <div class="section-header clearfix">
        <h2 class="section-title"><?= __($idfullname." Integration") ?></h2>
        <div class="section-actions clearfix hide-on-large-only">
            <a class="mdi mdi-menu-down icon js-settings-menu" href="javascript:void(0)"></a>
        </div>
    </div>

        <div class="section-content">

            <div class="clearfix">

                <form class="js-ajax-form"
                      action="<?= APPURL . "/e/" . $idname . "/"?>"
                      method="POST">
                    <input type="hidden" name="action" value="save">
                <div class="col s12 m6 l6">
                    <div class="form-result"></div>

                    <div class="mb-20">
                        <label class="form-label"><?= __("Codesett Purchase Key") ?></label>
                        <input class="input"
                               name="nextpost-marketplace-key"
                               type="text"
                               disabled
                               value="<?= htmlchars($Settings->get("data.nextpost.marketplace.licensekey")) ?>"
                               maxlength="100">
                    </div>

                <!--

                    <input class="fluid button" type="submit" value="<?/*= __("Save") */?>">-->
                </div>
                </form>
                <form class="js-ajax-form"
                      action="<?= APPURL . "/e/" . $idname . "/"?>"
                      method="POST">
                <div class="col s12 m6 m-last l6 l-last">
                    <br/>
                    <div class="form-result"></div>
                        <input type="hidden" name="action" value="deactivate">
                        <input class="fluid button" type="submit" value="<?= __("Deactivate") ?>">
                    </form>
                </div>
            </div>
        </div>



    <?php
}
?>