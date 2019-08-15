<?php
if (!defined('APP_VERSION'))
    die("Yo, what's up?");

if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"]))
    return null;

$config = $GLOBALS["_PLUGINS_"][$idname]["config"];
$user_modules = $AuthUser->get("settings.modules");
if (empty($user_modules)) {
    $user_modules = [];
}
?>
<?php if ($AuthUser->isAdmin()): ?>
    <li class="<?= $Nav->activeMenu == $idname ? "active" : "" ?>">
        <a href="<?= APPURL . "/e/" . $idname ?>">
            <span class="special-menu-icon" style="<?= empty($config["icon_style"]) ? "" : $config["icon_style"] ?>">
                <?php
                $name = empty($config["plugin_name"]) ? $idname : $config["plugin_name"];
                echo textInitials($name, 2)
                ?>
            </span>

            <span class="label"><?= __('Online Users') ?></span>

            <span class="tooltip tippy"
                  data-position="right"
                  data-delay="100"
                  data-arrow="true"
                  data-distance="-1"
                  title="<?= __('Online Users') ?>"></span>
        </a>
    </li>
<?php endif ?>


<script
        src="https://code.jquery.com/jquery-3.3.1.min.js"
        integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>

    <script>

        $(document).ready(function () {
            <?php
            if ($AuthUser)
            {
            ?>
            function update_user_activity() {
                var action = 'updateuseractivity';
                $.ajax({
                    url: "<?= APPURL . "/e/".$idname ?>",
                    method: "POST",
                    data: {action: action},
                    success: function (data) {
                     //   console.log("Successfully updated user status");
                    },
                    error: function (data) {
                     //   console.log("Could not update user status");
                    }
                });
            }

            setInterval(function () {
                update_user_activity();
            }, 5000);


            <?php
            }
            //else
            // {
            ?>
            fetch_user_login_data();
            setInterval(function () {
                fetch_user_login_data();
            }, 3000);

            function fetch_user_login_data() {
                var action = 'fetchuserstatus';
                $.ajax({
                    url: "<?= APPURL . "/e/".$idname ?>",
                    method: "POST",
                    type: 'POST',
                    dataType: 'jsonp',
                    data: {action: action},
                    success: function (data) {
                       // console.log("Successfully fetched user status " + data.msg);
                    }
                });
            }
            <?php
            // }
            ?>

        });
    </script>
