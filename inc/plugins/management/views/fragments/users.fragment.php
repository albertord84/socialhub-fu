<?php
    function setFone($val, $dest='link') {
        $val = preg_replace('%[^0-9]%iUs','',$val);
        if ($dest == 'link') {
            if (substr($val, 0, 2) == '55') {
                return $val;
            } else {
                return '55' . $val;
            }
        } else {
            $ddd = null;
            $nono= null;
            $lenght = strlen($val);

            if($lenght<8)
                return $val;

            if($lenght>12){
                $val = substr($val,2);
            }
            if(substr($val,0,1)=='0') {
                $val = substr($val, 1);
            }
            $lenght = strlen($val);
            if($lenght>9){
                $ddd = substr($val,0,2);
                $val = substr($val, 2);
                $lenght = strlen($val);
            }

            if($lenght==9){
                $val = substr($val,1,8);
                $nono = '9';
            }

            if(empty($val)){
                return $val;
            }
            return ($ddd!==null?'('.$ddd.') ':'').($nono!==null?$nono.' ':'').substr($val,0,4).'-'.substr($val,-4);
        }
    }
?>
<?php if (isset($loginError)) : ?>
    <div class="no-data">
        <span class="no-data-icon sli sli-minus"></span>
        <p><?=__('Invalid data'); ?></p>
    </div>
<?php else: ?>
<div class="pre-datatable">
    <table class="datatable" id="dataTable" data-page-length='50'>
        <thead>
        <tr>
            <td><?=__('ID'); ?></td>
            <td><?=__('Name'); ?></td>
            <td><?=__('Email'); ?></td>
            <td><?=__('Expired'); ?></td>
            <td><?=__('Type'); ?></td>
            <td><?=__('Total Accounts'); ?></td>
            <td><?=__('Package'); ?></td>
            <td><?=__('Actions'); ?></td>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<?php endif; ?>