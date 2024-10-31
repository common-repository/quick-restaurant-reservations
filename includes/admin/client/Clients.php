<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Clients
{

    private $post_type = 'qrr_client';

    public function __construct()
    {

    }

    public static function get_list_clients()
    {

        global $wpdb;

        $sql = "SELECT ID FROM {$wpdb->posts}
                WHERE post_type='qrr_client'
                AND post_status='publish'
                ORDER BY post_title ASC";

        $list = $wpdb->get_col($sql);

        $new_list = array();
        if (!empty($list)) {
            foreach( $list as $item ) {
                $client_id = intval($item);
                $new_list[] = array(
                    'ID' => $client_id,
                    'name' => get_the_title($client_id),
                    'email' => get_post_meta($client_id, 'qrr_email', true),
                    'phone' => get_post_meta($client_id, 'qrr_phone', true)
                );
            }
        }

        return apply_filters('qrr_clients_list', $new_list);
    }
}