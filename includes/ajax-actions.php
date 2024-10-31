<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


//-----------------------------
// ADMIN - Test date with schedules
//-----------------------------

add_action('wp_ajax_test_schedules_date', 'qrr_ajax_test_schedules_date');
add_action('wp_ajax_nopriv_test_schedules_date', 'qrr_ajax_test_schedules_date');

function qrr_ajax_test_schedules_date()
{
    $restaurant_id = intval(sanitize_text_field($_POST['restaurant_id']));
    $date_str = sanitize_text_field($_POST['date']);
    $schedules = sanitize_text_field($_POST['schedules']);

    $schedules = str_replace('\"', '"', $schedules);
    $schedules =  json_decode($schedules, true);
    $date = strtotime($date_str);

    $rest = new QRR_Restaurant($restaurant_id);
    $list = $rest->get_schedules_coincidences($date, $schedules, true);

    if (empty($list)) {
        wp_send_json_error(__('No rules found to check', 'Check date schedules', 'qrr'));
    }

    // Check the list of coincidences, return the first one strating from the end
    $list_messages = array();
    $list_rules = array();

    for ($i = count($list) - 1; $i >= 0; $i-- ) {

        if($list[$i] && $schedules[$i]['active'] ) {

            if ($schedules[$i]['opened']) {
                $list_messages[] = date('l Y/m/d H:i', $date).' -> Applied Rule '.($i+1).': <strong style="color:green;">OPENED</strong>';
                $list_rules[] = $i;
                //                wp_send_json_success( array(
                //                    'message' => date('l Y/m/d H:i',$date).' -> Applied Rule '.($i+1).': <strong style="color:green;">OPENED</strong>',
                //                    'rule' => $i
                //                ));
            } else {
                $list_messages[] = date('l Y/m/d H:i', $date).' -> Applied Rule '.($i+1).': <strong style="color:red;">CLOSED</strong>';
                $list_rules[] = $i;
                //                wp_send_json_success( array(
                //                    'message' => date('l Y/m/d H:i',$date).' -> Applied Rule '.($i+1).': <strong style="color:red;">CLOSED</strong>',
                //                    'rule' => $i
                //                ));
            }


        }

    }

    if (!empty($list_messages)) {
        wp_send_json_success(
            array(
            'messages' => $list_messages,
            'rules' => $list_rules
            )
        );
    }


    wp_send_json_error(date('l Y/m/d H:i', $date). __(' -> No rule can be applied, so <strong>Date not avaliable</strong>', 'qrr'));

}




//-----------------------------
// FRONT - Available hours
//-----------------------------

add_action('wp_ajax_get_available_hours', 'qrr_ajax_get_available_hours');
add_action('wp_ajax_nopriv_get_available_hours', 'qrr_ajax_get_available_hours');

function qrr_ajax_get_available_hours()
{
    //if( ! wp_verify_nonce( $_POST['nonce'], 'qrr_form' ) ){
    //    wp_send_json_error( __('Server error, nonce is wrong. Contact the administrator.', 'qrr') );
    //}

    // Data posted
    $restaurant_id = intval(sanitize_text_field($_POST['id']));
    //$date_time = intval($_POST['date']['time'])/1000; // Don't use this, seems comes with 1 day less from pickeadate
    $date_str = sanitize_text_field($_POST['date']['text']);
    $date_time = strtotime(sanitize_text_field($_POST['date']['day'])); //2017/10/08
    $party = intval(sanitize_text_field($_POST['party']));

    // Available hours
    //$hours =  qrr_get_booking_hours( $restaurant_id, $date_time, $party );
    $restaurant = new QRR_Restaurant($restaurant_id);
    $hours = $restaurant->get_booking_hours($date_time, $party);

    // Success list of hours
    if ($hours && !empty($hours)) {
        wp_send_json_success(array('hours'=>$hours));
    }

    // No results
    $rest_model = new QRR_Restaurant_Model($restaurant_id);
    $result_no = $rest_model->get_message_hours_not_available();
    $result_no = str_replace('{date}', $date_str, $result_no);
    $result_no = str_replace('{party}', $party, $result_no);

    wp_send_json_error($result_no);

}




//-----------------------------
// FRONT - Request booking
//-----------------------------

add_action('wp_ajax_request_booking', 'qrr_ajax_request_booking');
add_action('wp_ajax_nopriv_request_booking', 'qrr_ajax_request_booking');

