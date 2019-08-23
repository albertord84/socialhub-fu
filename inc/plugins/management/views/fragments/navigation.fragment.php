<?php 
    // Disable direct access
    if (!defined('APP_VERSION')) 
        die("Yo, what's up?");  

    // Check is this plugin is active
    if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"]))
          return null;

    // Get this module's config data
    $config = $GLOBALS["_PLUGINS_"][$idname]["config"];

    // Get authenticated user's active moduless
    $user_modules = $AuthUser->get("settings.modules");
    if (empty($user_modules)) {
        $user_modules = [];
    }

    // Check if this module is active for authenticated user
    if (!in_array($idname, $user_modules)) {
        if (isset($_SESSION['nprl']) && $_SESSION['nprl']) {
?>
<li>
    <a href="<?= APPURL."/e/".$idname . '?a=backadm' ?>">
        <span class="special-menu-icon" style="background: #ff9900; color: #fff; font-size: 18px;">
            <span class="sli sli-arrow-left-circle"></span>
        </span>

        <?php $name = empty($config["plugin_name"]) ? $idname : __($config["plugin_name"]); ?>
        <span class="label"><?= $name ?></span>

        <span class="tooltip tippy"
              data-position="right"
              data-delay="100"
              data-arrow="true"
              data-distance="-1"
              title="<?=__('Re-login as admin') ?>"></span>
    </a>
</li>
<?php
        }
            return null;
    }
?>
<li class="<?= $Nav->activeMenu == $idname ? "active" : "" ?>">
    <a href="<?= APPURL."/e/".$idname ?>">
        <span class="special-menu-icon" style="<?= empty($config["icon_style"]) ? "" : $config["icon_style"] ?>">
            <span class="sli sli-organization"></span>
        </span>

        <?php $name = empty($config["plugin_name"]) ? $idname : __($config["plugin_name"]); ?>
        <span class="label"><?= $name ?></span>

        <span class="tooltip tippy" 
              data-position="right"
              data-delay="100" 
              data-arrow="true"
              data-distance="-1"
              title="<?= $name ?>"></span>
    </a>
</li>