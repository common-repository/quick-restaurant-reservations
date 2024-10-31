<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Booking_Validation
{

    public $restaurant_id = 0;

    function __construct( $restaurant_id )
    {
        $this->restaurant_id = $restaurant_id;
    }

    public function validate( $form )
    {

        $errors = array();

        // Check standard fields
        $email = '';
        foreach($form as $field){

            switch ($field['name']) {

            case 'qrr-name':
                $name = wp_strip_all_tags(sanitize_text_field(stripslashes_deep($field['value'])), true);
                $result = $this->is_valid_name($name);
                break;

            case 'qrr-email':
                $email = trim(sanitize_text_field($field['value']));
                $result = $this->is_valid_email($email);
                break;

            default:
                $result = true;
                break;
            }

            if (is_wp_error($result)) {
                $errors[ $field['name'] ] = $result->get_error_message('validation');
            }
        }

        // Check required fields apart from standard
        $form_requested_fields = json_decode(get_post_meta($this->restaurant_id, 'qrr_booking_fields', true), ARRAY_A);

        $list_types = array('phone','text','textarea','select','checkbox','radio');

        foreach( $form_requested_fields as $r_field ) {

            if (in_array($r_field['type'], $list_types) ) {

                // Check is required
                if ($r_field['required'] && $this->is_field_empty($r_field, $form)) {

                    $text_error = !empty($r_field['error']) ? $r_field['error'] : __('This field is required', 'qrr');
                    $errors[ $r_field['id'] ] = $text_error;

                }

                // Check the type phone
                if ($r_field['type'] == 'phone') {
                    foreach( $form as $field ) {
                        if ($r_field['id'] == $field['name'] ) {
                            $phone = wp_strip_all_tags(sanitize_text_field(stripslashes_deep($field['value'])), true);
                            $result = $this->is_valid_phone($phone);
                            if (is_wp_error($result)) {
                                $errors[ $field['name'] ] = $result->get_error_message('validation');
                            }
                        }
                    }
                }

            }
        }

        // Validate banned IP
        $result = $this->is_valid_banned_ip($_SERVER['REMOTE_ADDR']);
        if (is_wp_error($result)) {
            $errors[ 'banned_ip' ] = $result->get_error_message('validation');
        }

        // Validate banned EMAIL
        $result = $this->is_valid_banned_email($email);
        if (is_wp_error($result)) {
            $errors[ 'banned_email' ] = $result->get_error_message('validation');
        }

        return apply_filters('qrr_validation_errors', $errors, $this->restaurant_id, $form, $form_requested_fields);
    }

    private function is_field_empty( $requested_field, $form_fields )
    {

        foreach( $form_fields as $field ) {
            if ($requested_field['id'] == $field['name'] ) {
                $value = wp_strip_all_tags(sanitize_text_field(stripslashes_deep($field['value'])), true);
                return empty($value);
            }
        }

        return true;
    }

    private function is_valid_name( $name )
    {

        //@TODO name validation
        if (!empty($name) && strlen($name) > 2) {
            return true;
        }
        return new WP_Error('validation', _x('Invalid Name. Should have at least 3 characters.', 'validation', 'qrr'));
    }


    private function is_valid_email( $email )
    {

        //@TODO check if user already exists
        if (is_email($email)) {
            return true;
        }
        return new WP_Error('validation', _x('Invalid Email', 'validation', 'qrr'));
    }

    private function is_valid_phone( $phone )
    {

        $regex = apply_filters('qrr_phone_validation_regex', '#.{8,15}#');
        if (preg_match($regex, $phone)) {
            return true;
        }
        return new WP_Error('validation', _x('Invalid Phone.', 'validation', 'qrr'));
    }

    private function is_valid_banned_ip( $ip )
    {

        $banned_ip = preg_split('/\r\n|\r|\n/', (string) QRR()->settings->get('qrr_banned_ip'));
        if (in_array($ip, $banned_ip)) {
            $message = QRR()->settings->get('qrr_banned_message', _x('We cannot process your request. Please contact us by email or phone.', 'validation', 'qrr'));
            return new WP_Error('validation', $message);
        }

        return true;
    }

    private function is_valid_banned_email( $email )
    {

        $banned_emails = preg_split('/\r\n|\r|\n/', (string) QRR()->settings->get('qrr_banned_email'));
        if (in_array($email, $banned_emails)) {
            $message = QRR()->settings->get('qrr_banned_message', _x('We cannot process your request. Please contact us by email or phone.', 'validation', 'qrr'));
            return new WP_Error('validation', $message);
        }

        return true;
    }



}