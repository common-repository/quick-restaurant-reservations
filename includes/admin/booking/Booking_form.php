<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Booking_Form
{


    public function __construct()
    {

    }

    // Process the form submitted at the front-end, and at the backend
    public function process_booking_form( $restaurant_id, $form, $is_front_end = true )
    {

        $result = $this->is_booking_available($restaurant_id, $form, $is_front_end);

        //ray('result');
        //ray($result);

        if (!$result['success'] ) {

            $restaurant_model = new QRR_Restaurant_Model($restaurant_id);
            return $restaurant_model->get_message_not_available();
        }

        else {

            $booking_id = $this->create_booking($restaurant_id, $form, $result);

            if (is_wp_error($booking_id)) {
                return $booking_id;
            }

            // Send email to admin and customer if form front-end
            if ($is_front_end) {
                $booking_email = new QRR_Booking_Email($booking_id);
                $booking_email->send_admin_email();
                $booking_email->send_customer_email();
            }

            // Send email to customer when creating booking ad the backend
            else {
                $qrr_send_email = $this->get_form_value($form, 'qrr-send-email');
                if ($qrr_send_email == 'on') {
                    $booking_email = new QRR_Booking_Email($booking_id);
                    $booking_email->send_customer_email();
                }
            }

            // Use this to trigger other actions
            do_action('qrr_booking_requested', $booking_id);

            // Return message
            $restaurant_model = new QRR_Restaurant_Model($restaurant_id);

            if(get_post_status($booking_id) == 'pending' ) {
                $result = $restaurant_model->get_message_pending();
            } else {
                $result = $restaurant_model->get_message_confirmed();
            }

            $booking_details = $this->get_booking_details($restaurant_id, $form);
            $booking_details = apply_filters('qrr_form_booking_details', $booking_details, $booking_id);
            $result['message'] = str_replace('{booking_details}', $booking_details, $result['message']);

            $result['booking_id'] = $booking_id;
            $result['booking_edit'] = get_edit_post_link($booking_id);
            return $result;
        }

    }

    function get_booking_details( $restaurant_id, $form )
    {

        $qrr_date = $this->get_form_value($form, 'qrr-date_submit', 'sanitize_text_field');
        $qrr_time = $this->get_form_value($form, 'qrr-time', 'sanitize_text_field');
        $qrr_time_data = explode('_', $qrr_time);
        $qrr_time_hour = $qrr_time_data[1];
        $qrr_party = $this->get_form_value($form, 'qrr-party', 'intval');
        $qrr_name = $this->get_form_value($form, 'qrr-name', 'sanitize_deep_text_field');
        $qrr_email = $this->get_form_value($form, 'qrr-email', 'sanitize_deep_text_field');
        $qrr_phone = $this->get_first_type_value('phone', $form, 'sanitize_text_field');

        $details = '<br>';
        $details .= '<div class="booking-details">';
        $details .= '<div class="booking.details-name"><strong>'.$qrr_name.'</strong></div>';
        $details .= '<div class="booking.details-email">'._x('Party', 'booking details', 'qrr').' '.$qrr_party.'</div>';
        $details .= '<div class="booking.details-date">'.$this->get_date_formatted($qrr_date.' '.$qrr_time_hour).'</div>';
        $details .= '<div class="booking.details-email">'.$qrr_email.'</div>';
        $details .= '<div class="booking.details-phone">'.$qrr_phone.'</div>';
        $details .= '</div>';

        return $details;
    }

    function get_date_formatted($qrr_date)
    {
        $date = $qrr_date;

        $time = strtotime($date);
        $remove_i18n = QRR()->settings->get('qrr_i18n');

        if ($remove_i18n) {
            $result = date(get_option('date_format'), $time).' '.date(get_option('time_format'), $time);
        } else {
            $result = date_i18n(get_option('date_format'), $time).' '.date_i18n(get_option('time_format'), $time);
        }

        return $result;
    }


    function is_booking_available( $restaurant_id, $form, $is_front_end = true )
    {

        $qrr_date = $this->get_form_value($form, 'qrr-date_submit', 'sanitize_text_field'); //2017/09/20
        $qrr_date = str_replace('/', '-', $qrr_date); // 2017-09-20

        $qrr_time = $this->get_form_value($form, 'qrr-time', 'sanitize_text_field'); // 0_09:45, schedule 0 hour 09:45
        $qrr_party = $this->get_form_value($form, 'qrr-party', 'intval');

        $qrr_time_data = explode('_', $qrr_time);
        $schedule_index = intval($qrr_time_data[0]);
        $qrr_time_hour = $qrr_time_data[1];

        // Check can book
        if (!QRR_Active('capacity')) {

            $result = array(
                'success'   => true,
                'mode'      => 'pending', //pending, confirmed
                'duration'  => 90, // minutes, 0 ? full time
                'tables'    => '' // To define from tables
            );

        } else {

            $result = QRR_Bookings_Controller::can_book($restaurant_id, $qrr_date, $qrr_time_hour, $schedule_index, $qrr_party);
            //ray('is_booking_available');
            //ray($result);
        }


        // From admin request force the status
        if (!$is_front_end ) {

            //$status = 'pending';

            // Overwrite booked from admin
            $result = array(
                'success'   => true,
                'mode'      => 'pending', //pending, confirmed
                'duration'  => 90, // minutes, 0 ? full time
                'tables'    => '' // To define from tables
            );

            // Overwrite status submitted
            //if (!$is_front_end){
                $status = $this->get_form_value($form, 'qrr-status', 'sanitize_text_field');
                $status = str_replace('qrr-', '', $status);
            //}

            $result['mode'] = $status;
        }

        //echo '<pre>'; print_r( $result ); echo '</pre>'; die();

        return $result;
    }



    // how is array with mode and duration
    function create_booking( $restaurant_id, $form, $how = array('success'=>true, 'mode'=>'pending', 'duration'=>120, 'tables'=>'') )
    {

        //ray('create_booking');
        //ray($how);
        //echo '<pre>'; print_r( $how ); echo '</pre>';
        //die();

        // For testing data
        /* update_option('test_form', array(
            'restaurant_id' => $restaurant_id,
            'form' => $form
        ));*/
        //return new WP_Error('process', __('There was an error processing your booking. Please call us to make a booking. Thanks.','qrr'));


        // Create the post qrr_booking
        $qrr_date = $this->get_form_value($form, 'qrr-date_submit', 'sanitize_text_field'); //2017/09/20
        //$label_date = $this->get_form_value( $form, 'label_qrr-date', 'sanitize_text_field' );

        $qrr_time = $this->get_form_value($form, 'qrr-time', 'sanitize_text_field'); // 0_09:45
        //$label_time = $this->get_form_value( $form, 'label_qrr-time', 'sanitize_text_field' );

        $qrr_name = $this->get_form_value($form, 'qrr-name', 'sanitize_deep_text_field');
        //$label_name = $this->get_form_value( $form, 'label_qrr-name', 'sanitize_text_field' );

        //$qrr_party = $this->get_form_value( $form, 'qrr-party' , 'intval' );
        //$label_party = $this->get_form_value( $form, 'label_qrr-name', 'sanitize_text_field' );

        $booking_date = $this->transform_to_date($qrr_date, $qrr_time);

        $qrr_message = $this->get_first_type_value('textarea', $form, 'sanitize_textarea');


        $status = 'pending';
        if ($how['mode'] == 'confirmed' || ($how['mode'] == 'qrr-confirmed')) {
            $status = 'qrr-confirmed';
        } else if ($how['mode'] == 'cancelled' || ($how['mode'] == 'qrr-cancelled')) {
            $status = 'qrr-cancelled';
        } else if ($how['mode'] == 'rejected' || ($how['mode'] == 'qrr-rejected')) {
            $status = 'qrr-rejected';
        }

        // Create the post qrr_booking
        $args = array(
            'post_type'        => 'qrr_booking',
            'post_title'    => $qrr_name,
            'post_content'    => $qrr_message,
            //'post_date'        => $booking_date, // Does not work when I edit the post
            'post_status'    =>  $status
        );
        $args = apply_filters('qrr_insert_booking_args', $args);

        $booking_id = wp_insert_post($args);
        if (is_wp_error($booking_id) || $booking_id === false ) {
            return new WP_Error('process', __('There was an error processing your booking. Please call us to make a booking. Thanks.', 'qrr'));
        }

        // Update post meta
        $this->set_up_booking_meta($restaurant_id, $booking_id, $form, $how);

        // Create/Update client
        $client_id = $this->set_up_client_data($booking_id, $form);
        //update_post_meta($booking_id, 'qrr_client_id', $client_id);

        return $booking_id;
    }



    // Helpers for processing the form
    //----------------------------------------

    public function get_form_value( $form, $name, $sanitize = '', $default = '' )
    {

        $value = $default;

        if ($form && is_array($form)) {
            foreach( $form as $field ) {

                if ($field['name'] == $name ) {

                    if (is_callable(array($this,$sanitize))) {
                        return call_user_func(array($this,$sanitize), $field['value']);
                    }

                    return $field['value'];
                }
            }
        }


        return $value;
    }

    public function get_form_values( $form, $name, $sanitize = '' )
    {

        $values = array();

        foreach( $form as $field ) {
            if ($field['name'] == $name ) {

                if (is_callable(array($this,$sanitize))) {
                    $values[] = call_user_func(array($this,$sanitize), $field['value']);
                } else {
                    $values[] = $field['value'];
                }
            }
        }

        if (empty($values)) {
            return '';
        } else if (count($values) == 1) {
            return $values[0];
        }

        return $values;
    }

    // Based on field_id get the options available form the defined form
    public function get_form_field_options( $restaurant_id, $field_id )
    {

        $options = array();
        $fields = json_decode(get_post_meta($restaurant_id, 'qrr_booking_fields', true), ARRAY_A);
        foreach( $fields as $field) {
            if ($field['id'] == $field_id) {

                if (isset($field['options'])) {
                    foreach( $field['options'] as $item ){
                        $options[] = $item['value'];
                    }
                }

                return $options;
            }
        }

        return $options;
    }


    function transform_to_date( $qrr_date = "2017/09/20", $qrr_time = '0_22:00')
    {

        $time_split = explode('_', $qrr_time);

        $cadena = $qrr_date.' '.$time_split[1].':00';

        $cadena = str_replace('/', '-', $cadena);

        return $cadena;
    }

    public function get_first_type_value( $type = 'textarea', $form = array(), $sanitize = '' )
    {

        foreach( $form as $field ) {

            // Find field name = 'type_qrr-12832873291' with value = textarea
            if (substr($field['name'], 0, 5) == 'type_' && $field['value'] == $type ) {

                $field_id = str_replace('type_', '', $field['name']);

                // Find the message from the id
                foreach ($form as $field_2) {
                    if ($field_2['name'] == $field_id) {

                        if (is_callable(array($this,$sanitize))) {
                            return call_user_func(array($this,$sanitize), $field_2['value']);
                        }

                        return $field_2['value'];
                    }
                }

            }
        }

        return '';
    }

    public function get_first_type_label( $type = 'phone', $form = array(), $sanitize = '', $default = '' )
    {

        if ($form && is_array($form)) {
            foreach( $form as $field ) {

                // Find field name = 'type_qrr-12832873291' with value = textarea
                if (substr($field['name'], 0, 5) == 'type_' && $field['value'] == $type ) {

                    $field_id = 'label_'.str_replace('type_', '', $field['name']);

                    // Find the message from the id
                    foreach ($form as $field_2) {
                        if ($field_2['name'] == $field_id) {

                            if (is_callable(array($this,$sanitize))) {
                                return call_user_func(array($this,$sanitize), $field_2['value']);
                            }

                            return $field_2['value'];
                        }
                    }

                }
            }
        }

        return $default;
    }


    // Sanitize data
    //----------------------------------------

    function sanitize_text_field( $value )
    {
        return sanitize_text_field($value);
    }

    function sanitize_deep_text_field( $value )
    {
        return wp_strip_all_tags(sanitize_text_field(stripslashes_deep($value)));
    }

    function intval( $value )
    {
        return intval($value);
    }

    function sanitize_textarea( $value )
    {
        if (function_exists('sanitize_textarea_field') ) {
            return sanitize_textarea_field($value);
        }
        return esc_textarea($value);
    }


    // Manage post meta data from the form booking
    //----------------------------------------

    function set_up_booking_meta( $restaurant_id, $booking_id, $form, $how)
    {

        // Save the form initial values
        update_post_meta($booking_id, 'qrr_booking_form', $form);

        // Update some values
        $qrr_date = $this->get_form_value($form, 'qrr-date_submit', 'sanitize_text_field');
        $qrr_date = str_replace('/', '-', $qrr_date);
        $qrr_time = $this->get_form_value($form, 'qrr-time', 'sanitize_text_field');
        $qrr_time_arr = explode('_', $qrr_time);
        $qrr_hour = $qrr_time_arr[1];
        $qrr_phone = $this->get_first_type_value('phone', $form, 'sanitize_text_field');

        // Get schedule name
        $schedule_index = $qrr_time_arr[0];
        $restaurant_model = new QRR_Restaurant_Model($restaurant_id);
        $schedule_name = $restaurant_model->get_schedule_name($schedule_index);

        // Calculate real duration when duration = 0 (alltime)
        $real_duration = $how['duration'];
        if ($real_duration == 0) {
            $end_hour = $restaurant_model->get_final_hour_for_schedule_index($schedule_index);
            $real_duration = qrr_hour_to_integer($end_hour) - qrr_hour_to_integer($qrr_hour);
            //ray('end_hour: ' . $end_hour);
            //ray('real_duration: '.$real_duration);
        }

        // Set a minimum duration here
        $min_duration = apply_filters('qrr_min_booking_duration', 15);
        if ($real_duration < $min_duration) {
            $real_duration = $min_duration;
        }
        //ray($how);
        //ray('real_duration:' . $real_duration);



        // Meta data, saved main only
        $meta = array(
            'qrr_restaurant_id' => $restaurant_id,
            'qrr_email' => $this->get_form_value($form, 'qrr-email', 'sanitize_text_field'),
            'qrr_party' => $this->get_form_value($form, 'qrr-party', 'intval'),
            'qrr_phone' => $this->get_first_type_value('phone', $form, 'sanitize_text_field'),
            'qrr_submitted_time' => current_time('timestamp'),
            'qrr_date' => $qrr_date,
            'qrr_time' => $qrr_hour,
            'qrr_schedule' => $schedule_index, // Schedule index
            'qrr_schedule_name' => $schedule_name,
            'qrr_duration' => $real_duration,
            'qrr_tables' => $how['tables'],
            'qrr_table_name' => $this->get_form_value($form, 'qrr-table-name', 'sanitize_text_field'),
            //'qrr_booking_time' => strtotime($qrr_date.' '.$qrr_hour.':00'),
            'qrr_ip' => $_SERVER['REMOTE_ADDR']
        );

        //ray($meta);


        $meta = apply_filters('qrr_insert_booking_meta', $meta);

        foreach( $meta as $key => $value ) {
            update_post_meta($booking_id, $key, $value);
        }


        // Rest of the form save other way with label and values
        $types = array('phone','text','textarea','select','checkbox','radio');
        $first_phone = true;

        $custom_fields = array();
        foreach( $form as $field ) {

            if (substr($field['name'], 0, 5) == 'type_' && in_array($field['value'], $types) ) {

                $type = $field['value'];
                $field_id = str_replace('type_', '', $field['name']);

                // Don't save first phone
                if ($type == 'phone' && $first_phone) {
                    $first_phone = false;
                } else {
                    $custom_fields[] = array(
                        'id'   => $field_id,
                        'type' => $type,
                        'label' => $this->get_form_value($form, 'label_'.$field_id),
                        'value' => $this->get_form_values($form, $field_id, 'sanitize_text_field'),
                        'options' => $this->get_form_field_options($restaurant_id, $field_id) // For select, radio, checkbox
                    );
                }

            }

        }
        update_post_meta($booking_id, 'qrr_custom_fields', $custom_fields);

    }


    // Manage Client data from the form booking
    //----------------------------------------

    function set_up_client_data( $booking_id, $form )
    {

        $email = $this->get_form_value($form, 'qrr-email', 'sanitize_text_field');
        $name = $this->get_form_value($form, 'qrr-name', 'sanitize_deep_text_field');
        $phone = $this->get_first_type_value('phone', $form, 'sanitize_text_field');

        $client_id = QRR_Client_Model::set_up_new_client($email, $name, $phone);

        // Update booking list for the client
        //QRR_Client_Model::add_client_booking_list( $client_id, $booking_id );

        return $client_id;
    }


}
