<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Client_Edit
{

    private $post_type = 'qrr_client';

    public function __construct()
    {

        add_action('add_meta_boxes', array($this,'add_meta_box'), 100);
        add_action('do_meta_boxes', array($this,'modify_meta_boxes'), 100);
        add_action('save_post', array($this, 'save_metabox'));

    }

    public function add_meta_box()
    {
        add_meta_box('qrr_info', __('Client data', 'qrr'), array($this, 'metabox_info'), $this->post_type, 'normal', 'high');
        add_meta_box('qrr_list_bookings', __('Bookings', 'qrr'), array($this, 'metabox_bookings'), $this->post_type, 'normal', 'high');
    }

    public function modify_meta_boxes( $post_type )
    {

        global $wp_meta_boxes;

        if ($post_type == $this->post_type ) {

            // Remove save publish
            //remove_meta_box( 'submitdiv', null, 'side' );

            // Remove all metaboxes except qrr_global
            $remove_all_other_metaboxes = apply_filters('qrr_client_remove_all_other_metaboxes', false);

            $allowed_metaboxes = array('submitdiv', 'qrr_info', 'qrr_list_bookings');

            if ($remove_all_other_metaboxes) {
                $filters = array('side','normal');
                foreach( $filters as $filter ) {
                    foreach($wp_meta_boxes['qrr_client'][$filter] as $key => $data ) {
                        foreach($data as $key2 => $data2) {
                            if (!in_array($key2, $allowed_metaboxes)) {
                                unset($wp_meta_boxes['qrr_client'][$filter][$key][$key2]);
                            }
                        }
                    }
                }
            }

        }
    }

    public function metabox_info()
    {
        global $post;

        wp_nonce_field('qrr_client_metabox_nonce', 'qrr_client_metabox_nonce');

        ?>
        <table id="qrr_booking-edit" class="qrr-custom-fields">

            <tr>
                <th><?php echo __('Email', 'qrr'); ?></label></th>
                <td><input type="text" name="qrr_email" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_email', true)); ?>"></td>
            </tr>

            <tr>
                <th><?php echo __('Phone', 'qrr'); ?></label></th>
                <td><input type="text" name="qrr_phone" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_phone', true)); ?>"></td>
            </tr>

        </table>
        <?php
    }

    public function metabox_bookings()
    {
        global $post;

        $model = new QRR_Client_Model($post->ID);

        $list = $model->get_bookings(100);

        echo '<div>'._x('List of bookings for', 'Metabox', 'qrr').' <strong>'.esc_html($model->get_email()).'</strong></div>';

        if (empty($list) ) {
            echo _x('No bookings yet!', 'Client edit', 'qrr');
            return;
        }

        //echo '<pre>'; print_r( $list ); echo '</pre>';

        $list_statuses = QRR_Booking_Edit::get_list_status();
        echo '<table class="client-bookings">';
        echo '<tr>';
        echo '<td>'._x('ID', 'Client edit', 'qrr').'</td>';
        echo '<td>'._x('Date', 'Client edit', 'qrr').'</td>';
        echo '<td>'._x('Party', 'Client edit', 'qrr').'</td>';
        echo '<td>'._x('Status', 'Client edit', 'qrr').'</td>';
        echo '</tr>';
        foreach( $list as $item ) {

            $booking_id = $item['booking_id'];
            $status = $item['status'];

            echo '<tr>';
            echo '<td><a href="'.esc_url(get_edit_post_link($booking_id)).'">'.esc_html($booking_id).'</a></td>';
            echo '<td>'.esc_html(get_post_meta($booking_id, 'qrr_date', true)).' '.esc_html(get_post_meta($booking_id, 'qrr_time', true)).'</td>';
            echo '<td>'.esc_html(get_post_meta($booking_id, 'qrr_party', true)).'</td>';
            echo '<td>'.(isset($list_statuses[$status]) ? esc_html($list_statuses[$status]['label_text']) : esc_html($status) ).'</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '<style>
        .client-bookings td { padding: 5px 10px; }
        </style>';
    }

    public function save_metabox($post_id)
    {

        global $post;

        // Check if our nonce is set.
        if (! isset($_POST['qrr_client_metabox_nonce']) ) {
            return;
        }

        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST['qrr_client_metabox_nonce'], 'qrr_client_metabox_nonce') ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && $this->post_type == sanitize_text_field($_POST['post_type']) ) {
            if (! current_user_can('edit_post', $post_id) ) {
                return;
            }
        } else {
            if (! current_user_can('edit_post', $post_id) ) {
                return;
            }
        }

        // don't do anything on autosave, auto-draft, bulk edit, or quick edit
        if (wp_is_post_autosave($post_id) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
            return;
        }


        $email = sanitize_email($_POST['qrr_email']);
        $phone = sanitize_text_field($_POST['qrr_phone']);

        update_post_meta($post_id, 'qrr_email', $email);
        update_post_meta($post_id, 'qrr_phone', $phone);

    }

}

new QRR_Client_Edit();
