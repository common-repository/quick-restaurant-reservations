<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Booking_Edit
{

    private $post_type = 'qrr_booking';

    public $use_list_clients = false;
    public $clients = [];

    public function __construct()
    {

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 100);

        add_action('add_meta_boxes', array($this,'add_meta_box'), 100);
        add_action('do_meta_boxes', array($this,'modify_meta_boxes'), 100);
        add_action('save_post', array($this, 'save_metabox'));

        add_action('qrr_booking_post_type_before_metaboxes', array($this,'insert_style'));
    }

    public function admin_scripts( $hook )
    {
	    $this->use_list_clients = QRR()->settings->get('qrr_booking_edit_use_clients');

        if ($this->use_list_clients) {
            $this->clients = QRR_Clients::get_list_clients();
        }

        global $wp_version, $post, $pagenow, $typenow;

        $js_dir = QRR_PLUGIN_URL . 'assets/js/';
        $css_dir = QRR_PLUGIN_URL . 'assets/css/';
        $lib_dir = QRR_PLUGIN_URL . 'assets/libs/';

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';


        // QRR admin settings style
        if (qrr_is_admin_post_type_page($hook, $this->post_type)) {
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('moment');
            wp_register_script('vuejs', $lib_dir.'vue/vue'.$suffix.'.js', array(), '2.4.2', true);
            wp_enqueue_script('admin-edit-qrr_booking', $js_dir.'admin-edit-qrr_booking.js', array('jquery','jquery-ui-datepicker','vuejs','moment'), QRR_VERSION, true);
            wp_localize_script(
                'admin-edit-qrr_booking', 'qrr_booking', array(
                'booking_id' => $post->ID,
                'booking' => array('email' => get_post_meta($post->ID, 'qrr_email', true) ),
                'clients' => $this->clients,
                'emails' => is_array(get_post_meta($post->ID, 'qrr_email_records', true)) ? array_reverse(get_post_meta($post->ID, 'qrr_email_records', true)) : array()
                )
            );
        }

    }

    public function add_meta_box()
    {
        add_meta_box('qrr_info', __('Booking data', 'qrr'), array($this, 'metabox_info'), $this->post_type, 'normal', 'high');
        add_meta_box('qrr_emails', __('Booking emails', 'qrr'), array($this, 'metabox_emails'), $this->post_type, 'normal', 'high');
        add_meta_box('qrr_booking_save', __('Booking Actions', 'qrr'), array($this, 'metabox_actions'), $this->post_type, 'side', 'high');
    }

    public function modify_meta_boxes( $post_type )
    {

        global $wp_meta_boxes;

        if ($post_type == $this->post_type ) {

            // Remove save publish
            remove_meta_box('submitdiv', null, 'side');

            // Remove all metaboxes except qrr_global
            $remove_all_other_metaboxes = apply_filters('qrr_booking_remove_all_other_metaboxes', false);

            $allowed_metaboxes = array('qrr_info', 'qrr_emails', 'qrr_booking_save');

            if ($remove_all_other_metaboxes) {
                $filters = array('side','normal');
                foreach( $filters as $filter ) {
                    foreach($wp_meta_boxes['qrr_booking'][$filter] as $key => $data ) {
                        foreach($data as $key2 => $data2) {
                            if (!in_array($key2, $allowed_metaboxes)) {
                                unset($wp_meta_boxes['qrr_booking'][$filter][$key][$key2]);
                            }
                        }
                    }
                }
            }

        }
    }

    public function metabox_actions()
    {

        global $post;

        wp_nonce_field('qrr_booking_metabox_nonce', 'qrr_booking_metabox_nonce');

        // For inserting custom style
        do_action('qrr_booking_post_type_before_metaboxes');

        ?>
        <div>
            <label><?php _e('Status', 'qrr'); ?></label>
            <select name="post_status">
            <?php foreach( self::get_list_status() as $status => $data){
                $selected = selected($post->post_status, $status);
                echo '<option value="'.esc_attr($status).'" '.esc_attr($selected).'>'.esc_html($data['label_text']).'</option>';
            }?>
            </select>

        </div>

        <div id="send_email_to_customer">
            <label><?php _e('Send Email to Customer', 'qrr'); ?> <input type="checkbox" name="qrr_send_email"></label>
        </div>

        <hr>

        <div id="delete-action"><a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>"><?php _e('Move to trash', 'qrr'); ?></a></div>
        <input type="submit" class="button save_order button-primary tips" name="save" value="<?php _e('Save Booking', 'qrr'); ?>" data-tip="<?php _e('Save/update the booking', 'qrr'); ?>" />
        <?php
    }

    public function set_default_post( $post )
    {

        $post->post_status = 'pending';
        update_post_meta($post->ID, 'qrr_party', 2);

    }


    public function metabox_info()
    {
        global $post;

        if ($post->post_status == 'auto-draft') {
            $this->set_default_post($post);
        }

        $restaurant_id = get_post_meta($post->ID, 'qrr_restaurant_id', true);
        $form = get_post_meta($post->ID, 'qrr_booking_form', true);
        //echo '<pre>'; print_r( $form ); echo '</pre>';
        $booking = new QRR_Booking_Form();

        $post_time = strtotime($post->post_date);

        $list_restaurants = $this->get_list_of_restaurants();

        ?>
        <table id="qrr_booking-edit" class="qrr-custom-fields">

            <tr>
                <th<label><?php echo __('Restaurant', 'qrr'); ?></label></th>
                <td><?php
                    $restaurant_id = get_post_meta($post->ID, 'qrr_restaurant_id', true);
                foreach($list_restaurants as $item){
                    if ($item['value'] == $restaurant_id) {
                        echo esc_html($item['title']);
                    }
                }
                ?>
                </td>
            </tr>

            <tr>
                <th><label><?php echo __('Schedule', 'qrr'); ?></label></th>
                <td>
                    <?php echo get_post_meta($post->ID, 'qrr_schedule_name', true); ?>
                </td>
            </tr>

            <tr>
                <th><label><?php echo esc_html($booking->get_form_value($form, 'label_qrr-date', '', __('Date', 'qrr'))); ?></label></th>
                <td><input type="text" name="qrr_date" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_date', true)); ?>"></td>
            </tr>

            <tr>
                <th><label><?php echo esc_html($booking->get_form_value($form, 'label_qrr-time', '', __('Time', 'qrr'))); ?></label></th>
                <td>
                    <select name="qrr_time">
                    <?php
                        $hours = qrr_get_list_hours_for_interval();
                        $current_hour = get_post_meta($post->ID, 'qrr_time', true);
                    foreach( $hours as $hour ) {
                        $selected = selected($hour, $current_hour);
                        echo '<option value="'.esc_attr($hour).'" '.esc_attr($selected).'>'.esc_html($hour).'</option>';
                    }
                    ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label><?php echo esc_html($booking->get_form_value($form, 'label_qrr-party', '', __('Party', 'qrr'))); ?></label></th>
                <td><input type="text" name="qrr_party" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_party', true)); ?>"></td>
            </tr>

            <tr>
                <th><label><?php _ex('Duration', 'Booking', 'qrr'); ?></label></th>
                <td><?php
                if (!QRR_Active('capacity')) {
                    echo '<p>'._x('Booking Capacity is set to UNLIMITED.', 'qrr').'</p>';
                    include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
                } else {
                    $options = qrr_duration_options(array());
                    $value = get_post_meta($post->ID, 'qrr_duration', true);
                    echo '<select name="qrr_duration">';
                    foreach( $options as $item ) {
                        $selected = selected($item['value'], $value);
                        echo '<option value="'.esc_attr($item['value']).'" '.esc_attr($selected).'>'.esc_html($item['name']).'</option>';
                    }
                    echo '</select>';
                }
                ?>
                </td>
            </tr>
            <tr>
                <th><?php _ex('Tables', 'Booking', 'qrr'); ?></th>
                <td><?php
                if (!QRR_Active('capacity')) {
                    echo '<p>'._x('Booking Capacity is set to UNLIMITED.', 'qrr').'</p>';
                    include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
                } else {
                    $value = get_post_meta($post->ID, 'qrr_tables', true);
                    echo '<input type="text" name="qrr_tables" value="'.esc_attr($value).'">';
                    echo ' <br><small>Use this only when managing tables in the schedule, use table seats separated by commas:</small><br>';
                    echo ' <small>2,2,3 -> means 1 table of 2 seats + 1 table of 2 seats + 1 table of 3 seats</small><br>';
                }
                ?>

                </td>
            </tr>

            <?php
            if (QRR_Active('capacity')) {
                ?>
            <tr>
                <th><?php _ex('Table Name', 'Booking', 'qrr'); ?></th>
                <td>
                    <?php
                        $value = get_post_meta($post->ID, 'qrr_table_name', true);
                        echo '<input type="text" name="qrr_table_name" value="'.esc_attr($value).'">';
                        echo ' <br><small>Custom field to annotate your customer tables. Not visible for customers.</small><br>';
                        echo '<small>For example: Table 12, Table A1,...</small><br>';
                    ?>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <th><?php echo esc_html($booking->get_form_value($form, 'label_qrr-email', '', __('Email', 'qrr'))); ?></th>
                <td>
                    <input type="text" name="qrr_email" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_email', true)); ?>">
                    <?php
                        if ($this->use_list_clients) {
                            ?>
                            <i class="fa fa-arrow-left"></i>
                            <select id="fill_from_customer">
		                        <?php
		                        echo '<option value="">'.__('Fill from customer', 'qrr').'</option>';
		                        $clients = $this->clients;
		                        if (!empty($clients) ) {
			                        foreach($clients as $client) {
				                        echo '<option value="'.esc_attr($client['email']).'" data-name="'.esc_attr($client['name']).'" data-phone="'.esc_attr($client['phone']).'">'.esc_html($client['email']).' | '.esc_html($client['name']).'</option>';
			                        }
		                        }
		                        ?>
                            </select>
                        <?php }
                    ?>

                </td>
            </tr>

            <tr>
                <th><label><?php echo esc_html($booking->get_first_type_label('phone', $form, '', __('Phone', 'qrr'))); ?></label></th>
                <td><input type="text" name="qrr_phone" value="<?php echo esc_attr(get_post_meta($post->ID, 'qrr_phone', true)); ?>"></td>
            </tr>

            <?php


            // Custom fields are created when the form is saved
            // save data here instead of the form
            $custom_fields = get_post_meta($post->ID, 'qrr_custom_fields', true);

            if (!empty($custom_fields) ) {

                foreach( $custom_fields as $field ) {

                    switch( $field['type'] ) {

                    case 'text':
                    case 'phone':
                        ?>
                            <tr>
                                <th><?php echo esc_html($field['label']); ?></th>
                                <td><input type="text" class="large" name="<?php echo esc_attr($field['id']); ?>" value="<?php echo esc_attr($field['value']); ?>"></td>
                            </tr>
                            <?php
                        break;

                    case 'textarea':
                        ?>
                            <tr>
                                <th><?php echo esc_html($field['label']); ?></th>
                                <td><textarea class="large" name="<?php echo esc_attr($field['id']); ?>"><?php echo esc_html($field['value']); ?></textarea></td>
                            </tr>
                            <?php
                        break;

                    case 'select':
                        ?>
                            <tr>
                                <th><?php echo esc_html($field['label']); ?></th>
                                <td>
                                    <select name="<?php echo esc_attr($field['id']); ?>">
                                    <?php
                                    foreach($field['options'] as $option) {
                                        echo '<option value="'.esc_attr($option).'" '.esc_attr(selected($field['value'], $option)).'>'.esc_html($option).'</option>';
                                    }
                                    ?>
                                    </select>
                                </td>
                            </tr>
                            <?php
                        break;

                    case 'checkbox':
                        ?>
                            <tr>
                                <th><?php echo esc_html($field['label']); ?></th>
                                <td>
                                <?php
                                foreach($field['options'] as $option) {
                                    if (is_array($field['value'])) {
                                        $selected = in_array($option, $field['value']);
                                    } else {
                                        $selected = ($field['value'] == $option);
                                    }
                                    $checked = $selected ? 'checked' : '';
                                    echo '<input type="checkbox" name="'.esc_attr($field['id']).'[]" value="'.esc_attr($option).'" '.esc_attr($checked).'>'.esc_html($option).'<br>';
                                }
                                ?>
                                </td>
                            </tr>
                            <?php
                        break;

                    case 'radio':
                        ?>
                            <tr>
                                <th><?php echo esc_html($field['label']); ?></th>
                                <td>
                                <?php
                                foreach($field['options'] as $option) {
                                    if (is_array($field['value'])) {
                                        $selected = in_array($option, $field['value']);
                                    } else {
                                        $selected = ($field['value'] == $option);
                                    }
                                    $checked = $selected ? 'checked' : '';
                                    echo '<input type="radio" name="'.esc_attr($field['id']).'" value="'.esc_attr($option).'" '.esc_attr($checked).'>'.esc_html($option).'<br>';
                                }
                                ?>
                                </td>
                            </tr>
                            <?php
                        break;

                    default:
                        break;
                    }
                }
            }


            ?>

        </table>
        <?php

    }

    public function metabox_emails()
    {
        ?>
        <div id="qrr-emails">
            <div class="qrr-send-email-update">
                <div><?php _e('Send email to customer:', 'qrr'); ?></div>
                <textarea class="email-content" v-model="update_email"></textarea>
                <div v-if="!sending_message && !sent_message">
                    <a href="#" @click.prevent="send_email" class="button btn btn-main"><?php _ex('SEND EMAIL', 'Booking', 'qrr'); ?></a>
                </div>
                <div v-if="sending_message">
                    <?php _ex('..sending..', 'Booking', 'qrr'); ?>
                </div>
                <div v-if="sent_message">
                    <?php _ex('Email sent!', 'Booking', 'qrr'); ?>
                </div>
            </div>
            <hr>
            <table class="qrr-email-list">
                <tr class="qrr-email-item" v-for="item in email_list">
                    <td>
                        <div>
                            <strong v-text="format_time(item.time)"></strong>
                        </div>
                        <div>
                            <span class="sent-success" v-if="item.success"><?php _e('Success'); ?></span>
                            <span class="sent-error" v-if="!item.success"><?php _e('Error'); ?></span>
                        </div>
                    </td>
                    <td>
                        <div><?php _ex('To:', 'List emails', 'qrr'); ?> <span v-text="item.category"></span> (<span v-text="item.to"></span>)</div>
                        <div><?php _ex('Subject:', 'List emails', 'qrr'); ?> <span v-text="item.subject"></span></div>
                        <div><?php _ex('Message:', 'List emails', 'qrr'); ?> <a @click.prevent="show_email(item)" class="link-show-email" href="#"><?php _ex('show', 'List emails', 'qrr'); ?></a></div>
                    </td>
                </tr>
            </table>
            <div id="qrr-popup-email" :class="{'open': popup_show}">
                <div class="popup-inner-wrap">
                    <div class="popup-close"><a href="#" @click.prevent="close_popup">X</a></div>
                    <div class="popup-inner" v-if="email_selected != null" v-html="email_selected.message">
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    public function insert_style()
    {
        echo '<style>';
        include QRR_PLUGIN_DIR . 'assets/css/admin-qrr_booking.css';
        echo '</style>';
    }

    public function get_list_of_restaurants()
    {
        return QRR_Restaurants::get_list_of_restaurants();
    }


    public function save_metabox($post_id)
    {

        global $post;

        // Check if our nonce is set.
        if (! isset($_POST['qrr_booking_metabox_nonce']) ) {
            return;
        }

        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST['qrr_booking_metabox_nonce'], 'qrr_booking_metabox_nonce') ) {
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

        // don't re-run and prevent looping
        if (did_action('save_post') > 1 ) {
            return;
        }


        // Check email to see if I have to create a new customer
        $email = sanitize_email($_POST['qrr_email']);
        $name = sanitize_text_field($_POST['post_title']);
        $phone = sanitize_text_field($_POST['qrr_phone']);
        if (get_post_meta($post_id, 'qrr-email', true) != $email) {
            $client_id = QRR_Client_Model::set_up_new_client($email, $name, $phone);
        }


        // Update post meta
        $keys = array(
            'qrr_date',
            'qrr_time',
            'qrr_restaurant_id',
            'qrr_email',
            'qrr_party',
            'qrr_tables',
            'qrr_table_name',
            'qrr_duration',
            'qrr_phone'
        );

        $keys = apply_filters('qrr_booking_save_metadada_keys', $keys);

        foreach( $keys as $key ) {
            if (isset($_POST[$key])) {
                $value = sanitize_text_field($_POST[$key]);
                update_post_meta($post_id, $key, $value);
            }
        }


        // Update custom fields
        $custom_fields = get_post_meta($post_id, 'qrr_custom_fields', true);
        $new_custom_fields = array();
        if (!empty($custom_fields) ) {
            foreach( $custom_fields as $field ) {
                $field_id = $field['id'];
                if (isset($_POST[$field_id])) {
                    $field['value'] = sanitize_text_field($_POST[$field_id]);
                }
                $new_custom_fields[] = $field;
            }
        }
        update_post_meta($post_id, 'qrr_custom_fields', $new_custom_fields);


        // Update date and status
        //$date = sanitize_text_field($_POST['qrr_date']);
        //$time = sanitize_text_field($_POST['qrr_time']);
        //$post_date = $date.' '.$time.':00';
        $post->post_title = sanitize_text_field($_POST['post_title']);
        //$post->post_date = $post_date;
        //$post->post_date_gmt = $post_date;
        //$post->post_modified = $post_date;
        //$post->post_modified_gmt = $post_date;
        $post->post_status = sanitize_text_field($_POST['post_status']);

        wp_update_post($post);


        // Send email
        if (isset($_POST['qrr_send_email'])) {
            $qrr_book_email = new QRR_Booking_Email($post_id);
            $qrr_book_email->send_customer_email();
        }

    }


    public static function get_list_status()
    {

        $list = array();

        // Post status
        $list['pending'] = array(
            'label'                     => '<span class="status-pending tips" data-tip="' . _x('Pending', 'Quick restaurant reservations', 'qrr') . '">' . _x('Pending', 'Quick restaurant reservations', 'qrr') . '</span>',
            'label_text'                => _x('Pending', 'Quick restaurant reservations', 'qrr'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'qrr'),
        );

        $list['qrr-confirmed'] = array(
            'label'                     => '<span class="status-qrr-confirmed tips" data-tip="' . _x('Confirmed', 'Quick restaurant reservations', 'qrr') . '">' . _x('Confirmed', 'Quick restaurant reservations', 'qrr') . '</span>',
            'label_text'                => _x('Confirmed', 'Quick restaurant reservations', 'qrr'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Confirmed <span class="count">(%s)</span>', 'Confirmed <span class="count">(%s)</span>', 'qrr'),
        );

        $list['qrr-rejected'] = array(
            'label'                     => '<span class="status-qrr-rejected tips" data-tip="' . _x('Rejected', 'Quick restaurant reservations', 'qrr') . '">' . _x('Rejected', 'Quick restaurant reservations', 'qrr') . '</span>',
            'label_text'                => _x('Rejected', 'Quick restaurant reservations', 'qrr'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'qrr'),
        );

        $list['qrr-cancelled'] = array(
            'label'                     => '<span class="status-qrr-cancelled tips" data-tip="' . _x('Cancelled', 'Quick restaurant reservations', 'qrr') . '">' . _x('Cancelled', 'Quick restaurant reservations', 'qrr') . '</span>',
            'label_text'                => _x('Cancelled', 'Quick restaurant reservations', 'qrr'),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'qrr'),
        );


        $list = apply_filters('qrr_post_statuses_args', $list);

        return $list;

    }


    /*
    function replace_form_field_with_value( $form, $field_id, $new_value ) {

        $already_updated = false;
        foreach( $form as $field ) {

            if ($field_id == $field['name']) {

                if (!$already_updated) {
                    $field['value'] = $new_value;
                    $already_updated = true;
                } else {
                    $field['value'] = ''; // Rest options as empty because I'm grouping in one field when edit in the admin
                }

            }
        }

        return $form;
    }
    */

}

new QRR_Booking_Edit();
