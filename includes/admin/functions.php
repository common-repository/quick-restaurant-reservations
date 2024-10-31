<?php

function qrr_is_admin_settings_page($hook)
{

    global $pagenow, $typenow;
    $ret = (isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'qrr-settings' );
    return (bool) apply_filters('qrr_is_admin_settings_page', $ret);
}

function qrr_is_admin_post_type_page($hook, $post_type)
{

    global $pagenow, $typenow, $post;

    $ret = ( ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) && $typenow == $post_type );
    return (bool) apply_filters('qrr_is_admin_'.$post_type.'_post_type', $ret);
}


function qrr_get_time_intervals()
{

    $list = array();

    $data = array(
        array( _x('5 minutes', 'Time Intervals', 'qrr'), 5 ),
        array( _x('10 minutes', 'Time Intervals', 'qrr'), 10 ),
        array( _x('15 minutes', 'Time Intervals', 'qrr'), 15 ),
        array( _x('20 minutes', 'Time Intervals', 'qrr'), 20 ),
        array( _x('30 minutes', 'Time Intervals', 'qrr'), 30 ),
        array( _x('45 minutes', 'Time Intervals', 'qrr'), 45 ),
        array( _x('60 minutes', 'Time Intervals', 'qrr'), 60 ),
    );

    foreach( $data as $d ) {
        $list[] = array(
            'name' => $d[0],
            'value' => $d[1]
        );
    }

    return apply_filters('qrr_schedule_time_intervals', $list);

}

function qrr_get_duration_options()
{
    return apply_filters('qrr_schedule_duration_options', array());
}

function qrr_get_notification_options()
{
    return apply_filters('qrr_schedule_notifications_options', array());
}


function qrr_get_pages( $force = false )
{

    $pages = get_pages();
    if ($pages ) {
        foreach ( $pages as $page ) {
            $pages_options[ $page->ID ] = $page->post_title;
        }
    }

    return $pages_options;
}


function qrr_change_status( $post_id, $status )
{

    $the_post = get_post(intval($post_id));
    $from = $the_post->post_status;
    $to = $status;

    wp_update_post(
        array(
        'ID' => $post_id,
        'post_status' => $status
        )
    );

    do_action('qrr_booking_changed_state_to', $post_id, $status);
    do_action('qrr_booking_changed_state', $post_id, $from, $to);

    // Don't use this, is not updating post_status for ajax mode
    //global $wpdb;
    //$wpdb->update($wpdb->posts,array('post_status'=>$status),array('ID'=>$post_id));

}



// Helpers to transform hours to integers
//---------------------------------------------
function qrr_get_list_hours_for_interval( $from = '00:00', $to = '23:55', $interval = 5 )
{
    $list = array();

    $start = qrr_hour_to_integer($from);
    $end = qrr_hour_to_integer($to);
    $interval = intval($interval);

    for ($i = $start; $i <= $end; $i += $interval ){
        $list[] = qrr_integer_to_hour($i);
    }

    return $list;
}


function qrr_hour_to_integer( $hour = '00:00' )
{

    $data = explode(':', $hour);
    $totalminutes = intval($data[0]) * 60 + intval($data[1]);

    return $totalminutes;
}

function qrr_integer_to_hour( $totalminutes = 0 )
{

    $hours = intval(floor($totalminutes/60));
    $minutes = $totalminutes - 60 * $hours;

    $str_hours = $hours < 10 ? "0".$hours : "".$hours;
    $str_minutes = $minutes < 10 ? "0".$minutes : "".$minutes;

    return $str_hours.':'.$str_minutes;
}

// duration = 0 means until end of the day
function qrr_interval_minutes( $hour, $duration = 0, $interval = 5 )
{

    $booking_start_minutes = qrr_hour_to_integer($hour);

    if ($duration > 0) {
        $booking_end_minutes = $booking_start_minutes + $duration;
    } else {
        $booking_end_minutes = qrr_hour_to_integer('24:00');
    }
    if ($booking_end_minutes > qrr_hour_to_integer('24:00')) {
        $booking_end_minutes = qrr_hour_to_integer('24:00');
    }

    // Values in minutes every 5 minutes
    $booking_minutes = array();
    for ($i = $booking_start_minutes; $i <= $booking_end_minutes; $i += $interval){
        $booking_minutes[] = $i;
    }

    return $booking_minutes;
}


// Current user
//----------------

function qrr_get_current_user_email()
{
    $current_user = wp_get_current_user();
    return $current_user->user_email;
}



// Bookings admin url
//----------------

// @TODO
function qrr_get_bookings_admin_url()
{
    return apply_filters('qrr_bookings_admin_url', get_admin_url().'edit.php?post_type=qrr_booking');

}


// Helpers
//----------------

function qrr_get_qrr_booking( $booking_id )
{
    return new QRR_Booking_Model($booking_id);
}

