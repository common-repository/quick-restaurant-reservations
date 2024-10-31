<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Booking_Model
{

    private $booking_id = 0;

    public function __construct( $booking_id = 0 )
    {
        $this->booking_id = $booking_id;
    }

    public function get_id()
    {
        return $this->booking_id;
    }

    public function get_status()
    {
        return get_post_status($this->booking_id);
    }

    public function get_status_label()
    {
        $list = QRR_Booking_Edit::get_list_status();
        $status = $this->get_status();
        if (isset($list[$status]['label'])) {
            return $list[$status]['label'];
        }
        return $status;
    }

    public function get_user_name()
    {
        return get_the_title($this->booking_id);
    }

    public function get_user_email()
    {
        return get_post_meta($this->booking_id, 'qrr_email', true);
    }

    public function get_party()
    {
        return get_post_meta($this->booking_id, 'qrr_party', true);
    }

    public function get_tables()
    {
        return get_post_meta($this->booking_id, 'qrr_tables', true);
    }

    public function get_table_name()
    {
        return get_post_meta($this->booking_id, 'qrr_table_name', true);
    }

    public function get_phone()
    {
        return get_post_meta($this->booking_id, 'qrr_phone', true);
    }

    public function get_restaurant_id()
    {
        return intval(get_post_meta($this->booking_id, 'qrr_restaurant_id', true));
    }

    public function get_restaurant_name()
    {
        $rest_id = $this->get_restaurant_id();
        return get_the_title($rest_id);
    }

    public function get_date_day()
    {
        return get_post_meta($this->booking_id, 'qrr_date', true);
    }

    public function get_date_hour()
    {
        return get_post_meta($this->booking_id, 'qrr_time', true);
    }

    public function get_date()
    {
        $date = get_post_meta($this->booking_id, 'qrr_date', true);
        $time = get_post_meta($this->booking_id, 'qrr_time', true);
        if (empty($date) || empty($time)) { return false;
        }
        return $date.' '.$time.':00';
    }

    public function get_schedule_name()
    {
        return get_post_meta($this->booking_id, 'qrr_schedule_name', true);
    }

    public function get_duration()
    {
        $duration = get_post_meta($this->booking_id, 'qrr_duration', true);
        if (!$duration) {
            $duration = 0; // All time
        }
        return $duration;
    }

    public function get_start_hour()
    {
        return $this->get_date_hour();
    }

    public function get_end_hour()
    {
        return qrr_integer_to_hour($this->get_end_minutes());
    }

    public function get_start_minutes()
    {
        return qrr_hour_to_integer($this->get_start_hour());
    }

    public function get_end_minutes()
    {
        $duration = $this->get_duration();
        if ($duration > 0) {
            $end = qrr_hour_to_integer($this->get_start_hour()) + $duration;
            return $end;
        }
        return 24*60;
    }

    public function get_date_formatted()
    {
        $date = $this->get_date();
        if (empty($date)) { return '';
        }

        $time = strtotime($date);
        $remove_i18n = QRR()->settings->get('qrr_i18n');

        if ($remove_i18n) {
            return date(get_option('date_format'), $time).' '.date(get_option('time_format'), $time);
        } else {
            return date_i18n(get_option('date_format'), $time).' '.date_i18n(get_option('time_format'), $time);
        }

        //return date(get_option('date_format'),$time).' '.date(get_option('time_format'),$time);
        //return date_i18n(get_option('date_format'),$time).' '.date_i18n(get_option('time_format'),$time);
    }

    function get_pending_link_email()
    {
        return admin_url('edit.php?post_type=qrr_booking&qrr_action=pending_email&booking_id='.$this->booking_id.'&type=email&_wpnonce='.wp_create_nonce('email_action'));
    }

    function get_confirm_link_email()
    {
        return admin_url('edit.php?post_type=qrr_booking&qrr_action=confirm_email&booking_id='.$this->booking_id.'&type=email&_wpnonce='.wp_create_nonce('email_action'));
        //return admin_url('edit.php?post_type=qrr_booking&action_mail=confirm_email&post_id='.$this->booking_id);
    }

    function get_reject_link_email()
    {
        return admin_url('edit.php?post_type=qrr_booking&qrr_action=reject_email&booking_id='.$this->booking_id.'&type=email&_wpnonce='.wp_create_nonce('email_action'));
        //return admin_url('edit.php?post_type=qrr_booking&action_mail=reject_email&post_id='.$this->booking_id);
    }

    function get_cancel_link_email()
    {
        return admin_url('edit.php?post_type=qrr_booking&qrr_action=cancel_no_email&booking_id='.$this->booking_id.'&type=email&_wpnonce='.wp_create_nonce('email_action'));
        //return admin_url('edit.php?post_type=qrr_booking&action_mail=cancel&post_id='.$this->booking_id);
    }


    public function get_message_from_custom_fields()
    {

        $custom_fields = get_post_meta($this->booking_id, 'qrr_custom_fields', true);

        if (empty($custom_fields)) {
            return '';
        }

        foreach( $custom_fields as $field ) {
            if ($field['type'] == 'textarea' ) {
                return $field['label'].': '.nl2br($field['value']);
            }
        }
    }

    // Will fetch all values except first textarea that is the messahe
    public function get_summary_custom_fields()
    {

        $custom_fields = get_post_meta($this->booking_id, 'qrr_custom_fields', true);
        if (empty($custom_fields)) {
            return '';
        }

        $list_values = array();
        $first_message = true;
        foreach( $custom_fields as $field ) {

            $value = $field['value'];
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $list_values[] = $field['label'].': '.nl2br($value);

        }

        return implode(' | ', $list_values);
    }

}
