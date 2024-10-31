<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Booking_Admin
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        add_filter('manage_qrr_booking_posts_columns', array( $this,'filter_custom_columns'));
        add_action('manage_qrr_booking_posts_custom_column', array($this,'display_custom_columns'), 10, 2);

        // Row actions
        add_filter('post_row_actions', array($this,'modify_list_row_actions'), 20, 2);

        add_filter('bulk_actions-edit-qrr_booking', array($this, 'modify_list_bulk_actions'), 20, 1);

        // Bulk actions
        add_action('admin_footer-edit.php', array( $this, 'bulk_admin' ));
        add_action('load-edit.php', array($this, 'email_action'), 15);
        add_action('load-edit.php', array($this, 'bulk_action'), 20);
        add_action('admin_notices', array($this, 'bulk_notices'));

        // Modify order based on booking date
        add_filter('parse_query', array($this,'parse_query'), 100, 1);

        // Remove filter dates
        add_filter('months_dropdown_results', array($this, 'months_dropdown_results'), 100, 2);

        // Add new filters
        add_action('restrict_manage_posts', array($this,'table_filters'), 100, 2);

        // Redirect create new booking to custom url
        add_action('admin_init', array( $this, 'redirect_new_add_booking_url' ));

        // Ajax actions
        //add_action( 'wp_ajax_pending_email', array( $this, 'ajax_action_pending_email' ) );
        //add_action( 'wp_ajax_confirm_email', array( $this, 'ajax_action_confirm_email' ) );
        //add_action( 'wp_ajax_reject_email', array( $this, 'ajax_action_reject_email' ) );
        //add_action( 'wp_ajax_cancel', array( $this, 'ajax_action_cancel' ) );

    }

    public function admin_scripts($hook)
    {

        global $wp_version, $post, $pagenow, $typenow;

        $js_dir = QRR_PLUGIN_URL . 'assets/js/';
        $css_dir = QRR_PLUGIN_URL . 'assets/css/';
        $lib_dir = QRR_PLUGIN_URL . 'assets/libs/';

        if ($hook == 'edit.php' && $typenow == 'qrr_booking') {
            wp_enqueue_style('datepicker', $css_dir.'datepicker.css', array(), QRR_VERSION);
            wp_enqueue_style('tipTip', $css_dir.'tipTip.css', array(), '1.2');
            wp_enqueue_style('admin-table-qrr_booking', $css_dir.'admin-table-qrr_booking.css', array(), QRR_VERSION);
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-tip', $js_dir.'jquery.tipTip.min.js', array('jquery'), '1.3', true);
            wp_enqueue_script('admin-table-qrr_booking', $js_dir.'admin-table-qrr_booking.js', array('jquery','jquery-ui-datepicker','jquery-tip'), QRR_VERSION, true);
        }
    }

    public function modify_list_row_actions($actions, $post )
    {

        if ($post->post_type == "qrr_booking" ) {
            unset($actions['inline hide-if-no-js']);
        }
        return $actions;
    }

    function modify_list_bulk_actions( $actions )
    {
        unset($actions['edit']);
        return $actions;
    }

    function is_trash_admin()
    {
        return (isset($_GET['post_status']) && sanitize_text_field($_GET['post_status']) == 'trash');
    }

    // Redirect own create booking url
    //--------------------------------------

    public function redirect_new_add_booking_url()
    {
        global $pagenow;

        if ('post-new.php' == $pagenow && isset($_GET['post_type']) && 'qrr_booking' == sanitize_text_field($_GET['post_type']) ) {
            wp_redirect(admin_url('edit.php?post_type=qrr_booking&page=qrr_create_booking'), '301');
        }
    }

    // Custom columns
    //--------------------------------------

    public function filter_custom_columns($columns)
    {

        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        //$new_columns['title'] = $columns['title'];
        $new_columns['id'] = _x('ID', 'Table bookings', 'qrr');
        $new_columns['qrr_restaurant'] = _x('Restaurant', 'Table bookings', 'qrr');
        $new_columns['qrr_status'] = _x('Status', 'Table bookings', 'qrr');
        $new_columns['qrr_date'] = _x('Date', 'Table bookings', 'qrr');
        $new_columns['qrr_duration'] = _x('Duration', 'Table bookings', 'qrr');
        $new_columns['qrr_party'] = _x('Party', 'Table bookings', 'qrr');
        if (QRR_Active('capacity')) {
	        $new_columns['qrr_tables'] = _x('Tables', 'Table bookings', 'qrr');
            $new_columns['qrr_table_name'] = _x('Table name', 'Table bookings', 'qrr');
        }
        $new_columns['qrr_name'] = _x('Name', 'Table bookings', 'qrr');
        $new_columns['qrr_name'] = _x('Name', 'Table bookings', 'qrr');
        $new_columns['qrr_email'] = _x('Email', 'Table bookings', 'qrr');
        $new_columns['qrr_phone'] = _x('Phone', 'Table bookings', 'qrr');

        if (!$this->is_trash_admin() ) {
            $new_columns['qrr_actions'] = _x('Actions', 'Table bookings', 'qrr');
        }

        return $new_columns;
    }

    public function display_custom_columns( $column, $post_id )
    {
        global $post;

        $model = new QRR_Booking_Model($post_id);

        switch ( $column ) {

        case 'id':
            //$link = get_edit_post_link($post_id);
            //echo '<a href="'.$link.'">'.$post_id.'</a>';
            echo esc_html($post_id);
            break;

        case 'qrr_restaurant':
            $rest_id = $model->get_restaurant_id();
            echo get_the_title($rest_id);

            break;

        case 'qrr_date':
            $date_str = $model->get_date();
            if (!empty($date_str)) {
                $date_obj = new DateTime($date_str);
                $format = apply_filters('qrr_admin_booking_column_date',  'F j, Y g:i a');
                echo esc_html($date_obj->format($format));
            } else {
                echo '-';
            }
            break;

        case 'qrr_duration':
            echo esc_html($model->get_duration()).__('(min)', 'qrr');
            break;

        case 'qrr_party':
            echo esc_html($model->get_party());
            break;

        case 'qrr_tables':
            echo esc_html($model->get_tables());
            break;

        case 'qrr_table_name':
            echo esc_html($model->get_table_name());
            break;

        case 'qrr_name':
            echo esc_html($post->post_title);
            break;

        case 'qrr_email':
            $user_email = $model->get_user_email();
            echo $user_email ? esc_html($user_email) : '-';
            break;

        case 'qrr_phone':
            echo esc_html($model->get_phone());
            break;

        case 'qrr_status':
            $list = QRR_Booking_Edit::get_list_status();
            $found = false;
            foreach($list as $key => $data){
                if ($post->post_status == $key ) {
                    $found = true;
                    echo wp_kses_post($data['label']);
                }
            }
            if (!$found) {
                echo esc_html($post->post_status);
            }
            break;

        case 'qrr_actions':

            $action_link = admin_url('edit.php?post_type=qrr_booking');
            $action_link = add_query_arg(array('booking_id' => $post->ID), $action_link);

            $actions = array();
            $actions['view'] = array(
                'url'       => admin_url('post.php?post=' . $post->ID . '&action=edit'),
                'name'         => __('View', 'qrr'),
                'name_tip'         => __('View', 'qrr'),
                'action'     => "view",
                'class'     => 'fa fa-eye'
            );
            $actions['pending'] = array(
                //'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?qrr_action=pending_email&booking_id=' . $post->ID ), 'booking-pending' ),
                //'url' => wp_nonce_url( add_query_arg( array('action' => 'pending_email'), $action_link ), 'booking-pending' ),
                'url' => add_query_arg(array('qrr_action' => 'pending_email'), $action_link),
                'name'         => _x('Pending', 'Actions', 'qrr'),
                'name_tip'         => _x('Pending (send email)', 'Actions', 'qrr'),
                'action'     => "pending",
                'class'     => 'fa fa-envelope'
            );
            $actions['confirm'] = array(
                //'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=confirm_email&booking_id=' . $post->ID ), 'booking-confirm' ),
                //'url' => wp_nonce_url( add_query_arg( array('action' => 'confirm_email'), $action_link ), 'booking-confirm' ),
                'url' => add_query_arg(array('qrr_action' => 'confirm_email'), $action_link),
                'name'         => _x('Confirm', 'Actions', 'qrr'),
                'name_tip'         => _x('Confirm (send email)', 'Actions', 'qrr'),
                'action'     => "confirm",
                'class'     => 'fa fa-envelope'
            );
            $actions['reject'] = array(
                //'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?action=reject_email&booking_id=' . $post->ID ), 'booking-reject' ),
                //'url' => wp_nonce_url( add_query_arg( array('action' => 'reject_email'), $action_link ),  'booking-reject' ),
                'url' => add_query_arg(array('qrr_action' => 'reject_email'), $action_link),
                'name'         => _x('Reject', 'Actions', 'qrr'),
                'name_tip'         => _x('Reject (send email)', 'Actions', 'qrr'),
                'action'     => "reject",
                'class'     => 'fa fa-envelope'
            );
            $actions['cancel'] = array(
                //'url'         => wp_nonce_url( admin_url( 'admin-ajax.php?qrr_action=cancel&booking_id=' . $post->ID ), 'booking-cancel' ),
                //'url' => wp_nonce_url( add_query_arg( array('action' => 'cancel'), $action_link ), 'booking-cancel' ),
                'url' => add_query_arg(array('qrr_action' => 'cancel_no_email'), $action_link),
                'name'         => _x('Cancel', 'Actions', 'qrr'),
                'name_tip'         => _x('Cancel (no email)', 'Actions', 'qrr'),
                'action'     => "cancel",
                'class'     => 'fa fa-times'
            );

            $status = $post->post_status;

            if ($status == 'pending') {
                unset($actions['pending']);
            } else if ($status == 'qrr-confirmed') {
                unset($actions['confirm']);
            } else if ($status == 'qrr-rejected') {
                unset($actions['reject']);
            } else if ($status == 'qrr-cancelled') {
                unset($actions['cancel']);
            } else if ($status == 'trash') {
                $actions = array();
            }

            // Remove all actions if no date available, or no client available
            if (!$this->can_have_actions($model, $post)) {
                $actions = array();
            }

            $actions = apply_filters('qrr_admin_booking_actions', $actions, $post);

            echo '<p>';
            foreach ( $actions as $action ) {
                $action['url'] = add_query_arg(array('_wpnonce' => wp_create_nonce('booking_action')), $action['url']);
                printf('<a class="'.esc_attr($action['class']).' button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr($action['action']), esc_url($action['url']), esc_attr($action['name_tip']), esc_attr($action['name']));
            }
            echo '</p>';

            break;
        }

        return $column;
    }

    public function can_have_actions($model, $post)
    {

        $date = $model->get_date();
        if (empty($date)) { return false;
        }

        $user_email = $model->get_user_email();
        if (empty($user_email)) { return false;
        }

        return true;
    }


    // Bulk Actions
    //---------------------------------

    public function bulk_admin()
    {

        if ($this->is_trash_admin()) {
            return;
        }

        global $post_type;
        if ($post_type == 'qrr_booking' ) {
            ?>
            <script>
                jQuery(document).ready(function(){

                    var list = [
                        { slug: 'pending', title: '<?php _e('Pending', 'qrr')?>' },
                        { slug: 'pending_email', title: '<?php _e('Pending (send email)', 'qrr')?>' },
                        { slug: 'confirm', title: '<?php _e('Confirm', 'qrr')?>' },
                        { slug: 'confirm_email', title: '<?php _e('Confirm (send email)', 'qrr')?>' },
                        { slug: 'reject', title: '<?php _e('Reject', 'qrr')?>' },
                        { slug: 'reject_email', title: '<?php _e('Reject (send email)', 'qrr')?>' },
                        //{ slug: 'cancel', title: '<?php _e('Cancel', 'qrr')?>' },
                        { slug: 'cancel_no_email', title: '<?php _e('Cancel', 'qrr')?>' }
                    ];

                    for (var i = 0; i < list.length; i++ ) {
                        jQuery('<option>').val( list[i].slug ).text( list[i].title ).appendTo("select[name='action']");
                        jQuery('<option>').val( list[i].slug ).text( list[i].title ).appendTo("select[name='action2']");
                    }

                });
            </script>
            <?php
        }
    }

    public function email_action()
    {
        global $typenow;
        $post_type = $typenow;

        // check post type
        if ($post_type != 'qrr_booking' ) {
            return;
        }

        if (isset($_GET['qrr_action']) && isset($_GET['booking_id']) && isset($_GET['_wpnonce'])) {

            $nonce = sanitize_text_field($_GET['_wpnonce']);

            if (isset($_GET['type']) && sanitize_text_field($_GET['type']) == 'email'){

                // Nonce does not work well outside the admin dashboard.. think a different solution
                if (!QRR()->settings->get('qrr_booking_skip_admin_email_nonce')){
	                if (!wp_verify_nonce($nonce, 'email_action')) {
		                wp_die(__('Email action not allowed.', 'qrr'));
	                }
                }
            }
            else if (!wp_verify_nonce($nonce, 'booking_action')) {
	            wp_die(__('You have taken too long. Please go back and retry.', 'qrr'));
            }

            $post_id = intval(sanitize_text_field($_GET['booking_id']));

            $action = sanitize_text_field($_GET['qrr_action']);

            // allowed actions
            $allowed_actions = array("pending_email", "confirm_email", "reject_email", "cancel_no_email");
            if(!in_array($action, $allowed_actions)) { return;
            }

            $sendback = admin_url("edit.php?post_type=$post_type");

            switch( $action ) {

            case 'pending_email':
                if (!$this->action_pending_email($post_id) ) { wp_die(__('Error action -Pending email-', 'qrr'));
                }
                $sendback = add_query_arg(array('done_pending_email' => 1, 'ids' => $post_id ), $sendback);
                break;

            case 'confirm_email':
                if (!$this->action_confirm_email($post_id) ) { wp_die(__('Error action -Confirm email-', 'qrr'));
                }
                $sendback = add_query_arg(array('done_confirm_email' => 1, 'ids' => $post_id ), $sendback);
                break;

            case 'reject_email':
                if (!$this->action_reject_email($post_id) ) { wp_die(__('Error action -Reject email-', 'qrr'));
                }
                $sendback = add_query_arg(array('done_reject_email' => 1, 'ids' => $post_id ), $sendback);
                break;

            case 'cancel_no_email':
                if (!$this->action_cancel($post_id) ) { wp_die(__('Error action -Cancel-', 'qrr'));
                }
                $sendback = add_query_arg(array('done_cancel' => 1, 'ids' => $post_id ), $sendback);
                break;

            default:
                return;
            }

            $sendback = remove_query_arg(array('_wpnonce', 'qrr_action', 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback);

            wp_redirect($sendback);
            exit();
        }

        return;
    }

    public function bulk_action()
    {

        global $typenow;
        $post_type = $typenow;

        // check post type
        if ($post_type != 'qrr_booking' ) {
            return;
        }

        // get the action
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();

        // allowed actions
        $allowed_actions = array("pending","pending_email","confirm", "confirm_email", "reject", "reject_email", "cancel", "cancel_no_email");
        if(!in_array($action, $allowed_actions)) { return;
        }

        // security check
        check_admin_referer('bulk-posts');

        // make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
        if(isset($_REQUEST['post'])) {
	        $post_ids = array_map('intval', qrr_sanitize_text_or_array_field($_REQUEST['post']));
        }
        if(empty($post_ids)) {
            return;
        }

        // based on wp-admin/edit.php
        $sendback = remove_query_arg(array('pending','pending_email','confirm','confirm_email','reject','reject_email','cancel','trashed', 'untrashed', 'deleted', 'locked', 'ids'), wp_get_referer());
        if (! $sendback ) {
            $sendback = admin_url("edit.php?post_type=$post_type");
        }

        $pagenum = $wp_list_table->get_pagenum();
        $sendback = add_query_arg('paged', $pagenum, $sendback);

        // perform action
        switch( $action ) {

        case 'pending':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_pending($post_id) ) { wp_die(__('Error action -Pending-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_pending' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'pending_email':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_pending_email($post_id) ) { wp_die(__('Error action -Pending email-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_pending_email' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'confirm':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_confirm($post_id) ) { wp_die(__('Error action -Confirm-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_confirm' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'confirm_email':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_confirm_email($post_id) ) { wp_die(__('Error action -Confirm email-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_confirm_email' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'reject':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_reject($post_id) ) { wp_die(__('Error action -Reject-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_reject' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'reject_email':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_reject_email($post_id) ) { wp_die(__('Error action -Reject email-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_reject_email' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        case 'cancel_no_email':
            $done = 0;
            foreach( $post_ids as $post_id ) {
                if (!$this->action_cancel($post_id) ) { wp_die(__('Error action -Cancel-', 'qrr'));
                }
                $done++;
            }
            $sendback = add_query_arg(array('done_cancel' => $done, 'ids' => join(',', $post_ids) ), $sendback);
            break;

        default:
            return;
        }

        $sendback = str_replace('&amp;', '&', $sendback);
        $sendback = remove_query_arg(array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback);

        wp_redirect($sendback);
        exit();
    }

    public function action_pending( $post_id )
    {
        qrr_change_status($post_id, 'pending');
        return true;
    }

    public function action_pending_email( $post_id )
    {
        qrr_change_status($post_id, 'pending');
        $be = new QRR_Booking_Email($post_id);
        $be->send_customer_email();
        return true;
    }

    public function action_confirm( $post_id )
    {
        qrr_change_status($post_id, 'qrr-confirmed');
        return true;
    }

    public function action_confirm_email( $post_id )
    {
        qrr_change_status($post_id, 'qrr-confirmed');
        $be = new QRR_Booking_Email($post_id);
        $be->send_customer_email();
        return true;
    }

    public function action_reject( $post_id )
    {
        qrr_change_status($post_id, 'qrr-rejected');
        return true;
    }

    public function action_reject_email( $post_id )
    {
        qrr_change_status($post_id, 'qrr-rejected');
        $be = new QRR_Booking_Email($post_id);
        $be->send_customer_email();
        return true;
    }

    public function action_cancel( $post_id )
    {
        /*wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'qrr-cancelled'
        ));*/
        qrr_change_status($post_id, 'qrr-cancelled');
        return true;
    }

    public function bulk_notices()
    {
        global $post_type, $pagenow;

        $list = array(
            'done_pending' => __('%s Booking(s) have changed to Pending.', 'qrr'),
            'done_pending_email' => __('%s Booking(s) have changed to Pending with email sent.', 'qrr'),
            'done_confirm' => __('%s Booking(s) have been Confirmed.', 'qrr'),
            'done_confirm_email' => __('%s Booking(s) have been Confirmed with email sent.', 'qrr'),
            'done_reject' => __('%s Booking(s) have been Rejected.', 'qrr'),
            'done_reject_email' => __('%s Booking(s) have been Rejected with email sent.', 'qrr'),
            'done_cancel' => __('%s Booking(s) have been Cancelled.', 'qrr'),
        );

        if($pagenow == 'edit.php' && $post_type == 'qrr_booking' ) {
            foreach( $list as $key => $title ) {
                if (isset($_REQUEST[$key])) {
                    $request_key = sanitize_text_field($_REQUEST[$key]);
                    $message =  _n($title, $title, $request_key);
                    $message = sprintf($message, $request_key);
                    echo "<div class=\"updated\"><p>{$message}</p></div>";
                }

            }
            $canonical = remove_query_arg(array('ids','done_pending','done_pending_email','done_confirm','done_confirm_email','done_reject','done_reject_email','done_cancel'));
            ?>
            <script>

                window.history.replaceState( null, null, '<?php echo esc_attr($canonical); ?>' + window.location.hash );
                setTimeout(function(){
                    jQuery('input[name=_wp_http_referer]').val(location.pathname + location.search);
                }, 300);

            </script>
            <?php
        }

    }

    // Filters
    //---------------------------------

    public function months_dropdown_results($months, $post_type)
    {
        if ($post_type == 'qrr_booking') {
            return array();
        }
        return $months;
    }

    public function table_filters($post_type, $which)
    {

        if('qrr_booking' !== $post_type) {
            return; //check to make sure this is your cpt
        }

        if ($this->is_trash_admin() ) {
            return;
        }

        $value = isset($_GET['filter_view']) ? sanitize_text_field($_GET['filter_view']) : '';
        echo '<select name="filter_view">';
        //echo '<option value="" '.selected($value,'').'>'._x('Select View','Filters','qrr').'</option>';
        echo '<option value="" '.selected($value, '').'>'._x('TODAY/FUTURE bookings', 'Filters', 'qrr').'</option>';
        echo '<option value="today" '.selected($value, 'today').'>'._x('TODAY bookings', 'Filters', 'qrr').'</option>';
        echo '<option value="future" '.selected($value, 'future').'>'._x('FUTURE bookings', 'Filters', 'qrr').'</option>';
        echo '<option value="all" '.selected($value, 'all').'>'._x('ALL bookings', 'Filters', 'qrr').'</option>';
        echo '</select>';



        echo '<select name="filter_restaurant">';
        echo '<option value="">'._x('Restaurant', 'Filters', 'qrr').'</option>';
        echo '<option value="all">'._x('All', 'Filters', 'qrr').'</option>';

        $list = QRR_Restaurants::get_list_of_restaurants();
        foreach($list as $item){
            $selected = selected($item['value'], isset($_GET['filter_restaurant']) ? sanitize_text_field($_GET['filter_restaurant']) : '');
            echo '<option value="'.esc_attr($item['value']).'" '.$selected.'>'.esc_html($item['title']).'</option>';
        }
        echo '</select>';



        $value = isset($_GET['booking_date']) ? sanitize_text_field($_GET['booking_date']) : '';
        echo '<input name="booking_date" placeholder="select date" value="'.esc_attr($value).'">';
    }

    // Parse Query
    //---------------------------------

    // Order by booking date
    public function parse_query( $query )
    {

        global $pagenow;
        $qv = &$query->query_vars;

        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && sanitize_text_field($_GET['post_type']) == 'qrr_booking' ) {

            // Order by booking date
            $qv['meta_query'] = array(
                'relation' => 'AND',
                array(
                    'relation' => 'AND',
                    'booking_date' => array(
                        'key' => 'qrr_date',
                        'value' => '1970/01/01',
                        'compare' => '!='
                        //'type' => 'NUMERIC'
                    ),
                    'booking_hour' => array(
                        'key' => 'qrr_time',
                        'value' => '00',
                        'compare' => '!='
                    )
                ),
                /*
                 * Debug showing all bookings even when qrr_date does not exists, it's for testing only
                array(
                    'booking_date_exists' => array(
                        'key' => 'qrr_date',
                        'compare' => 'NOT EXISTS'
                    ),
                )
                */

            );

            $qv['orderby'] = array('booking_date'=>'desc', 'booking_hour'=>'asc');
            $qv['order'] = 'DESC';

            // Filter restaurant
            if (isset($_GET['filter_restaurant'])) {
                $filter_restaurant = sanitize_text_field($_GET['filter_restaurant']);

                if ($filter_restaurant != '' && $filter_restaurant != 'all') {
                    $qv['meta_query']['restaurant'] = array(
                        'key' => 'qrr_restaurant_id',
                        'value' => $filter_restaurant,
                        'compare' => '=',
                        'type' => 'NUMERIC'
                    );
                }
            }

            // Filter booking date
            if (isset($_GET['booking_date'])) {
                $booking_date = sanitize_text_field($_GET['booking_date']);

                if ($booking_date != '' && $booking_date != 'all') {
                    $qv['meta_query']['booking_date_filter'] = array(
                        'key' => 'qrr_date',
                        'value' => $booking_date,
                        'compare' => '='
                    );
                }
            }

            // By Default show TODAY/FUTURE bookings
            if (!isset($_GET['booking_date']) || ( isset($_GET['booking_date']) && empty(sanitize_text_field($_GET['booking_date'])) ) ) {



                // Only if it is not trash list
                if (!isset($_GET['post_status']) || ( isset($_GET['post_status']) && sanitize_text_field($_GET['post_status']) !== 'trash') ) {

                    $qv['meta_query']['booking_date_filter'] = array(
                        'key' => 'qrr_date',
                        'value' => current_time('Y-m-d'),
                        'compare' => '>='
                    );
                    if (isset($_GET['filter_view'])) {
                        $filter_view = sanitize_text_field($_GET['filter_view']);

                        if ($filter_view == 'today') {
                            $qv['meta_query']['booking_date_filter']['compare'] = '=';
                        } else if ($filter_view == 'future') {
                            $qv['meta_query']['booking_date_filter']['compare'] = '>';
                        } else if ($filter_view == 'all') {
                            unset($qv['meta_query']['booking_date_filter']);
                        }
                    }

                }

            }

            //echo '<pre style="padding-left: 300px;">QUERY: '; print_r( $qv ); echo '</pre>';
        }

    }


    // Ajax actions
    //-------------------------------------

    public function ajax_action_pending_email()
    {
        $this->process_ajax_booking('booking-pending', 'pending');
    }

    public function ajax_action_confirm_email()
    {
        $this->process_ajax_booking('booking-confirm', 'qrr-confirmed');
    }

    public function ajax_action_reject_email()
    {
        $this->process_ajax_booking('booking-reject', 'qrr-rejected');
    }

    public function ajax_action_cancel()
    {
        $this->process_ajax_booking('booking-cancel', 'qrr-cancelled');
    }

    public function process_ajax_booking( $nonce, $status )
    {

        if (! check_admin_referer($nonce) ) {
            wp_die(__('You have taken too long. Please go back and retry.', 'qrr'));
        }

        $booking_id = isset($_GET['booking_id']) && intval(sanitize_text_field($_GET['booking_id']))  ? intval(sanitize_text_field($_GET['booking_id']))  : '';
        if (! $booking_id ) { die();
        }

        if ($status == 'pending') {
            if (!$this->action_pending_email($booking_id) ) {
                wp_die(__('Error action -Pending email-', 'qrr'));
            }
        } else if ($status == 'qrr-confirmed') {
            if (!$this->action_confirm_email($booking_id) ) {
                wp_die(__('Error action -Confirm email-', 'qrr'));
            }
        } else if ($status == 'qrr-rejected') {
            if (!$this->action_reject_email($booking_id) ) {
                wp_die(__('Error action -Reject email-', 'qrr'));
            }
        } else if ($status == 'qrr-cancelled') {
            if (!$this->action_cancel($booking_id) ) {
                wp_die(__('Error action -Cancel-', 'qrr'));
            }
        }

        wp_safe_redirect(wp_get_referer());
    }

}

new QRR_Booking_Admin();
