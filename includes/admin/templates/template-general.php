<?php
global $post;

$value_general = get_post_meta($post->ID, 'qrr_booking_general', true);


function qrr_options_min_max($min = 1, $max = 10)
{
    $options = array();
    for( $i = $min; $i <= $max; $i++ ){
        $options[] = array('value' => $i, 'title' => $i);
    }
    return $options;
}

function qrr_options_party_min()
{
    $options = array();
    $min_party = apply_filters('qrr_min_party_options', 20);
    for ($i = 1; $i <= $min_party; $i++) {
        $options[] = array('value' => $i, 'title' => $i);
    }
    return $options;
}

function qrr_options_party_max()
{
    $options = array();
    $max_party = apply_filters('qrr_max_party_options', 50);
    for ($i = 1; $i <= $max_party; $i++) {
        $options[] = array('value' => $i, 'title' => $i);
    }
    return $options;
}

function qrr_options_transform( $options_t)
{
    $options = array();
    foreach($options_t as $key => $title){
        $options[] = array('value' => $key, 'title' => $title);
    }
    return $options;
}

function qrr_options_early_bookings()
{
    $options = array(
        1 => _x('From 1 day in advance', 'Early booking', 'qrr'),
        2 => _x('From 2 days in advance', 'Early booking', 'qrr'),
        3 => _x('From 3 days in advance', 'Early booking', 'qrr'),
        4 => _x('From 4 days in advance', 'Early booking', 'qrr'),
        5 => _x('From 5 days in advance', 'Early booking', 'qrr'),
        6 => _x('From 6 days in advance', 'Early booking', 'qrr'),
        7 => _x('From 7 days in advance', 'Early booking', 'qrr'),
        14 => _x('From 14 days in advance', 'Early booking', 'qrr'),
        30 => _x('From 30 days in advance', 'Early booking', 'qrr'),
        90 => _x('From 90 days in advance', 'Early booking', 'qrr'),
        180 => _x('From 180 days in advance', 'Early booking', 'qrr'),
        365 => _x('From 1 year in advance', 'Early booking', 'qrr'),
        730 => _x('From 2 years in advance', 'Early booking', 'qrr')
    );
    return apply_filters('qrr_options_early_bookings', qrr_options_transform($options));
}

function qrr_options_late_bookings()
{
    $options = array(
        '0m' => _x('Up to the last minute', 'Late booking', 'qrr'),
        '15m' => _x('At least 15 minutes in advance', 'Late booking', 'qrr'),
        '30m' => _x('At least 30 minutes in advance', 'Late booking', 'qrr'),
        '45m' => _x('At least 45 minutes in advance', 'Late booking', 'qrr'),
        '1h' => _x('At least 1 hour in advance', 'Late booking', 'qrr'),
        '2h' => _x('At least 2 hours in advance', 'Late booking', 'qrr'),
        '3h' => _x('At least 3 hours in advance', 'Late booking', 'qrr'),
        '4h' => _x('At least 4 hours in advance', 'Late booking', 'qrr'),
        '24h' => _x('At least 24 hours in advance', 'Late booking', 'qrr'),
        '1d' => _x('At least from the day before', 'Late booking', 'qrr'),
        '2d' => _x('At least from the 2 days before', 'Late booking', 'qrr'),
        '3d' => _x('At least from the 3 days before', 'Late booking', 'qrr')
    );
    return apply_filters('qrr_options_late_bookings', qrr_options_transform($options));
}

function qrr_options_date_format()
{
    $options_t = array(
        'mmmm d,yyyy',
        'dddd, dd mmm, yyyy',
        'dd mmm, yyyy',
        'dd mmmm, yyyy',
        'mm/dd/yyyy',
        'dd/mm/yyyy',
    );
    $options = array();
    foreach($options_t as $key){
        $options[] = array('value' => $key, 'title' => $key);
    }
    return apply_filters('qrr_options_date_format', $options);
}

