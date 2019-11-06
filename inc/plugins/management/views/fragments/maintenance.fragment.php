    <div class="form-result"></div>
    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('Delete Users (Expired Accounts)'); ?></h2>        
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=maintenance"; ?>" method="post" class="js-ajax-form">            
            <input type="hidden" name="maintenance" value="users" />
            <div class="clearfix mb-20">
                <div class="col s5 m5">
                    <label class="form-label"><?= __('Earlier than:'); ?></label>
                    <select class="input" name="days">
                        <option value="30">30 <?=__('days')?></option>
                        <option value="60">60 <?=__('days')?></option>
                        <option value="90">90 <?=__('days')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s5 m5">
                    <label class="form-label"><?= __('Limit Users'); ?></label>
                    <select class="input" name="limit">
                        <option value="50">50 <?=__('Users')?></option>
                        <option value="100">100 <?=__('Users')?></option>
                        <option value="150">150 <?=__('Users')?></option>
                        <option value="200">200 <?=__('Users')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s2 m2 s-last m-last l-last">
                    <input class="fluid button button--outline button--oval" type="submit" value="<?=__('Run')?>" style="margin-top: 22px">
                </div>
            </div>
            <p class="inline-block" style="margin-top: -10px;"><small><?= __('30 days ago:') ?> <strong><?= $expireUsers ?></strong></small></p>
            <label class="inline-block pull-right">
                <input type="checkbox" 
                       class="checkbox" 
                       name="search"
                       value="1" checked>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?= __('Search quantity') ?>
                    <span class="tooltip tippy" 
                          data-position="top"
                          data-size="small"
                          title="<?= __('Only you can check how many users were found before deleting the records.') ?>">
                          <span class="mdi mdi-help-circle"></span>
                    </span>
                </span>
            </label>
        </form>
    </div>
    <hr  class="clearfix mb-20">

    <div class="section-header clearfix  pt-20">
        <h2 class="section-title mr-60"><?= __('Delete Modules Activity Log'); ?></h2>        
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=maintenance"; ?>" method="post" class="js-ajax-form">
            <input type="hidden" name="maintenance" value="logs" />            
            <div class="clearfix mb-20">
                <div class="col s4 m4">
                    <label class="form-label"><?= __('Earlier than:'); ?></label>
                    <select class="input" name="days">
                        <option value="30">30 <?=__('days')?></option>
                        <option value="60">60 <?=__('days')?></option>
                        <option value="90">90 <?=__('days')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s4 m4">
                    <label class="form-label"><?= __('Module Type'); ?></label>
                    <select class="input" name="type">
                        <?= $modules ?>
                    </select>
                </div>
                <div class="col s2 m2">
                    <label class="form-label"><?= __('Limite Logs'); ?></label>
                    <select class="input" name="limit">
                        <option value="1000">1.000 <?=__('Logs')?></option>
                        <option value="2500">2.500 <?=__('Logs')?></option>
                        <option value="5000">5.000 <?=__('Logs')?></option>
                        <option value="10000">10.000 <?=__('Logs')?></option>
                        <option value="20000">20.000 <?=__('Logs')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s2 m2 s-last m-last l-last">
                    <input class="fluid button button--outline button--oval" type="submit" value="<?=__('Run')?>" style="margin-top: 22px">
                </div>
            </div>
            <label class="pull-right">
                <input type="checkbox" 
                       class="checkbox" 
                       name="search"
                       value="1" checked>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?= __('Search quantity') ?>
                    <span class="tooltip tippy" 
                          data-position="top"
                          data-size="small"
                          title="<?= __('Just for you to check how many logs were found before deleting the logs.') ?>">
                          <span class="mdi mdi-help-circle"></span>    
                    </span>
                </span>
            </label>
        </form>
    </div>
    <hr  class="clearfix mb-20">

    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('Delete Posts'); ?></h2>
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=maintenance"; ?>" method="post" class="js-ajax-form">
            <input type="hidden" name="maintenance" value="posts-posted" />
            <div class="clearfix mb-20">
                <div class="col s3 m3">
                    <label class="form-label"><?= __('Earlier than:'); ?></label>
                    <select class="input" name="days">
                        <option value="5">5 <?=__('days')?></option>
                        <option value="15">15 <?=__('days')?></option>
                        <option value="30" selected="selected">30 <?=__('days')?></option>
                        <option value="60">60 <?=__('days')?></option>
                        <option value="90">90 <?=__('days')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s3 m3">
                    <label class="form-label"><?= __('Limit'); ?></label>
                    <select class="input" name="limit">
                        <option value="50">50 <?=__('Posts')?></option>
                        <option value="100">100 <?=__('Posts')?></option>
                        <option value="150">150 <?=__('Posts')?></option>
                        <option value="200">200 <?=__('Posts')?></option>
                        <option value="all"><?=__('All (Care)')?></option>
                    </select>
                </div>
                <div class="col s2 m2 mt-35">
                    <label>
                        <input type="radio" 
                               class="radio" 
                               name="posts"
                               value="published" checked>
                        <span>
                            <span class="icon"></span>
                            <?= __('Published') ?>
                        </span>
                    </label>
                </div>
                <div class="col s2 m2 mt-35">
                    <label>
                        <input type="radio" 
                               class="radio" 
                               name="posts"
                               value="unpublished">
                        <span>
                            <span class="icon"></span>
                            <?= __('Unpublished') ?>
                        </span>
                    </label>
                </div>
                <div class="col s2 m2 s-last m-last l-last">
                    <input class="fluid button button--outline button--oval" type="submit" value="<?=__('Run')?>" style="margin-top: 22px">
                </div>
            </div>
            <p class="inline-block" style="margin-top: -10px;">
                <small>
                    <?= __('30 days ago:')." ".__('Published: ') ?><strong><?= $postsPublished ?></strong> & <?= __('Unpublished: ') ?><strong><?= $postsUnpublished ?></strong>
                </small>
            </p>
            <label class="pull-right">
                <input type="checkbox" 
                       class="checkbox" 
                       name="search"
                       value="1" checked>
                <span>
                    <span class="icon unchecked">
                        <span class="mdi mdi-check"></span>
                    </span>
                    <?= __('Search quantity') ?>
                    <span class="tooltip tippy" 
                          data-position="top"
                          data-size="small"
                          title="<?= __('Just for you to check how many posts were found before deleting the records.') ?>">
                          <span class="mdi mdi-help-circle"></span>    
                    </span>
                </span>
            </label>
        </form>
    </div>    
    <hr  class="clearfix mb-20">