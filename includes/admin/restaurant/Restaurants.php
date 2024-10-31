<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Restaurants
{

    public static function get_list_of_restaurants()
    {
        global $wpdb;
        $sql = "SELECT ID as value, post_title as title FROM {$wpdb->posts} WHERE post_type='qrr_restaurant' AND post_status='publish'";
        return $wpdb->get_results($sql, ARRAY_A);
    }

}