function qrr_ajax_request_booking()
{

    //if( ! wp_verify_nonce( $_POST['nonce'], 'qrr_form' ) ){
    //    wp_send_json_error( __('Server error, nonce is wrong. Contact the administrator.', 'qrr') );
    //}

    // Data posted
    $restaurant_id = intval(sanitize_text_field($_POST['id']));

    // Sanitize all array data
    $booking_data = qrr_sanitize_text_or_array_field($_POST['booking_data']);

    // Is front end
    $is_front_end = ( sanitize_text_field($_POST['is_front_end']) == 'yes' );

    if ($is_front_end) {

        // Validation errors
        $validate = new QRR_Booking_Validation($restaurant_id);
        $errors = $validate->validate($booking_data);

        if (!empty($errors)) {

            // Only main error message if banned ip or email
            if (isset($errors['banned_ip'])) {
                wp_send_json_error($errors['banned_ip']);
            } else if (isset($errors['banned_email'])) {
                wp_send_json_error($errors['banned_email']);
            }

            // List of errors
            wp_send_json_error(array( 'validation' => $errors ));
        }

        else {

            // Process booking
            $booking_form = new QRR_Booking_Form();
            $result = $booking_form->process_booking_form($restaurant_id, $booking_data, true);

            if (is_wp_error($result) ) {
                wp_send_json_error($result->get_error_message('process'));
            } else {
                wp_send_json_success($result); // array(message,redirect,timeout)
            }

        }

    }

    // Create booking from back end
    else {

        // Process booking
        $booking_form = new QRR_Booking_Form();
        $result = $booking_form->process_booking_form($restaurant_id, $booking_data, false);

        if (is_wp_error($result) ) {
            wp_send_json_error($result->get_error_message('process'));
        } else {
            wp_send_json_success($result); // array(message,redirect)
        }

    }


}


function qrr_sanitize_array($input)
{
    $new_input = array();

    foreach ( $input as $key => $val ) {
        $new_input[ $key ] = sanitize_text_field($val);
    }

    return $new_input;
}

function qrr_sanitize_text_or_array_field($array_or_string)
{

    if(is_string($array_or_string) ) {
        $array_or_string = sanitize_text_field($array_or_string);
    }
    elseif(is_array($array_or_string) ) {
        foreach ( $array_or_string as $key => &$value ) {
            if (is_array($value) ) {
                $value = qrr_sanitize_text_or_array_field($value);
            }
            else {
                $value = sanitize_text_field($value);
            }
        }
    }

    return $array_or_string;
}


//-----------------------------
// ADMIN - sent update email
//-----------------------------

add_action('wp_ajax_send_update_email', 'qrr_ajax_send_update_email');
add_action('wp_ajax_nopriv_send_update_email', 'qrr_ajax_send_update_email');

function qrr_ajax_send_update_email()
{

    $booking_id = intval(sanitize_text_field($_POST['booking_id']));
    $message = sanitize_text_field($_POST['message']);

    $booking_email = new QRR_Booking_Email($booking_id);
    $booking_email->send_customer_email_update($message);

    $list = get_post_meta($booking_id, 'qrr_email_records', true);

    wp_send_json_success(array('list'=> array_reverse($list) ));
}

//-----------------------------
// ADMIN - Create bookings
//-----------------------------


add_action('wp_ajax_get_available_hours_all', 'qrr_ajax_get_available_hours_all');

function qrr_ajax_get_available_hours_all()
{

    // Data posted
    $restaurant_id = intval(sanitize_text_field($_POST['id']));
    $date_str = sanitize_text_field($_POST['date']['text']);
    $date_time = strtotime(sanitize_text_field($_POST['date']['day'])); //2017/10/08
    //$party = intval(sanitize_text_field($_POST['party'])); // not needed

    // Available hours
    //$hours =  qrr_get_booking_hours( $restaurant_id, $date_time, $party );
    $restaurant = new QRR_Restaurant($restaurant_id);
    $hours = $restaurant->get_booking_hours_all($date_time);

    // Success list of hours
    if ($hours && !empty($hours)) {
        wp_send_json_success(array('hours'=>$hours));
    }

    // No results
    $result_no = __('No hours available for date {date}.', 'qrr');
    $result_no = str_replace('{date}', $date_str, $result_no);

    wp_send_json_error(array('message'=>$result_no));
}

