<?php

if (QRR_Active_plugin('fields')) {

    ?>
    <div>
        <a style="color: white; background: lightslategray;" class="button" target="_blank" href="<?php echo esc_url(QRR_Activate_license('fields')); ?>">Activate your license</a>
    </div>
    <?php

}
else {

    ?>
    <div>
        <a style="color: white; background: lightslategray;" class="button" target="_blank" href="<?php echo esc_url(QRR()->get_addons_link()); ?>">Add-On to add custom fields</a>
    </div>
    <?php

}
