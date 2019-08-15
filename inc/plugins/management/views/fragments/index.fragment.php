<?php 
    // Disable direct access
    if (!defined('APP_VERSION')) die("Yo, what's up?"); 
?>
<div class="skeleton skeleton--full" id="audit">
    <div class="clearfix">
        <aside class="skeleton-aside">
            <div class="aside-list ">
                <div class="aside-list-item <?= $action == 'dashboard' ? "active" : "" ?>">
                    <div class="clearfix">
                        <span class="circle" style="background-image: url(<?= PLUGINS_URL."/management/assets/img/dash.png"?>); background-repeat: no-repeat; background-size: cover;"></span>
                        <div class="inner">
                            <div class="title"><?=__('Dashboard'); ?></div>
                            <div class="sub"><?=__('Essential Information'); ?></div>
                        </div>
                        <a class="full-link" href="<?= $baseUrl."?a=dashboard" ?>"></a>
                    </div>
                </div>
                <div class="aside-list-item <?= $action == 'users' ? "active" : "" ?>">
                    <div class="clearfix">
                        <span class="circle" style="background-image: url(<?= PLUGINS_URL."/management/assets/img/users.png"?>); background-repeat: no-repeat; background-size: cover;"></span>
                        <div class="inner">
                            <div class="title"><?=__('Users'); ?></div>
                            <div class="sub"><?=__('Registered Users'); ?></div>
                        </div>
                        <a class="full-link" href="<?= $baseUrl."?a=users" ?>"></a>
                    </div>
                </div>
                <div class="aside-list-item <?= $action == 'accounts' ? "active" : "" ?>">
                    <div class="clearfix">
                        <span class="circle" style="background-image: url(<?= PLUGINS_URL."/management/assets/img/accounts.png"?>); background-repeat: no-repeat; background-size: cover;"></span>
                        <div class="inner">
                            <div class="title"><?=__('Accounts'); ?></div>
                            <div class="sub"><?=__('Instagram Accounts'); ?></div>
                        </div>
                        <a class="full-link" href="<?= $baseUrl."?a=accounts" ?>"></a>
                    </div>
                </div>
                <div class="aside-list-item <?= $action == 'maintenance' ? "active" : "" ?>">
                    <div class="clearfix">
                        <span class="circle" style="background-image: url(<?= PLUGINS_URL."/management/assets/img/manager.png"?>); background-repeat: no-repeat; background-size: cover;"></span>
                        <div class="inner">
                            <div class="title"><?=__('Maintenance'); ?></div>
                            <div class="sub"><?=__('System Maintenance'); ?></div>
                        </div>
                        <a class="full-link" href="<?= $baseUrl."?a=maintenance" ?>"></a>
                    </div>
                </div>
                <div class="aside-list-item <?= $action == 'reports' ? "active" : "" ?>">
                    <div class="clearfix">
                        <span class="circle" style="background-image: url(<?= PLUGINS_URL."/management/assets/img/reports.png"?>); background-repeat: no-repeat; background-size: cover;"></span>
                        <div class="inner">
                            <div class="title"><?=__('Reports'); ?></div>
                            <div class="sub"><?=__("See what's happening"); ?></div>
                        </div>
                        <a class="full-link" href="<?= $baseUrl."?a=reports" ?>"></a>
                    </div>
                </div>

            </div>


        </aside>

        <section class="skeleton-content">
            <div style="width: 96%; margin: 2%">
                <?php
                if($action == 'users')
                    require_once(PLUGINS_PATH."/".$this->getVariable("idname")."/views/fragments/users.fragment.php");
                if($action == 'accounts')
                    require_once(PLUGINS_PATH."/".$this->getVariable("idname")."/views/fragments/accounts.fragment.php");
                if($action == 'dashboard')
                    require_once(PLUGINS_PATH."/".$this->getVariable("idname")."/views/fragments/dashboard.fragment.php");
                if($action == 'maintenance')
                    require_once(PLUGINS_PATH."/".$this->getVariable("idname")."/views/fragments/maintenance.fragment.php");
                if($action == 'reports')
                    require_once(PLUGINS_PATH."/".$this->getVariable("idname")."/views/fragments/reports.fragment.php");
                ?>
            </div>
        </section>
    </div>
</div>