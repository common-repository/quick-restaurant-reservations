<?php

if (! defined('ABSPATH') ) { exit; // Exit if accessed directly
}

$list = QRR_Restaurants::get_list_of_restaurants();

?>

<div class="wrap_qrr">
    <h2><?php _e('Create Booking', 'qrr'); ?></h2>
    <p><?php _e('Select the restaurant for the booking:', 'qrr'); ?></p>

    <?php
    if (empty($list)) {
        _ex('No restaurants yet. Please create one.', 'Create booking', 'qrr');
    } else {
        foreach( $list as $item ) {
            echo '<p>';
            echo '<a href="'.esc_url(add_query_arg(array('restaurant_id' => $item['value'] ))).'">'.esc_html($item['title']).'</a>';
            echo '</p>';
        }
    }
    ?>

</div>
