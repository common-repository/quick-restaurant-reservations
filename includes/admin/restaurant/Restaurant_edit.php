<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Restaurant_Edit
{

    private $post_type = 'qrr_restaurant';

    public function __construct()
    {

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'), 100);

        add_action('add_meta_boxes', array($this,'add_meta_box'), 100);

        // Creating a conflict with Avada if I remove all other metaboxes, so by default is false now
        add_action('do_meta_boxes', array($this,'modify_meta_boxes'), 100);
        add_action('save_post', array($this, 'save_metabox'));
        //add_action( 'edit_form_top', array( $this, 'form_top' ), 100 );
        //add_filter( 'get_user_option_screen_layout_'.$this->post_type, array( $this, 'screen_layout_post' ) );

        add_action('admin_init', array($this,'check_max_posts'));
        add_action('admin_notices', array($this, 'check_max_posts_notices'));
    }

    function check_max_posts_notices()
    {
        if (isset($_GET['max_restaurants'])) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Use our Capacity add-on to create more restaurants.', 'qrr'); ?> <a href="<?php echo esc_url(QRR()->get_addons_link()); ?>" target="_blank">Add-Ons</a></p>
            </div>
            <?php
        }
    }


    public function check_max_posts()
    {

        global $pagenow, $typenow;

        if (QRR_Active('capacity')) { return;
        }

        if ($pagenow == 'post-new.php' && $typenow == 'qrr_restaurant') {

            global $wpdb;
            $sql = "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type='qrr_restaurant' AND post_status != 'auto-draft'";
            $num = $wpdb->get_var($sql);

            if ($num >= 1 ) {
                $link = admin_url().'edit.php?post_type=qrr_restaurant&max_restaurants='.$num;
                wp_redirect($link);
                die();
            }
        }

    }


    public function admin_scripts( $hook )
    {

        global $wp_version, $post, $pagenow, $typenow;

        $js_dir = QRR_PLUGIN_URL . 'assets/js/';
        $css_dir = QRR_PLUGIN_URL . 'assets/css/';
        $lib_dir = QRR_PLUGIN_URL . 'assets/libs/';

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

        // Fontawesome
        wp_enqueue_style('fontawesome', $css_dir.'font-awesome'.$suffix.'.css', array(), '4.3.0');

        // QRR admin settings style
        if (qrr_is_admin_post_type_page($hook, $this->post_type)) {
            // https://github.com/stuttter/wp-datepicker-styling
            wp_enqueue_style('ionrangeslider', $lib_dir.'ionrangeslider/css/ion.rangeSlider.css', array(), '2.2.0');
            wp_enqueue_style('ionrangeslider-theme', $lib_dir.'ionrangeslider/css/ion.rangeSlider.skinFlat.css', array(), '2.2.0');
            wp_enqueue_style('datepicker', $css_dir.'datepicker.css', array(), QRR_VERSION);
            wp_enqueue_style('admin-qrr_restaurant', $css_dir.'admin-qrr_restaurant.css', array(), QRR_VERSION);
        }

        // Tabs for the metabox
        wp_register_script('admin-qrr-tabs', $js_dir.'admin-qrr-tabs.js', array('jquery'), QRR_VERSION, true);

        // Vue
        wp_register_script('vuejs', $lib_dir.'vue/vue'.$suffix.'.js', array(), '2.4.2', true);
        wp_register_script('vue-components', $js_dir.'vue-components'.'.js', array('vuejs'), QRR_VERSION, true);

        wp_register_script('ionrangeslider', $lib_dir.'ionrangeslider/js/ion.rangeSlider'.$suffix.'.js', array(), '2.2.0', true);
        wp_register_script('sortable', $lib_dir.'vue/Sortable'.$suffix.'.js', array(), '1.6.0', true);
        wp_register_script('vuedraggable', $lib_dir.'vue/vuedraggable'.$suffix.'.js', array('vuejs','sortable'), '1.0.0', true);

        // Booking metaboxes
        wp_register_script('admin-booking-schedule', $js_dir.'admin-booking-schedule.js', array('jquery','vue-components','vuedraggable','jquery-ui-datepicker','ionrangeslider'), QRR_VERSION, true);
        wp_register_script('admin-booking-fields-main', $js_dir.'admin-booking-fields-main.js', array('admin-booking-schedule'), QRR_VERSION, true);


        // Localize script
        wp_localize_script(
            'admin-booking-schedule', 'qrr_options', array(
            'time_intervals' => qrr_get_time_intervals(),
            'duration_options' => qrr_get_duration_options(),
            'notification_options' => qrr_get_notification_options()
            )
        );
        //wp_register_script( 'admin-booking-capacity', $js_dir.'admin-booking-capacity'.'.js', array('jquery','vue-components','vuedraggable','jquery-ui-datepicker','ionrangeslider'), QRR_VERSION, true );

    }

    public function add_meta_box()
    {

        // Add new metaboxes
        //add_meta_box('qrr_personal', 'QRR PERSOANL', array($this,'prueba'), 'qrr_restaurant','advanced','hight');
        add_meta_box('qrr_global', __('Bookings', 'qrr'), array($this, 'metabox_global'), $this->post_type, 'normal', 'high');
        //add_meta_box( 'qrr_general', __( 'Booking General', 'qrr' ), array($this, 'metabox_general'), $this->post_type, 'normal', 'high' );
        //add_meta_box( 'qrr_schedules', __( 'Booking Schedule', 'qrr' ), array($this, 'metabox_schedule'), $this->post_type, 'normal', 'high' );
        //add_meta_box( 'qrr_capacity', __( 'Booking Capacity', 'qrr' ), array($this, 'metabox_capacity'), $this->post_type, 'normal', 'high' );
        //add_meta_box( 'qrr_form', __( 'Booking Form', 'qrr' ), array($this, 'metabox_form'), $this->post_type, 'normal', 'high' );
        //add_meta_box( 'qrr_notifications', __( 'Booking Notifications', 'qrr' ), array($this, 'metabox_notifications'), $this->post_type, 'normal', 'high' );

    }


    public function modify_meta_boxes( $post_type )
    {

        global $wp_meta_boxes;

        if ($post_type == $this->post_type ) {

            // Remove save publish
            //remove_meta_box( 'submitdiv', null, 'side' );

            // Remove all metaboxes except qrr_global
            $remove_all_other_metaboxes = apply_filters('qrr_restaurant_remove_all_other_metaboxes', false);

            $allowed = array('postimagediv','qrr_global','submitdiv');
            if ($remove_all_other_metaboxes) {
                $filters = array('side','normal');
                foreach( $filters as $filter ) {
                    foreach($wp_meta_boxes['qrr_restaurant'][$filter] as $key => $data ) {
                        foreach($data as $key2 => $data2) {
                            if (!in_array($key2, $allowed)) {
                                unset($wp_meta_boxes['qrr_restaurant'][$filter][$key][$key2]);
                            }
                        }
                    }
                }
            }


        }
    }



    public function form_top( $post )
    {


        if ($post->post_type == 'qrr_restaurant' ) {

            $status = $post->post_status;
            $post_type = $post->post_type;
            $post_type_object = get_post_type_object($post_type);
            $can_publish = current_user_can($post_type_object->cap->publish_posts);

            if (0 == $post->ID || $status == 'auto-draft' ) {
                ?>
                <div class="clearfix qrr-save-box">
                    <div id="status_updates" class="sticky_save">
                        <div id="publishing-action">
                            <span class="spinner"></span>
                            <input name="original_publish" type="hidden" id="original_publish" value="Publish">
                            <input type="submit" name="publish" id="publish" class="button button-primary button-large" value="Publish">
                        </div>
                    </div>
                </div>
                <?php
            }
            else {
                ?>
            <div class="clearfix qrr-save-box">
                <div id="status_updates" class="sticky_save">
                    <div id="publishing-action">
                        <span class="spinner"></span>
                        <input name="original_publish" type="hidden" id="original_publish" value="Update">
                        <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
                    </div>
                </div>
            </div>

                <?php
            }

        }

    }

    public function screen_layout_post()
    {
        return 1;
    }

    public function metabox_global( $post)
    {

        $selected_tab = get_post_meta($post->ID, 'qrr_selected_tab', true);
        if (empty($selected_tab)) {
            $selected_tab = '#qrr-metabox-general';
        }

        ?>
        <input type="hidden" name="qrr_selected_tab" value="<?php echo esc_attr($selected_tab); ?>">
        <div id="qrr-metabox-global">
            <div class="nav-tab-wrapper">
                <a href="#qrr-metabox-general" class="nav-tab <?php echo $selected_tab == '#qrr-metabox-general' ? 'nav-tab-active' : ''; ?>"><?php _ex('General', 'Tabs', 'qrr'); ?></a>
                <a href="#qrr-metabox-schedule" class="nav-tab <?php echo $selected_tab == '#qrr-metabox-schedule' ? 'nav-tab-active' : ''; ?>"><?php _ex('Schedules', 'Tabs', 'qrr'); ?></a>
                <a href="#qrr-metabox-capacity" class="nav-tab <?php echo $selected_tab == '#qrr-metabox-capacity' ? 'nav-tab-active' : ''; ?>"><?php _ex('Capacity', 'Tabs', 'qrr'); ?></a>
                <a href="#qrr-metabox-forms" class="nav-tab <?php echo $selected_tab == '#qrr-metabox-forms' ? 'nav-tab-active' : ''; ?>"><?php _ex('Form', 'Tabs', 'qrr'); ?></a>
                <a href="#qrr-metabox-notifications" class="nav-tab <?php echo $selected_tab == '#qrr-metabox-notifications' ? 'nav-tab-active' : ''; ?>"><?php _ex('Email', 'Tabs', 'qrr'); ?></a>
            </div>
            <div class="tab-contents">
                <div id="qrr-metabox-general" class="group <?php echo $selected_tab == '#qrr-metabox-general' ? 'active' : ''; ?>">
                    <?php $this->metabox_general($post); ?>
                </div>
                <div id="qrr-metabox-schedule" class="group <?php echo $selected_tab == '#qrr-metabox-schedule' ? 'active' : ''; ?>">
                    <?php $this->metabox_schedule($post); ?>
                </div>
                <div id="qrr-metabox-capacity" class="group <?php echo $selected_tab == '#qrr-metabox-capacity' ? 'active' : ''; ?>">
                    <?php $this->metabox_capacity($post); ?>
                </div>
                <div id="qrr-metabox-forms" class="group <?php echo $selected_tab == '#qrr-metabox-forms' ? 'active' : ''; ?>">
                    <?php $this->metabox_form($post); ?>
                </div>
                <div id="qrr-metabox-notifications" class="group <?php echo $selected_tab == '#qrr-metabox-notifications' ? 'active' : ''; ?>">
                    <?php $this->metabox_notifications($post); ?>
                </div>
            </div>
        </div>
        <?php
        wp_enqueue_script('admin-qrr-tabs');
    }

    public function metabox_general( $post )
    {

        wp_nonce_field('qrr_restaurant_metabox_nonce', 'qrr_restaurant_metabox_nonce');

        //$value = get_post_meta($post->ID, 'qrr_booking_general', true);
        //echo '<input id="qrr_booking_general" name="qrr_booking_general" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
        include QRR_PLUGIN_DIR . 'includes/admin/templates/template-general.php';
    }

    public function metabox_schedule( $post)
    {

        $value = get_post_meta($post->ID, 'qrr_booking_schedule', true);
        if (!$value) {
            $value = '[{"active":true,"opened":true,"visible":true,"days":[{"active":true},{"active":true},{"active":true},{"active":true},{"active":true},{"active":true},{"active":true}],"dates":{"from":"","to":""},"alldates":true,"time_type":"interval","time":{"from":"08:00","to":"22:00"},"time_interval":15,"time_specific":[],"alltime":false,"capacity":"capacity_unlimited","capacity_seats":{"total":0,"duration":0},"capacity_tables":[],"notifications":{"confirmation_type":"manual","automatic_party":false,"automatic_party_number":8,"automatic_seats":false,"automatic_seats_number":20},"test_applied":false,"late_bookings_overwrite":"no","late_bookings_mode":"days_and_time","late_booking_hours":2,"late_bookings_days":1,"late_bookings_time":"72000"}]';
        }

        echo '<input data-restid="'.$post->ID.'" id="qrr_booking_schedule" name="qrr_booking_schedule" type="hidden" value="' . esc_attr(stripslashes($value)) . '"/>';
        include QRR_PLUGIN_DIR . 'includes/admin/templates/template-schedule.php';
    }

    public function metabox_capacity( $post )
    {

        if (!QRR_Active('capacity') ) {
            echo '<p>'._x('Booking Capacity is set to UNLIMITED.', 'qrr').'</p>';
            include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
        }

        do_action('qrr_metabox_booking_capacity', $post);
    }

    public function metabox_form( $post )
    {

        $value = get_post_meta($post->ID, 'qrr_booking_fields', true);
        if (!$value) {
            $value = '[{"type":"header","id":"header-booking","title":"Book a table","required":true,"canremove":false,"canmove":false,"error":"","description":""},{"type":"date","id":"qrr-date","title":"Date","required":true,"canremove":false,"canmove":false,"error":"","description":""},{"type":"party","id":"qrr-party","title":"Party","required":true,"canremove":false,"canmove":false,"error":"","description":""},{"type":"time","id":"qrr-time","title":"Time","required":true,"canremove":false,"canmove":false,"error":"","description":""},{"type":"header","id":"header-contact","title":"Contact","required":true,"canremove":true,"canmove":false,"error":"","description":""},{"type":"name","id":"qrr-name","title":"Name","required":true,"canremove":false,"canmove":true,"error":"","description":""},{"type":"email","id":"qrr-email","title":"Email","required":true,"canremove":false,"canmove":true,"error":"","description":""},{"type":"phone","id":"qrr-phone","title":"Phone","required":false,"canremove":true,"canmove":true,"error":"","description":""},{"type":"textarea","id":"qrr-message","title":"Message","required":false,"canremove":true,"canmove":true,"error":"","description":""}]';
        }
        echo '<input id="qrr_booking_fields" type="hidden" name="qrr_booking_fields" value="' . esc_attr(stripslashes($value)) . '"/>';

        if (!QRR_Active('fields') ) {
            include QRR_PLUGIN_DIR.'includes/admin/templates/template-fields.php';
            include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-fields.php';
        }

        do_action('qrr_metabox_booking_fields', $post);
    }

    public function metabox_notifications( $post )
    {

        //$value = get_post_meta($post->ID, 'qrr_booking_notifications', true);
        //echo '<input id="qrr_booking_notifications" name="qrr_booking_notifications" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
        include QRR_PLUGIN_DIR . 'includes/admin/templates/template-notifications.php';
    }



    public function save_metabox($post_id)
    {

        // Check if our nonce is set.
        if (! isset($_POST['qrr_restaurant_metabox_nonce']) ) {
            return;
        }

        // Verify that the nonce is valid.
        if (! wp_verify_nonce($_POST['qrr_restaurant_metabox_nonce'], 'qrr_restaurant_metabox_nonce') ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && $this->post_type == sanitize_text_field($_POST['post_type'])) {
            if (! current_user_can('edit_post', $post_id) ) {
                return;
            }
        } else {
            if (! current_user_can('edit_post', $post_id) ) {
                return;
            }
        }

        $keys = array(
            'qrr_selected_tab',
            'qrr_booking_general',
            'qrr_booking_schedule',
            'qrr_booking_fields',

            'qrr_email_logo',
            'qrr_email_restaurant_page',
            'qrr_email_replay_to_name',
            'qrr_email_replay_to_email',
            'qrr_email_admin_email_send',
            'qrr_email_admin_email',

            'qrr_email_admin_subject',
            'qrr_email_admin_content',

            'qrr_email_admin_confirmed_subject',
            'qrr_email_admin_confirmed_content',


            'qrr_email_pending_subject',
            'qrr_email_pending_content',

            'qrr_email_confirmed_subject',
            'qrr_email_confirmed_content',

            'qrr_email_rejected_subject',
            'qrr_email_rejected_content',

            'qrr_email_update_subject',
        );

        $keys = apply_filters('qrr_restaurant_save_metadada_keys', $keys);

        foreach( $keys as $key ) {
            if (isset($_POST[$key])) {

                if (preg_match('#qrr_email#', $key)){
	                //update_post_meta($post_id, $key, sanitize_textarea_field($_POST[$key]));
                    update_post_meta($post_id, $key, wp_filter_post_kses($_POST[$key]));
                }
                else {
	                update_post_meta($post_id, $key, qrr_sanitize_text_or_array_field($_POST[$key]));
                }

            }
        }


    }

}

new QRR_Restaurant_Edit();
