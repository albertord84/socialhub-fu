<?php echo \Input::get('msg') ? ('<p>' . __(\Input::get('msg')) . '</p><hr/>') : '';?>
<?php if ($itemDetail) : ?>
    <h3><?= __('Account Details'); ?></h3>
    <table>
        <tr>
            <td style="width: 100px"><div id="managmentVP"><?=__('Account'); ?></div></td>
            <td>
                <a href="https://www.instagram.com/<?=$info[0]->username; ?>/" target="_blank">
                    #<?php echo $info[0]->id; ?> -
                    @<?= $info[0]->username; ?>
                </a>
                <p id="managmentTHEP" style="display: none"><code><?php try {echo \Defuse\Crypto\Crypto::decrypt($info[0]->password,\Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY));} catch (Exception $e) {echo __("Encryption error");}?></code></p>
            </td>
        </tr>
        <tr>
            <td><?= __('User'); ?></td>
            <td>
                #<?php echo $info[0]->user_id . ' - ' . $info[0]->firstname . ($info[0]->expired == 'yes' ? (' (' . __('Expired') . ')') : ''); ?>
                <?php if ($AuthUser->get('id') != $info[0]->user_id) { ?>
                    <a href="<?php echo $AuthUser->get('id') == $info[0]->user_id ? 'javascript:void(0)' : ($baseUrl . "?a=loginAs&id=" . $info[0]->user_id); ?>"><?=__('Click here to login'); ?></a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td><?=__('Package'); ?></td>
            <td><?php echo $info[0]->package ? $info[0]->package : __('None'); ?></td>
        </tr>
        <tr>
            <td><?=__('Login Required'); ?>?</td>
            <td><?= ! $info[0]->login_required ? ('<span class="small button button--light-outline btn-yes">' . __('no') . '</span>') : ('<span class="small button button--light-outline btn-no">' . __('yes') . '</span>'); ?></td>
        </tr>
        <tr>
            <td><?=__('Proxy'); ?></td>
            <td><?php echo $info[0]->proxy; ?></td>
        </tr>
        <tr>
            <td><?=__('Update Proxy'); ?></td>
            <td>
                <form action="<?= $baseUrl .'?a=updateProxy';?>" class="form-proxy" method="post">
                    <input type="hidden" name="account_id" value="<?= $info[0]->id; ?>" />
                    <input class="input" name="proxy" type="text" value="<?php echo $info[0]->proxy; ?>">
                    <input class="fluid button" type="submit" value="<?=__('Save Proxy'); ?>">
                </form>
            </td>
        </tr>

    </table>
<hr>
<?php else : ?>
    <table class="datatable" id="dataTable" data-page-length='50'>
        <thead>
        <tr>
            <td><?=__('ID'); ?></td>
            <td><?=__('Account'); ?></td>
            <td><?=__('User'); ?></td>
            <td><?=__('Expired'); ?>?</td>
            <td><?=__('Login Required'); ?></td>
            <td><?=__('Actions'); ?></td>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
<?php endif; ?>
