<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Client_Model
{

    private $client_id = 0;

    public function __construct( $client_id = 0 )
    {
        $this->client_id = $client_id;
    }

    public function get_email()
    {
        return get_post_meta($this->client_id, 'qrr_email', true);
    }

    public function get_phone()
    {
        return get_post_meta($this->client_id, 'qrr_phone', true);
    }

    public function get_bookings_count()
    {

        $list = $this->get_bookings();

        $result = array();

        $list_statuses = QRR_Booking_Edit::get_list_status();
        foreach($list_statuses as $key => $status){
            $result[$key] = array(
                'status' => $key,
                'label' => $status['label'],
                'count' => 0
            );
            if (!empty($list)) {
                foreach($list as $item){
                    if ($key == $item['status']) {
                        $result[$key]['count']++;
                    }
                }
            }
        }

        return $result;
    }

    public function get_bookings( $limit = 0)
    {

        $email = $this->get_email();

        global $wpdb;
        $sql = "SELECT p.ID as booking_id, p.post_status as status FROM {$wpdb->posts} as p
                INNER JOIN {$wpdb->postmeta} as pm
                ON pm.post_id = p.ID
                INNER JOIN {$wpdb->postmeta} as pmdate
                ON pmdate.post_id = p.ID
                INNER JOIN {$wpdb->postmeta} as pmtime
                ON pmtime.post_id = p.ID
                WHERE p.post_type = 'qrr_booking'
                AND pm.meta_key = 'qrr_email'
                AND pm.meta_value = '{$email}'
                AND pmdate.meta_key = 'qrr_date'
                AND pmtime.meta_key = 'qrr_time'
                ORDER BY (pmdate.meta_value) DESC
        ";

        if ($limit > 0) {
            $sql .= ' lIMIT '.$limit;
        }

        return $wpdb->get_results($sql, ARRAY_A);
    }


    // Static functions

    public static function set_up_new_client( $email, $name, $phone = '' )
    {

        $client_id = self::get_client_id($email);

        // Create the client if not exists
        if (!$client_id) {

            $args = array(
                'post_type'        => 'qrr_client',
                'post_title'    => $name,
                'post_content'    => '',
                'post_date'        => date('Y-m-d H:i:s', current_time('timestamp')),
                'post_status'    => 'publish',
            );
            $args = apply_filters('qrr_insert_client_args', $args);

            $client_id = wp_insert_post($args);

            update_post_meta($client_id, 'qrr_email', $email);
            update_post_meta($client_id, 'qrr_phone', $phone);

            do_action('qrr_client_created', $client_id);
        }

        return $client_id;
    }

    public static function get_client_id( $email )
    {
        global $wpdb;
        $sql = "SELECT ID from {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm
            ON pm.post_id = p.ID
            WHERE p.post_type='qrr_client'
            AND post_status='publish'
            AND pm.meta_key='qrr_email'
            AND pm.meta_value='{$email}'
            LIMIT 1
            ";
        return $wpdb->get_var($sql);
    }

    // Don't use this, better fetch the list on the fly
    public static function add_client_booking_list( $client_id , $booking_id )
    {

        $list_bookings = get_post_meta($client_id, 'qrr_booking_list', true);
        if (!is_array($list_bookings)) {
            $list_bookings = array();
        }
        $list_bookings[] = $booking_id;
        update_post_meta($client_id, 'qrr_booking_list', $list_bookings);
    }



}