function qrr_options_hour_format()
{
    $options_t = array(
        __('24 hours', 'qrr') => '24h',
        __('AM/PM', 'qrr') => '12h',
    );
    $options = array();
    foreach($options_t as $title => $value){
        $options[] = array('value' => $value, 'title' => $title);
    }
    return apply_filters('qrr_options_hour_format', $options);
}

function qrr_options_first_day_week()
{
    $options = array(
        array('value' => 0, 'title' => __('Sunday', 'qrr')),
        array('value' => 1, 'title' => __('Monday', 'qrr'))
    );
    return apply_filters('qrr_options_form_first_day', $options);
}

function qrr_options_pages()
{
    $options = qrr_options_transform(qrr_get_pages());
    return array_merge(array(array('value'=> '','title' => __('None', 'qrr'))), $options);
}


// Define the fields
//------------------------------------

$fields = array(
    'min_party' => array(
        'type' => 'select',
        'title' => __('Min Party', 'qrr'),
        'desc' => _x('Min party size allowed in the booking form.', 'Settings', 'qrr'),
        'options' => qrr_options_party_min(),
        'value' => isset($value_general['min_party']) ? $value_general['min_party'] : 0
    ),
    'max_party' => array(
        'type' => 'select',
        'title' => __('Max Party', 'qrr'),
        'desc' => _x('Max party size allowed in the booking form.', 'Settings', 'qrr'),
        'options' => qrr_options_party_max(),
        'value' => isset($value_general['max_party']) ? $value_general['max_party'] : 20
    ),
    'early' => array(
        'type' => 'select',
        'title' => __('Early Booking', 'qrr'),
        'desc' => _x('How early customers can make their booking.', 'Settings', 'qrr'),
        'options' => qrr_options_early_bookings(),
        'value' => isset($value_general['early']) ? $value_general['early'] : 0
    ),
    'late' => array(
        'type' => 'select',
        'title' => __('Late Booking', 'qrr'),
        'desc' => _x('How late customers can make their booking.', 'Settings', 'qrr'),
        'options' => qrr_options_late_bookings(),
        'value' => isset($value_general['late']) ? $value_general['late'] : 0
    ),

    'date_format' => array(
        'type' => 'select',
        'title' => __('Form Date Format', 'qrr'),
        'desc' => _x('Change date format used in the booking form.', 'Settings', 'qrr'),
        'options' => qrr_options_date_format(),
        'value' => isset($value_general['date_format']) ? $value_general['date_format'] : 'dddd, dd mmm, yyyy'
    ),

    'hour_format' => array(
        'type' => 'select',
        'title' => __('Form Hour Format', 'qrr'),
        'desc' => _x('Change hour format used in the booking form.', 'Settings', 'qrr'),
        'options' => qrr_options_hour_format(),
        'value' => isset($value_general['hour_format']) ? $value_general['hour_format'] : '24h'
    ),


    'first_day' => array(
        'type' => 'select',
        'title' => __('First Day of the Week', 'qrr'),
        'desc' => _x('Select first day of the week.', 'Settings', 'qrr'),
        'options' => qrr_options_first_day_week(),
        'value' => isset($value_general['first_day']) ? $value_general['first_day'] : 0
    ),
    'message_not_available_hours' => array(
        'type' => 'textarea',
        'title' => __('Hours not available message', 'qrr'),
        'desc' => _x('Enter the message to display when there are no available hours. <br>You can use the tags {date} and {party}', 'Settings', 'qrr'),
        'value' => isset($value_general['message_not_available_hours']) ? $value_general['message_not_available_hours'] : __('Sorry, no hours available for date {date} and party {party}.', 'qrr')
    ),
    'message_pending' => array(
        'type' => 'textarea',
        'title' => __('Pending Message', 'qrr'),
        'desc' => _x('Enter the message to display when a booking request is made. <br>You can use the tag {booking_details} to display the booking data to the user.', 'Settings', 'qrr'),
        'value' => isset($value_general['message_pending']) ? $value_general['message_pending'] : __('Thanks, your booking request is waiting to be confirmed. You will receive updates in the email address you have provided.{booking_details}', 'qrr')
    ),
    'redirect_page_pending' => array(
        'type' => 'select',
        'title' => __('Redirect Page (Pending)', 'qrr'),
        'desc' => _x('Select the page to redirect after the booking is submitted.', 'Settings', 'qrr'),
        'options' => apply_filters('qrr_options_redirect_page_pending', qrr_options_pages()),
        'value' => isset($value_general['redirect_page_pending']) ? $value_general['redirect_page_pending'] : ''
    ),
    'message_confirmed' => array(
        'type' => 'textarea',
        'title' => __('Confirmed Message', 'qrr'),
        'desc' => _x('Enter the message to display when a booking is confirmed automatically. <br>You can use the tag {booking_details} to display the booking data to the user.', 'Settings', 'qrr'),
        'value' => isset($value_general['message_confirmed']) ? $value_general['message_confirmed'] : __('Thanks, your booking request has been confirmed.You will receive updates in the email address you have provided.{booking_details}', 'qrr')
    ),
    'redirect_page_confirmed' => array(
        'type' => 'select',
        'title' => __('Redirect Page (Confirmed)', 'qrr'),
        'desc' => _x('Select the page to redirect after the booking is confirmed.', 'Settings', 'qrr'),
        'options' => apply_filters('qrr_options_redirect_page_confirmed',  qrr_options_pages()),
        'value' => isset($value_general['redirect_page_confirmed']) ? $value_general['redirect_page_confirmed'] : ''
    ),
    'message_not_available' => array(
        'type' => 'textarea',
        'title' => __('Not available Message', 'qrr'),
        'desc' => _x('Enter the message to display when a booking is not available.', 'Settings', 'qrr'),
        'value' => isset($value_general['message_not_available']) ? $value_general['message_not_available'] : __("We cannot process your booking request just now, we don't have available seats. Please try another date.", 'qrr')
    ),
    'redirect_page_seconds' => array(
        'type' => 'select',
        'title' => __('Redirect after X seconds', 'qrr'),
        'desc' => _x('seconds', 'Settings', 'qrr'),
        'options' => qrr_options_min_max(1, 10),
        'value' => isset($value_general['redirect_page_seconds']) ? $value_general['redirect_page_seconds'] : 5
    ),


);

