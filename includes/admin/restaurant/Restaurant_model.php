<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Restaurant_Model
{

    private $restaurant_id;
    private $options_general = '';

    public function __construct( $restaurant_id )
    {

        $this->restaurant_id = $restaurant_id;
        $this->options_general = get_post_meta($restaurant_id, 'qrr_booking_general', true);
    }


    // Messages after form submitted
    //-----------------------------------------

    public function get_message_hours_not_available()
    {
        $message = isset($this->options_general['message_not_available_hours']) ? $this->options_general['message_not_available_hours'] : __('Sorry, no hours available for date {date} and party {party}.', 'qrr');
        return $message;
    }

    public function get_message_not_available()
    {
        $message = isset($this->options_general['message_not_available']) ? $this->options_general['message_not_available'] : '';
        return apply_filters(
            'qrr_restaurant_message_not_available', array(
            'message'   => $message,
            'redirect'  => 'same',
            'timeout' =>  1000 * intval(isset($this->options_general['redirect_page_seconds']) ? $this->options_general['redirect_page_seconds'] : 5)
            ), $this->restaurant_id
        );
    }

    public function get_message_pending()
    {
        $message = isset($this->options_general['message_pending']) ? $this->options_general['message_pending'] : '';
        $page_id = isset($this->options_general['redirect_page_pending']) ? $this->options_general['redirect_page_pending'] : '';

        $link = '';
        if (!empty($page_id) ) {
            $link = get_the_permalink(intval($page_id));
        }
        return apply_filters(
            'qrr_restaurant_message_pending', array(
            'message'   => $message,
            'redirect'  => $link,
            'timeout' =>  1000 * intval(isset($this->options_general['redirect_page_seconds']) ? $this->options_general['redirect_page_seconds'] : 5)
            ), $this->restaurant_id
        );
    }

    public function get_message_confirmed()
    {
        $message = isset($this->options_general['message_confirmed']) ? $this->options_general['message_confirmed'] : '';
        $page_id = isset($this->options_general['redirect_page_confirmed']) ? $this->options_general['redirect_page_confirmed'] : '';

        $link = '';
        if (!empty($page_id) ) {
            $link = get_permalink(intval($page_id));
        }
        return apply_filters(
            'qrr_restaurant_message_confirmed', array(
            'message'   => $message,
            'redirect'  => $link,
            'timeout' => 1000 * intval(isset($this->options_general['redirect_page_seconds']) ? $this->options_general['redirect_page_seconds'] : 5)
            ), $this->restaurant_id
        );
    }

    public function get_from_email()
    {
        return get_post_meta($this->restaurant_id, 'qrr_email_replay_to_email', true);
    }

    public function get_from_name()
    {
        return get_post_meta($this->restaurant_id, 'qrr_email_replay_to_name', true);
    }

    public function get_admin_email()
    {
        $email = get_post_meta($this->restaurant_id, 'qrr_email_admin_email', true);
        if (!$email) {
            $email = get_option('admin_email');
        }
        return $email;
    }



    public function get_email_subject_pending()
    {
        $subject = get_post_meta($this->restaurant_id, 'qrr_email_pending_subject', true);
        if (empty($subject)) {
            $subject = $this->get_default_email_customer_subject('pending');
        }
        return $subject;
    }

    public function get_email_subject_confirmed()
    {
        $subject = get_post_meta($this->restaurant_id, 'qrr_email_confirmed_subject', true);
        if (empty($subject)) {
            $subject = $this->get_default_email_customer_subject('qrr-confirmed');
        }
        return $subject;
    }

    public function get_email_subject_rejected()
    {
        $subject = get_post_meta($this->restaurant_id, 'qrr_email_rejected_subject', true);
        if (empty($subject)) {
            $subject = $this->get_default_email_customer_subject('qrr-rejected');
        }
        return $subject;
    }

    public function get_email_subject_update()
    {
        return get_post_meta($this->restaurant_id, 'qrr_email_update_subject', true);
    }

    public function get_email_content_pending()
    {
        $content = get_post_meta($this->restaurant_id, 'qrr_email_pending_content', true);
        if (empty($content)) {
            $content = $this->get_default_email_customer_content('pending');
        }
        return wpautop($content);
    }

    public function get_email_content_confirmed()
    {
        $content = get_post_meta($this->restaurant_id, 'qrr_email_confirmed_content', true);
        if (empty($content)) {
            $content = $this->get_default_email_customer_content('qrr-confirmed');
        }
        return wpautop($content);
    }

    public function get_email_content_rejected()
    {
        $content = get_post_meta($this->restaurant_id, 'qrr_email_rejected_content', true);
        if (empty($content)) {
            $content = $this->get_default_email_customer_content('qrr-rejected');
        }
        return wpautop($content);
    }

    public function get_email_subject_admin()
    {
        $subject = get_post_meta($this->restaurant_id, 'qrr_email_admin_subject', true);
        if (empty($subject)) {
            $subject = $this->get_default_email_admin_subject('pending');
        }
        return $subject;
    }

    public function get_email_subject_admin_confirmed()
    {
        $subject = get_post_meta($this->restaurant_id, 'qrr_email_admin_confirmed_subject', true);
        if (empty($subject)) {
            $subject = $this->get_default_email_admin_subject('qrr-confirmed');
        }
        return $subject;
    }

    public function get_email_content_admin()
    {
        $content = get_post_meta($this->restaurant_id, 'qrr_email_admin_content', true);
        if (empty($content)) {
            $content = $this->get_default_email_admin_content('pending');
        }
        return wpautop($content);
    }

    public function get_email_content_admin_confirmed()
    {
        $content = get_post_meta($this->restaurant_id, 'qrr_email_admin_confirmed_content', true);
        if (empty($content)) {
            $content = $this->get_default_email_admin_content('qrr-confirmed');
        }
        return wpautop($content);
    }



    // Default email content
    //-----------------------------------------------------------

    public function get_default_email_admin_subject( $status )
    {

        $subject = '';
        switch($status){
        case 'pending':
            $subject = __('You have received a new booking request', 'qrr');
            break;
        case 'qrr-confirmed':
            $subject = __('You have received a new booking already confirmed', 'qrr');
            break;
        default:
            return '';
                break;
        }

        return apply_filters('qrr_email_default_admin_subject', $subject, $status, $this->restaurant_id);
    }

    public function get_default_email_admin_content( $status )
    {

        $content = '';
        switch($status){
        case 'pending':
            ob_start();
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-email-admin.php';
            $content = ob_get_clean();
            break;
        case 'qrr-confirmed':
            ob_start();
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-email-admin_confirmed.php';
            $content = ob_get_clean();
            break;
        default:
            return '';
                break;
        }


        return apply_filters('qrr_email_default_admin_content', $content, $status, $this->restaurant_id);
    }


    public function get_default_email_customer_subject( $status )
    {

        $subject = '';
        switch($status){
        case 'pending':
            $subject = __('Your booking at {site_name} is pending', 'qrr');
            break;
        case 'qrr-confirmed':
            $subject = __('Your booking at {site_name} has been confirmed', 'qrr');
            break;
        case 'qrr-rejected':
            $subject = __('Your booking at {site_name} has been rejected', 'qrr');
            break;
        default:
            return '';
                break;
        }

        $subject = str_replace('{site_name}', get_bloginfo('name'), $subject);

        return apply_filters('qrr_email_default_customer_subject', $subject, $status, $this->restaurant_id);

    }


    public function get_default_email_customer_content( $status )
    {

        $content = '';
        switch($status){
        case 'pending':
            ob_start();
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-email-pending.php';
            $content = ob_get_clean();
            break;
        case 'qrr-confirmed':
            ob_start();
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-email-confirmed.php';
            $content = ob_get_clean();
            break;
        case 'qrr-rejected':
            ob_start();
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-email-rejected.php';
            $content = ob_get_clean();
            break;
        default:
            return '';
                break;
        }

        return apply_filters('qrr_email_default_customer_content', $content, $status, $this->restaurant_id);
    }


    // Data based on the schedules
    //--------------------------------------------

    public function get_schedules()
    {
        $schedules = get_post_meta($this->restaurant_id, 'qrr_booking_schedule', true);
        if (empty($schedules)) { return false;
        }

        return json_decode($schedules, true);
    }

    public function get_schedule_index( $index )
    {
        $schedules = $this->get_schedules();
        if (isset($schedules[$index])) { return $schedules[$index];
        }
        return false;
    }

    public function get_global_capacity()
    {
        $capacity = get_post_meta($this->restaurant_id, 'qrr_booking_capacity', true);
        if (empty($capacity)) { return false;
        }
        $data = json_decode($capacity, true);
        return $data['capacity'];
    }

    public function get_capacity_and_duration($schedule_index)
    {

        $schedule = $this->get_schedule_index($schedule_index);

        if($schedule['capacity'] == 'capacity_unlimited' ) {
            return array(
                'capacity' => 'capacity_unlimited'
            );
        }

        else if ($schedule['capacity'] == 'capacity_seats_global') {
            $capacity = $this->get_global_capacity();
            return array(
                'capacity'  => 'capacity_seats_global',
                'seats'     => $capacity['total']['total_seats'],
                'duration'  => $capacity['total']['duration']
            );
        }

        else if ($schedule['capacity'] == 'capacity_tables_global') {
            $capacity = $this->get_global_capacity();
            return array(
                'capacity'  => 'capacity_tables_global',
                'tables'     => $capacity['tablesNum']
            );
        }

        else if ($schedule['capacity'] == 'capacity_seats_specific') {
            return array(
                'capacity'  => 'capacity_seats_specific',
                'seats'     => $schedule['capacity_seats']['total'],
                'duration'  => $schedule['capacity_seats']['duration']
            );
        }

        else if ($schedule['capacity'] == 'capacity_tables_specific') {
            return array(
                'capacity'  => 'capacity_tables_specific',
                'tables'     => $schedule['capacity_tables']
            );
        }

        return false;

    }

    public function get_schedule_name($schedule_index)
    {
        $schedule = $this->get_schedule_index($schedule_index);
        if (!$schedule) { return '-';
        }

        $name = $schedule['name'];
        if (empty($name)) {
            return '-';
        }
        return $name;
    }

    public function get_final_hour_for_schedule_index($schedule_index)
    {
        $schedule = $this->get_schedule_index($schedule_index);

        if (!$schedule) { return '24:00';
        }

        if ($schedule['alltime']) {
            return '24:00';
        }

        $final_hour = '24:00';

        if ($schedule['time_type'] == 'interval') {

            if (!empty($schedule['time']['to'])) {
                $final_hour = $schedule['time']['to'];
            }

        } else if ($schedule['time_type'] == 'specific') {

            $intervals = $schedule['time_specific'];
            if(!empty($intervals)) {
                $final_hour = $intervals[ count($intervals)-1 ];
            }
        }

        return $final_hour;
    }

    public function get_late_booking()
    {
        $late = isset($this->options_general['late']) ? $this->options_general['late'] : '1h';
        return $late;
    }

    public function get_hour_format()
    {
        $format = isset($this->options_general['hour_format']) ? $this->options_general['hour_format'] : '24h';
        return $format;
    }

}
