<div class="section-header clearfix">
    <h2 class="section-title">
        <?= $info['daysAgo'] ? __('Your numbers in the last %s days', $info['daysAgo']) : __('Your numbers'); ?>
    </h2>
</div>
<div class="managment-dashboard">
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= $info['countUsers']; ?></div>
                <div class="sub"><?=__('Users'); ?></div>
            </div>
        </div>
    </div>
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= $info['countAccounts']; ?></div>
                <div class="sub"><?=__('Accounts'); ?></div>
            </div>
        </div>
    </div>
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= $info['countPosts']; ?></div>
                <div class="sub"><?=__('Published Posts'); ?></div>
            </div>
        </div>
    </div>
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= readableNumber($info['totalActions'], 3); ?></div>
                <div class="sub"><?= __('Automations'); ?></div>
            </div>
        </div>
    </div>
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= $info['countUsersActive']; ?></div>
                <div class="sub"><?=__('Active Users'); ?></div>
            </div>
        </div>
    </div>
    <div class="box-list">
        <div class="box-list-item text-c">
            <div class="inner">
                <div class="title"><?= $info['countAccountsActive']; ?></div>
                <div class="sub"><?=__('Active Accounts'); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="clearfix"></div>
<?php
if ($info['accountsByDay']) :
    $label = [];
    $data = [];
    $randomColors = [];
    $label2 = [];
    $data2 = [];
    $date = null;
    foreach($info['accountsByDay'] as $u)
    {
        $date = new DateTime($u['dt']);
        $label[] = '"' . $date->format($info['dateFormat']) . '"';
        $data[] = $u['total'];
        $randomColors[] = '"' . $info['colors'][array_rand($info['colors'], 1)] . '"';
    }

    foreach($info['accountsActiveByDay'] as $ua)
    {
        $data3[] = $ua['total'];
        $randomColors[] = '"' . $info['colors'][array_rand($info['colors'], 1)] . '"';
    }

    ?>
    <div class="section-header clearfix">
        <h2 class="section-title"><?= __('New Accounts by day'); ?></h2>
    </div>
    <canvas id="accountsGraphic" width="" height=""></canvas>
    <script>
        new Chart(document.getElementById("accountsGraphic"), {
            "type": "line",
            "data": {
                "labels": [<?= implode(',', $label);?>],
                "datasets": [{
                    "label": "<?= __('Recent IG Active Accounts');?>",
                    "data": [<?= implode(',', $data3);?>],
                    "fill": true,
                    "backgroundColor": <?= $randomColors[3];?>
                },{
                    "label": "<?= __('Recent IG Accounts');?>",
                    "data": [<?= implode(',', $data);?>],
                    "fill": true,
                    "backgroundColor": <?= $randomColors[0];?>
                }]
            },
            "options": {
                "responsive": true,            
                "tooltips": {
                    "mode": 'index',
                    "intersect": false,
                },
                "hover": {"mode": 'nearest', "intersect": true },
                "scales": {"yAxes": [{"ticks": {"beginAtZero": true}}]}
            }
        });
    </script>
<?php endif; ?>
<div class="clearfix pt-20"></div>
<?php
    if ($info['usersByDay']) :
        $label = [];
        $data = [];
        $randomColors = [];
        $date = null;
        foreach($info['usersByDay'] as $u)
        {
            $date = new DateTime($u['dt']);
            $label[] = '"' . $date->format($info['dateFormat']) . '"';
            $data[] = $u['total'];
            $randomColors[] = '"' . $info['colors'][array_rand($info['colors'], 1)] . '"';
        }
?>
<div class="section-header clearfix">
    <h2 class="section-title"><?= __('News users by day'); ?></h2>
</div>
<canvas id="userGraphic" width="" height=""></canvas>
<script>
    new Chart(document.getElementById("userGraphic"), {
        "type": "bar",
        "data": {
            "labels": [<?= implode(',', $label);?>],
            "datasets": [{
                "label": "<?= __('Latest Registered users');?>",
                "data": [<?= implode(',', $data);?>],
                "fill": true,
                "backgroundColor": [<?= implode(',', $randomColors);?>]
            }]
        },
        "options": {
            "responsive": true,            
            "tooltips": {
                "mode": 'index',
                "intersect": false,
            },
            "hover": {"mode": 'nearest', "intersect": true },
            "scales": {"yAxes": [{"ticks": {"beginAtZero": true}}]}
        }
    });
</script>
<?php endif; ?>

<div class="clearfix pt-20"></div>

<?php
if ($info['accountsUsersByDay']) :
    $label = [];
    $data1 = [];
    $data2 = [];
    $randomColors = [];
    $date = null;
    foreach($info['accountsUsersByDay'] as $u)
    {
        $date = new DateTime($u['dt']);
        $label[] = '"' . $date->format($info['dateFormat']) . '"';
        $data1[] = $u['totalAccounts'];
        $data2[] = $u['totalUsers'];
        $randomColors[] = '"' . $info['colors'][array_rand($info['colors'], 1)] . '"';
    }

    ?>
    <div class="section-header clearfix">
        <h2 class="section-title"><?= __('New Users x New Accounts by Day'); ?></h2>
    </div>
    <canvas id="accountsUsersGraphic" width="" height=""></canvas>
    <script>
        new Chart(document.getElementById("accountsUsersGraphic"), {
            "type": "line",
            "data": {
                "labels": [<?= implode(',', $label);?>],
                "datasets": [{
                    "label": "<?= __('New IG Active Accounts');?>",
                    "data": [<?= implode(',', $data3);?>],
                    "fill": true,
                    "backgroundColor": <?= $randomColors[2];?>
                },{
                    "label": "<?= __('New IG Accounts');?>",
                    "data": [<?= implode(',', $data1);?>],
                    "fill": true,
                    "backgroundColor": <?= $randomColors[0];?>
                },{
                    "label": "<?= __('New Users');?>",
                    "data": [<?= implode(',', $data2);?>],
                    "fill": true,
                    "backgroundColor": <?= $randomColors[5];?>
                }]
            },
            "options": {
                "responsive": true,            
                "tooltips": {
                    "mode": 'index',
                    "intersect": false,
                },
                "hover": {"mode": 'nearest', "intersect": true },
                "scales": {"yAxes": [{"ticks": {"beginAtZero": true}}]}             
            }
        });
    </script>
<?php endif; ?>
<div class="pt-20"></div>

