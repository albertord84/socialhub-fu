<?php
if($res['status']!=True and empty($licensekey)) {

?>
<form class="js-ajax-form"
      action="<?= APPURL . "/e/" . $idname . "/"?>"
      method="POST">
    <input type="hidden" name="action" value="activate">

    <div class="section-header clearfix">
        <h2 class="section-title"><?= __("Chatrr Licensing") ?></h2>
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
        <h2 class="section-title"><?= __("Chatrr Integration") ?></h2>

        <div class="section-actions clearfix hide-on-large-only">
            <a class="mdi mdi-menu-down icon js-settings-menu" href="javascript:void(0)"></a>

        </div>


        <div class="section-content">

            <div class="clearfix pt-30 mb-20">
                <div class="col s12 m6 l6">
                    <button id="checkUpdateBtn" class="fluid button button--oval button--dark" style="text-align: center; font-size: 18px">
                        <i class="mdi mdi-link mdi-18px float-right"></i>  Check for updates</button>
                </div>
                <div class="col s12 m6 m-last l6 l-last">
                    <button id="doUpdateBtn" class="fluid button button--oval button--dark" style="text-align: center; font-size: 18px">
                        <i class="mdi mdi-update mdi-18px float-right"></i> Update Module</button>
                </div>
              <progress id="prog" value="0" max="100.0" style="width: 100%;"></progress><br><br>
            </div>
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
                               value="<?= htmlchars($Settings->get("data.chatrr.nextpost-marketplace-key")) ?>"
                               maxlength="100">
                    </div>

                    <div class="mb-20">
                        <label class="form-label"><?= __("Live Chat handler") ?></label>
                        <select name="chatrr-chathandler" class="input">
                            <option <?= $Settings->get("data.chatrr.chathandler") == "" ? "selected" : "" ?>>Choose a chat handler</option>
                            <option value="tidio" <?= $Settings->get("data.chatrr.chathandler") == "tidio" ? "selected" : "" ?>><?= __("Tidio.com") ?></option>
                            <option value="fbmessenger" <?= $Settings->get("data.chatrr.chathandler") == "fbmessenger" ? "selected" : "" ?>><?= __("Fb Messenger") ?></option>
                        </select>
                    </div>

                    <div class="mb-20">
                        <label class="form-label">
                            <?= __("Chatrr FB Page id") ?>
                            <span class="compulsory-field-indicator">*</span>
                        </label>

                        <input class="input"
                               name="chatrr-fbpageid"
                               type="text"
                               value="<?= htmlchars($Settings->get("data.chatrr.fbpageid")) ?>"
                               maxlength="100">
                        <small><?=__("Ensure that this domain has been whitelisted on your Page's Messenger Platform Settings")?></small>
                    </div>



                    <div class="mb-20">
                        <label class="form-label">
                            <?= __("FB Chat Bubble colour") ?>
                            <span class="compulsory-field-indicator">*</span>
                        </label>
                        <input class="input"
                               id="fbcolorpickerdisplay"
                               name="chatrr-fbbubblecolour"
                               type="text"
                               placeholder="#FF0000"
                               value="<?= htmlchars($Settings->get("data.chatrr.fbbubblecolour")) ?>"
                               maxlength="100">

                        <input class="input"
                               id="fbcolorpicker"
                               type="color"
                               placeholder="#FF0000"
                               value="<?= htmlchars($Settings->get("data.chatrr.fbbubblecolour")) ?>"
                               maxlength="100">
                    </div>


                    <div class="mb-40">
                        <label class="form-label">
                            <?= __("Tidio Public Key") ?>
                            <span class="compulsory-field-indicator">*</span>
                        </label>

                        <input class="input"
                               name="chatrr-tidioid"
                               type="text"
                               value="<?= htmlchars($Settings->get("data.chatrr.tidioid")) ?>"
                               maxlength="100">
                    </div>


                    <div class="mb-20">
                        <label class="form-label">
                            <?= __("Tidio Chat Bubble colour") ?>
                            <span class="compulsory-field-indicator">*</span>
                        </label>
                        <input class="input"
                               id="tidiocolorpickerdisplay"
                               name="chatrr-tidiobubblecolour"
                               type="text"
                               placeholder="#FF0000"
                               value="<?= htmlchars($Settings->get("data.chatrr.tidiobubblecolour")) ?>"
                               maxlength="100">

                        <input class="input"
                               id="tidiocolorpicker"
                               type="color"
                               placeholder="#FF0000"
                               value="<?= htmlchars($Settings->get("data.chatrr.tidiobubblecolour")) ?>"
                               maxlength="100">
                    </div>



                    <input class="fluid button" type="submit" value="<?= __("Save") ?>">
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

<script>
    var theFbColourInput = document.getElementById("fbcolorpicker");
    var theFbColourInputDisplay = document.getElementById("fbcolorpickerdisplay");
    theFbColourInput.addEventListener("input", function() {
        theFbColourInputDisplay.value = theFbColourInput.value;
    }, false);
    theFbColourInputDisplay.addEventListener("input", function() {
        theFbColourInput.value = theFbColourInputDisplay.value;
    }, false);

    var theTidioColourInput = document.getElementById("tidiocolorpicker");
    var theTidioColourInputDisplay = document.getElementById("tidiocolorpickerdisplay");
    theTidioColourInput.addEventListener("input", function() {
        theTidioColourInputDisplay.value = theTidioColourInput.value;
    }, false);

    theTidioColourInputDisplay.addEventListener("input", function() {
        theTidioColourInput.value = theTidioColourInputDisplay.value;
    }, false);
</script>


<script>

    var checkUpdateBtn = document.getElementById("checkUpdateBtn");
    var doUpdateBtn = document.getElementById("doUpdateBtn");

    checkUpdateBtn.addEventListener("click", function () {
        _callCheckUpdate();
    });
    doUpdateBtn.addEventListener("click", function () {
        _callChackAndDoUpdate();
    });






    var data = {};
    var _callCheckUpdate = function (token) {
        data.action = "checkupdate";
        $("body").addClass("onprogress");
        $.ajax({
            url: "<?= APPURL . "/e/".$idname."" ?>",
            type: 'POST',
            dataType: 'jsonp',
            data: data,
            timeout: 10000,
            success: function (resp, parsedjson) {
                if (resp.result == 1) {
                    //window.location.href = resp.url;
                    NextPost.Alert({
                        title: "Update Available",
                        content: resp.msg +"\t\n"+ "changelog: "+resp.changelog
                    });

                    $("body").removeClass("onprogress");
                } else {

                    NextPost.Alert({
                        title: "Error checking for update",
                        content: resp.msg
                    });

                    $("body").removeClass("onprogress");
                }
            },
            error: function (parsedjson, textStatus, errorThrown) {
                NextPost.Alert({
                    content: parsedjson,
                    title: textStatus
                });

            }
        });

    }

    var _callChackAndDoUpdate = function (token) {
        data.action = "checkupdate";
       // $("body").addClass("onprogress");
        $.ajax({
            url: "<?= APPURL . "/e/".$idname."" ?>",
            type: 'POST',
            dataType: 'jsonp',
            data: data,
            timeout: 10000,
            success: function (resp, parsedjson) {
                if (resp.result == 1) {
                    _callDoUpdate(resp.updateid, resp.hassql, resp.version)
                } else {

                    NextPost.Alert({
                        title: "Error checking for new version",
                        content: resp.msg
                    });

                 //   $("body").removeClass("onprogress");
                }
            },
            error: function (parsedjson, textStatus, errorThrown) {
                NextPost.Alert({
                    content: parsedjson,
                    title: textStatus
                });
              //  $("body").removeClass("onprogress");
            }
        });

    }
    var _callDoUpdate = function (updateid, hassql, version) {
        data.action = "doupdate";
        data.updateid = updateid;
        data.hassql = hassql;
        data.version = version;
       // $("body").addClass("onprogress");
        $.ajax({
            url: "<?= APPURL . "/e/".$idname."" ?>",
            type: 'POST',
            dataType: 'jsonp',
            data: data,
            timeout: 10000,
            success: function (resp, parsedjson) {
                if (resp.result == 1) {

                    NextPost.Alert({
                        title: "Update Done",
                        content: resp.msg +"\t\n"+ "changelog: "+resp.changelog
                    });

                   // $("body").removeClass("onprogress");
                } else {

                    NextPost.Alert({
                        title: "Update not successful",
                        content: resp.msg
                    });

                 //   $("body").removeClass("onprogress");
                }
            },
            error: function (parsedjson, textStatus, errorThrown) {
                NextPost.Alert({
                    content: parsedjson,
                    title: "Final Update Stage: "+textStatus + " "
                });
              //  $("body").removeClass("onprogress");
            }
        });

    }
</script>
