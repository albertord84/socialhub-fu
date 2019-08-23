<?php if ($info['viewType'] == 'form') { ?>
    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('New Users by Day/Month'); ?></h2>
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=reports"; ?>" method="post">
            <input type="hidden" name="report" value="users" />
            <div class="clearfix mb-20">
                <div class="col s4 m4">
                    <label class="form-label"><?= __('Group by'); ?></label>
                    <select class="input" name="group_by">
                        <option value="day"><?=__('Day')?></option>
                        <option value="month"><?=__('Month')?></option>
                    </select>
                </div>

                <div class="col s4 m4">
                    <label class="form-label"><?=__('Day Range')?></label>
                    <input class="input leftpad js-datepicker"
                           data-range="true"
                           data-multiple-dates-separator=" - "
                           name="time"
                           data-position="bottom left"
                           data-date-format="<?= str_replace(["Y", "m", "d", "F"], ["yyyy", "mm", "dd", "MM"], $info['dateFormat']) ?>"
                           data-user-datetime-format="<?= $info['dateFormat'] ?>"
                           data-timepicker="false"
                           type="text"
                           autocomplete="off"
                    >
                  <ul class="field-tips"><li><?=__('Always select the DAYS'); ?></li></ul>
                </div>
                <div class="col s4 m4 s-last m-last l-last">
                    <input class="fluid button button--outline" type="submit" value="<?=__('Generate')?>" style="margin-top: 22px">
                </div>
            </div>
        </form>
    </div>
    <hr  class="clearfix mb-20">

    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('New Accounts by Day/Month'); ?></h2>
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=reports"; ?>" method="post">
            <input type="hidden" name="report" value="accounts" />
            <div class="clearfix mb-20">
                <div class="col s4 m4">
                    <label class="form-label"><?= __('Group by'); ?></label>
                    <select class="input" name="group_by">
                        <option value="day"><?=__('Day')?></option>
                        <option value="month"><?=__('Month')?></option>
                    </select>
                </div>

                <div class="col s4 m4">
                    <label class="form-label"><?=__('Day Range')?></label>
                    <input class="input leftpad js-datepicker"
                           data-range="true"
                           data-multiple-dates-separator=" - "
                           name="time"
                           data-position="bottom left"
                           data-date-format="<?= str_replace(["Y", "m", "d", "F"], ["yyyy", "mm", "dd", "MM"], $info['dateFormat']) ?>"
                           data-user-datetime-format="<?= $info['dateFormat'] ?>"
                           data-timepicker="false"
                           type="text"
                           autocomplete="off"
                    >
                  <ul class="field-tips"><li><?=__('Always select the DAYS'); ?></li></ul>
                </div>
                <div class="col s4 m4 s-last m-last l-last">
                    <input class="fluid button button--outline" type="submit" value="<?=__('Generate')?>" style="margin-top: 22px">
                </div>
            </div>
        </form>
    </div>
    <hr  class="clearfix mb-20">

    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('New Accounts x New Users'); ?></h2>
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=reports"; ?>" method="post">
            <input type="hidden" name="report" value="accounts_users" />
            <div class="clearfix mb-20">
                <div class="col s4 m4">
                    <label class="form-label"><?= __('Group by'); ?></label>
                    <select class="input" name="group_by">
                        <option value="day"><?=__('Day')?></option>
                        <option value="month"><?=__('Month')?></option>
                    </select>
                </div>

                <div class="col s4 m4">
                    <label class="form-label"><?=__('Day Range')?></label>
                    <input class="input leftpad js-datepicker"
                           data-range="true"
                           data-multiple-dates-separator=" - "
                           name="time"
                           data-position="bottom left"
                           data-date-format="<?= str_replace(["Y", "m", "d", "F"], ["yyyy", "mm", "dd", "MM"], $info['dateFormat']) ?>"
                           data-user-datetime-format="<?= $info['dateFormat'] ?>"
                           data-timepicker="false"
                           type="text"
                           autocomplete="off"
                    >
                  <ul class="field-tips"><li><?=__('Always select the DAYS'); ?></li></ul>
                </div>
                <div class="col s4 m4 s-last m-last l-last">
                    <input class="fluid button button--outline" type="submit" value="<?=__('Generate')?>" style="margin-top: 22px">
                </div>
            </div>
        </form>
    </div>
    <hr  class="clearfix mb-20">
    <div class="section-header clearfix  pt-20">
        <h2 class="section-title"><?= __('Actions by Day/Month'); ?></h2>
    </div>
    <div class="clearfix">
        <form action="<?=$baseUrl."?a=reports"; ?>" method="post">
            <input type="hidden" name="report" value="actions" />
            <div class="clearfix mb-20">
                <div class="col s3 m3">
                    <label class="form-label"><?= __('Group by'); ?></label>
                    <select class="input" name="group_by">
                        <option value="day"><?=__('Day')?></option>
                        <option value="month"><?=__('Month')?></option>
                    </select>
                </div>

                <div class="col s3 m3">
                    <label class="form-label"><?=__('Day Range')?></label>
                    <input class="input js-datepicker"
                           data-range="true"
                           data-multiple-dates-separator=" - "
                           name="time"
                           data-position="top left"
                           data-date-format="<?= str_replace(["Y", "m", "d", "F"], ["yyyy", "mm", "dd", "MM"], $info['dateFormat']) ?>"
                           data-user-datetime-format="<?= $info['dateFormat'] ?>"
                           data-timepicker="false"
                           type="text"
                           autocomplete="off"
                    >
                  <ul class="field-tips"><li><?=__('Always select the DAYS'); ?></li></ul>
                </div>

                <div class="col s3 m3">
                    <label class="form-label"><?=__('Actions')?></label>
                    <select class="input" name="action">
                        <?php foreach($info['plugins'] as $k => $v) { ?>
                            <option value="<?=$k?>"><?=$v?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col s3 m3 s-last m-last l-last">
                    <input class="fluid button button--outline" type="submit" value="<?=__('Generate')?>" style="margin-top: 22px">
                </div>
            </div>
        </form>
    </div>
    <hr  class="clearfix mb-20">
<?php } else {
    $date = new \DateTime();
    $sum = 0;
?>
    <?php if ($info['action'] == 'users') { ?>
        <table class="report">
            <thead>
            <tr>
                <th colspan="3"><?= $info['title']; ?></th>
            </tr>
            </thead>
<?php if($info['data']) { ?>
            <tbody>
            <tr>
                <td><strong>#</strong></td>
                <td><strong><?= __('date'); ?></strong><?= $info['groupBy'] == 'month' ? (' (' . __('Year-month') . ')'): ''?></td>
                <td><strong><?= __('total'); ?></strong></td>
            </tr>
            <?php  foreach($info['data'] as $k => $d) { $sum += $d['total']; ?>
                <tr>
                    <td><?= $k+1;?></td>
                    <td><?= $info['dateObj']->modify($d['dt'])->format($info['groupBy'] == 'day' ? $info['dateFormat'] : 'Y-m');?></td>
                    <td><?= $d['total'];?></td>
                </tr>
            <?php }?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3">
                    <?= __('Total')?>: <strong><?= readableNumber($sum); ?></strong>
                    <hr>
                    <?= __('Average of new Users') . ' ' . ($info['groupBy'] == 'day' ? __('per day') : __('per month'))?>: <strong><?= readableNumber($sum / count($info['data'])); ?></strong>
                </th>
            </tr>
            </tfoot>
<?php } else  { ?><tbody><tr><td colspan="3"><?= __('no data'); ?></td></tr></tbody><?php } ?>
        </table>
    <?php } elseif ($info['action'] == 'accounts') { ?>
        <table class="report">
            <thead>
            <tr>
                <th colspan="3"><?= $info['title']; ?></th>
            </tr>
            </thead>
<?php if($info['data']) { ?>
            <tbody>
            <tr>
                <td><strong>#</strong></td>
                <td><strong><?= __('Date'); ?></strong><?= $info['groupBy'] == 'month' ? (' (' . __('Year-month') . ')'): ''?></td>
                <td><strong><?= __('Total'); ?></strong></td>
            </tr>
            <?php  foreach($info['data'] as $k => $d) { $sum += $d['total']; ?>
                <tr>
                    <td><?= $k+1;?></td>
                    <td><?= $info['dateObj']->modify($d['dt'])->format($info['groupBy'] == 'day' ? $info['dateFormat'] : 'Y-m');?></td>
                    <td><?= $d['total'];?></td>
                </tr>
            <?php }?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="3">
                    <?= __('Total')?>: <strong><?= readableNumber($sum); ?></strong>
                    <hr>
                    <?= __('Average of new Accounts')?>: <strong><?= readableNumber($sum / count($info['data'])); ?></strong>
                    <?= ($info['groupBy'] == 'day' ? __('per day') : __('per month')); ?>
                </th>
            </tr>
            </tfoot>
 <?php } else  { ?><tbody><tr><td colspan="3"><?= __('no data'); ?></td></tr></tbody><?php } ?>
        </table>
    <?php } elseif ($info['action'] == 'accounts_users') { ?>
        <table class="report">
            <thead>
            <tr>
                <th colspan="5"><?= $info['title']; ?></th>
            </tr>
            </thead>
<?php if($info['data']) { ?>
            <tbody>
            <tr>
                <td><strong>#</strong></td>
                <td><strong><?= __('Date'); ?></strong><?= $info['groupBy'] == 'month' ? (' (' . __('Year-month') . ')'): ''?></td>
                <td><strong><?= __('New Users'); ?></strong></td>
                <td><strong><?= __('New Accounts'); ?></strong></td>
                <td><strong><?= __('Total'); ?></strong></td>
            </tr>
            <?php $sum2 = 0; foreach($info['data'] as $k => $d) { $sum += $d['totalAccounts']; $sum2 += $d['totalUsers']; ?>
                <tr>
                    <td><?= $k+1;?></td>
                    <td><?= $info['dateObj']->modify($d['dt'])->format($info['groupBy'] == 'day' ? $info['dateFormat'] : 'Y-m');?></td>
                    <td><?= $d['totalUsers'];?></td>
                    <td><?= $d['totalAccounts'];?></td>
                    <td><?= $d['totalAccounts'] + $d['totalUsers'];?></td>
                </tr>
            <?php }?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2"><?= __('Total')?></th>
                <th><strong><?= readableNumber($sum2); ?></strong></th>
                <th><strong><?= readableNumber($sum); ?></strong></th>
                <th><strong><?= readableNumber($sum + $sum2); ?></strong></th>
            </tr>
            </tfoot>
<?php } else  { ?><tbody><tr><td colspan="5"><?= __('no data'); ?></td></tr></tbody><?php } ?>
        </table>
    <?php } elseif ($info['action'] == 'actions') { ?>
        <table class="report">
            <thead>
            <tr>
                <th colspan="5"><?= $info['title']; ?></th>
            </tr>
            </thead>
            <?php if($info['data']) { ?>
            <tbody>
            <tr>
                <td><strong>#</strong></td>
                <td><strong><?= __('Date'); ?></strong><?= $info['groupBy'] == 'month' ? (' (' . __('Year-month') . ')'): ''?></td>
                <td><strong><?= __('Success'); ?></strong></td>
                <td><strong><?= __('Error'); ?></strong></td>
                <td><strong><?= __('Total'); ?></strong></td>
            </tr>


            <?php foreach($info['data'] as $k => $d) { $sum += $d['total']; ?>
                <tr>
                    <td><?= $k+1;?></td>
                    <td><?= $info['dateObj']->modify($d['dt'])->format($info['groupBy'] == 'day' ? $info['dateFormat'] : 'Y-m');?></td>
                    <td><span style="color: #3b7cff"><?= $d['total_success'];?></span></td>
                    <td><span style="color: #ff0a0a"><?= $d['total_error'];?></span></td>
                    <td><span><?= $d['total'];?></span></td>
                </tr>
            <?php }?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="5">
                    <?= __('Total')?>: <strong><?= readableNumber($sum); ?></strong>
                    <hr>
                    <?= __('Average of Actions') . ' ' . ($info['groupBy'] == 'day' ? __('per day') : __('per month'))?>: <strong><?= readableNumber($sum / count($info['data'])); ?></strong>
                </th>
            </tr>
            </tfoot>
        <?php } else { ?>
            <tr>
                <th colspan="5"><?= __('no data'); ?></th>
            </tr>
        <?php }?>
        </table>
    <?php } ?>
<?php }  ?>