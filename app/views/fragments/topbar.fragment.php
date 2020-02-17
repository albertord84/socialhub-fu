        <div id="topbar">
            <div class="clearfix">
                <a href="javascript:void(0)" class="topbar-mobile-menu-icon mdi mdi-menu"></a>

                <?php if (!empty($TopBar->title)): ?>
                    <h1 class="topbar-title"><?= $TopBar->title ?></h1>
                <?php endif ?>

                <?php if (!empty($TopBar->subtitle)): ?>
                    <div class="topbar-subtitle"><?= $TopBar->subtitle ?></div>
                <?php endif ?>

                <?php if (!empty($TopBar->btn)): ?>
                    <a class="topbar-special-link" href="<?= !empty($TopBar->btn["link"]) ? $TopBar->btn["link"] : "javascript:void(0)" ?>">
                        <?php if (!empty($TopBar->btn["icon"])): ?>
                            <span class="icon <?= $TopBar->btn["icon"] ?>"></span>
                        <?php endif ?>

                        <?php if (!empty($TopBar->btn["title"])): ?>
                            <?= $TopBar->btn["title"] ?>
                        <?php endif ?>
                    </a>
                <?php endif ?>

                <div class="topbar-actions clearfix">
                    
                    <script>
                       // @see https://docs.headwayapp.co/widget for more configuration options.
                       var HW_config = {
                           selector: ".headway-bell", // CSS selector where to inject the badge
                           account:  "ypvQD7",
                           trigger: ".toggleHeadway",
                           translations: {
                               title: "Notifications",
                               readMore: "Read more",
                               labels: {
                                   "new": "News",
                                   "improvement": "Updates",
                                   "fix": "Fixes"
                               }
                           }
                       }
                    </script>
                    <script async src="https://cdn.headwayapp.co/widget.js"></script>
                    <div class="item" href="#">
                       <a class="link toggleHeadway">
                       <span class="sli sli-bell icon headway-bell"></span> 
                       </a>
                    </div>
                    <div class="item">
                        <div class="topbar-profile clearfix">
                            <span class="greeting">
                                <?= __("Hi, %s!", htmlchars($AuthUser->get("firstname"))) ?>
                            </span>
                            
                            <div class="pull-left clearfix context-menu-wrapper">
                                <a href="javascript:void(0)" class="circle">
                                    <span>
                                        <?= 
                                            mb_substr($AuthUser->get("firstname"), 0, 1) .
                                            mb_substr($AuthUser->get("lastname"), 0, 1)
                                        ?>
                                    </span>
                                </a>

                                <a href="javascript:void(0)" class="mdi mdi-chevron-down arrow"></a>

                                <div class="context-menu">
                                    <ul>
                                        <li><a href="<?= APPURL."/profile" ?>"><?= __('Profile') ?></a></li>
                                        <li><a href="<?= APPURL."/logout" ?>"><?= __('Logout') ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>