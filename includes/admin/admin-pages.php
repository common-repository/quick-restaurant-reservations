<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

function qrr_add_menu_pages()
{
    global $qrr_bookings_page, $qrr_payments_page, $qrr_customers_page;

    /*
    $qrr_bookings_page = add_menu_page(
        _x( 'Bookings', 'Title admin page for bookings', 'qrr' ),
        _x( 'Bookings', 'Title admin bookings menu item', 'qrr' ),
        'manage_options', //'manage_bookings',
        'qrr-bookings',
        'qrr_show_admin_bookings_page',
        'dashicons-clipboard',
        '26'
    );
    $main = 'qrr-bookings';
    */

    $main = 'edit.php?post_type=qrr_booking';
    $qrr_add_new_booking   = add_submenu_page($main, __('Create Booking', 'qrr'), __('Create Booking', 'qrr'), 'edit_posts', 'qrr_create_booking', 'qrr_show_new_booking');
    $qrr_restaurant_page   = add_submenu_page($main, __('Restaurants', 'qrr'), __('Restaurants', 'qrr'), 'edit_posts', 'edit.php?post_type=qrr_restaurant', null);
    $qrr_customers_page    = add_submenu_page($main, __('Clients', 'qrr'), __('Clients', 'qrr'), 'edit_posts', 'edit.php?post_type=qrr_client', null);
    //$qrr_payments_page     = add_submenu_page( $main, __( 'Payments', 'qrr' ), __( 'Payments', 'qrr' ), 'manage_options', 'qrr-payments', 'qrr_show_admin_payments_page' );
    $qrr_settings          = add_submenu_page($main, __('Settings', 'qrr'), __('Settings', 'qrr'), 'manage_options', 'qrr-settings', 'qrr_show_settings_page');
    $qrr_addons            = add_submenu_page($main, __('Add-ons', 'qrr'), __('Add-ons', 'qrr'), 'manage_options', 'qrr-addons', 'qrr_show_addons_page');
}
add_action('admin_menu', 'qrr_add_menu_pages', 10);

function qrr_remove_default_create_booking_url()
{
    global $submenu;

    if (isset($submenu['edit.php?post_type=qrr_booking']) ) {
        foreach ( $submenu['edit.php?post_type=qrr_booking'] as $key => $value ) {
            if ('post-new.php?post_type=qrr_booking' == $value[2] ) {
                unset($submenu['edit.php?post_type=qrr_booking'][ $key ]);
                return;
            }
        }
    }
}

add_action('admin_menu', 'qrr_remove_default_create_booking_url', 20);

function qrr_show_admin_payments_page()
{
    echo '<h1>PAYMENTS PAGE</h1>';
}


function qrr_show_settings_page()
{
    do_action('qrr_settings_admin_page');
}

function qrr_show_new_booking()
{
    include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_create.php';
    $page = new QRR_Booking_Create();
    $page->output();
}

function qrr_show_addons_page()
{
    include QRR_PLUGIN_DIR . 'includes/admin/template-addons/addons-page.php';
}