// Shortcode INFO
//------------------------------------

if (!QRR_Active('capacity')) {
    $info = __('Use this shortcode {shortcode} inside any page content.', 'qrr');
    $info .= '<br>'.__('You can also display a custom page for this restaurant: ', 'qrr');
    ob_start();
    include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
    $info .= ob_get_clean();
} else {
    $info = __('Use the shortcode {shortcode} inside the content editor to display the form. You can also use it in any other page content.', 'qrr');
}

$string = str_replace('{shortcode}', '[qrr_form id="'.$post->ID.'"]', $info);

echo '<p>'.esc_html($string).'</p><hr>';

// Start table
//------------------------------------
echo '<table class="table-fields">';



// Fields
//------------------------------------
foreach( $fields as $key_field => $field ) {

    if ($key_field == 'separation') {
        echo '</table>';
        echo '<hr class="table-separation">';
        echo '<table class="table-fields">';
    }

    else {
        echo '<tr>';
        echo '<td><div class="field-title">'.esc_html($field['title']).'</div></td>';
        echo '<td>';

        switch ($field['type']){

        case 'select':
            echo '<select name="qrr_booking_general['.esc_attr($key_field).']">';
            foreach($field['options'] as $option){
                $selected = selected($field['value'], $option['value']);
                echo '<option value="'.esc_attr($option['value']).'" '.esc_attr($selected).'>'.esc_html($option['title']).'</option>';
            }
            echo '</select>';
            break;

        case 'textarea':
            echo '<textarea name="qrr_booking_general['.esc_attr($key_field).']">'.wp_kses_post($field['value']).'</textarea>';
            break;

        default:
            break;
        }

        echo '<div class="qrr-settings-desc">'.esc_html($field['desc']).'</div></td>';
        echo '</tr>';
    }

}



// End table
//------------------------------------
echo '</table>';
