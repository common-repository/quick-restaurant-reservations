<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

function qrr_setup_post_types()
{
    global $wp_version;

    // Post type restaurants
    $restaurant_labels = array(
        'name'                 => _x('Restaurants', 'post type general name', 'qrr'),
        'singular_name'     => _x('Restaurant', 'post type singular name', 'qrr'),
        'add_new'             => __('Add New Restaurant', 'qrr'),
        'add_new_item'         => __('Add New Restaurant', 'qrr'),
        'edit_item'         => __('Edit Restaurant', 'qrr'),
        'new_item'             => __('New Restaurant', 'qrr'),
        'all_items'         => __('All Restaurants', 'qrr'),
        'view_item'         => __('View Restaurants', 'qrr'),
        'search_items'         => __('Search Restaurants', 'qrr'),
        'not_found'         => __('No Restaurants found', 'qrr'),
        'not_found_in_trash'=> __('No Restaurants found in Trash', 'qrr'),
        'parent_item_colon' => '',
        'menu_name'         => _x('Restaurants', 'restaurant post type menu name', 'qrr')
    );

    $restaurant_args = array(
        'labels'          => apply_filters('qrr_restaurant_labels', $restaurant_labels),
        'public'          => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'can_export'      => true,
        'show_in_menu'    => false,
        'supports'        => apply_filters('qrr_restaurant_supports', array('title')),
    );
    register_post_type('qrr_restaurant', apply_filters('qrr_restaurant_args', $restaurant_args));

    // Post type Booking
    $booking_labels = array(
        'name'                 => _x('Bookings', 'post type general name', 'qrr'),
        'singular_name'     => _x('Booking', 'post type singular name', 'qrr'),
        'add_new'             => __('Add New Booking', 'qrr'),
        'add_new_item'         => __('Add New Booking', 'qrr'),
        'edit_item'         => __('Edit Booking', 'qrr'),
        'new_item'             => __('New Booking', 'qrr'),
        'all_items'         => __('All Bookings', 'qrr'),
        'view_item'         => __('View Bookings', 'qrr'),
        'search_items'         => __('Search Bookings', 'qrr'),
        'not_found'         => __('No Bookings found', 'qrr'),
        'not_found_in_trash'=> __('No Bookings found in Trash', 'qrr'),
        'parent_item_colon' => '',
        'menu_name'         => _x('Rest. Bookings', 'booking post type menu name', 'qrr')
    );

    $booking_args = array(
        'labels'          => apply_filters('qrr_booking_labels', $booking_labels),
        'public'          => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'menu_icon'       => 'dashicons-clipboard',
        'can_export'      => true,
        //'capabilities' => array('create_posts' => false),
        'supports'        => apply_filters('qrr_booking_supports', array('title')),
    );
    register_post_type('qrr_booking', apply_filters('qrr_booking_args', $booking_args));

    // Post type Payments
    /*$payment_labels = array(
        'name'               => _x( 'Payments', 'post type general name', 'qrr' ),
        'singular_name'      => _x( 'Payment', 'post type singular name', 'qrr' ),
        'add_new'            => __( 'Add New', 'qrr' ),
        'add_new_item'       => __( 'Add New Payment', 'qrr' ),
        'edit_item'          => __( 'Edit Payment', 'qrr' ),
        'new_item'           => __( 'New Payment', 'qrr' ),
        'all_items'          => __( 'All Payments', 'qrr' ),
        'view_item'          => __( 'View Payment', 'qrr' ),
        'search_items'       => __( 'Search Payments', 'qrr' ),
        'not_found'          => __( 'No Payments found', 'qrr' ),
        'not_found_in_trash' => __( 'No Payments found in Trash', 'qrr' ),
        'parent_item_colon'  => '',
        'menu_name'          => __( 'Payment History', 'qrr' )
    );

    $payment_args = array(
        'labels'          => apply_filters( 'qrr_payment_labels', $payment_labels ),
        'public'          => false,
        'query_var'       => false,
        'rewrite'         => false,
        //'capability_type' => 'shop_payment',
        //'map_meta_cap'    => true,
        'supports'        => array( 'title' ),
        'can_export'      => true
    );*/
    //register_post_type( 'qrr_payment', apply_filters('qrr_payment_args',$payment_args) );

    // Post type Clients
    $client_labels = array(
        'name'               => _x('Clients', 'post type general name', 'qrr'),
        'singular_name'      => _x('Client', 'post type singular name', 'qrr'),
        'add_new'            => __('Add New', 'qrr'),
        'add_new_item'       => __('Add New Client', 'qrr'),
        'edit_item'          => __('Edit Client', 'qrr'),
        'new_item'           => __('New Client', 'qrr'),
        'all_items'          => __('All Clients', 'qrr'),
        'view_item'          => __('View Client', 'qrr'),
        'search_items'       => __('Search Clients', 'qrr'),
        'not_found'          => __('No Clients found', 'qrr'),
        'not_found_in_trash' => __('No Clients found in Trash', 'qrr'),
        'parent_item_colon'  => '',
        'menu_name'          => __('Clients', 'qrr')
    );

    $client_args = array(
        'labels'          => apply_filters('qrr_client_labels', $client_labels),
        'public'          => true,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'can_export'      => true,
        'show_in_menu'    => false,
        'supports'        => apply_filters('qrr_client_supports', array('title')),
    );
    register_post_type('qrr_client', apply_filters('qrr_client_args', $client_args));

    // Post status
    $list_status = QRR_Booking_Edit::get_list_status();
    foreach( $list_status as $key => $data ) {
        register_post_status($key, $data);
    }
}

add_action('init', 'qrr_setup_post_types');

