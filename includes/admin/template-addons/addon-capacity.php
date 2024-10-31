<?php

if (QRR_Active_plugin('capacity')) {

    ?>
    <div>
        <a style="color: white; background: lightslategray;" class="button" target="_blank" href="<?php echo esc_url(QRR_Activate_license('capacity')); ?>">Activate your license</a>
    </div>
    <?php

}
else {

    ?>
    <div>
        <a style="color: white; background: lightslategray;" class="button" target="_blank" href="<?php echo esc_url(QRR()->get_addons_link()); ?>">Add-On to add this feature</a>
    </div>
    <?php

}

