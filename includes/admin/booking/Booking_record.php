<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


// List with emails sent

class QRR_Booking_Record
{

    public function __construct()
    {

        add_action('qrr_email_sent', array($this, 'register_email_sent'), 10, 6);
    }

    public function register_email_sent( $sent, $category, $booking_id, $to, $subject, $message)
    {

        $list = get_post_meta($booking_id, 'qrr_email_records', true);

        if (!is_array($list)) {
            $list = array();
        }
        $list[] = array(
            'success' => $sent,
            'time' =>  current_time('timestamp'),
            'category' => $category,
            'to' => $to,
            'subject' => $subject,
            'message' => $message
        );

        update_post_meta($booking_id, 'qrr_email_records', $list);

    }

}

new QRR_Booking_Record();