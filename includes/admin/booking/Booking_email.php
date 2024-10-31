<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Booking_Email
{

    private $booking_id = 0; // Booking post

    private $restaurant_id = 0;
    private $restaurant_model = null;

    private $post_booking = null;
    private $from_name = '';
    private $from_email = '';
    private $admin_email = '';
    private $customer_name = '';
    private $customer_email = '';

    private $headers = '';

    private $tags_content = null;


    public function __construct( $booking_id = 0 )
    {

        $this->booking_id = $booking_id;

        $this->init_fields();
    }


    function init_fields()
    {

        $this->post_booking = get_post($this->booking_id);
        $this->customer_email = get_post_meta($this->booking_id, 'qrr_email', true);
        $this->customer_name = $this->post_booking->post_title;

        $this->restaurant_id = intval(get_post_meta($this->booking_id, 'qrr_restaurant_id', true));
        $this->restaurant_model = new QRR_Restaurant_Model($this->restaurant_id);

        $this->from_name = $this->restaurant_model->get_from_name();
        $this->from_email = $this->restaurant_model->get_from_email();
        $this->admin_email = $this->restaurant_model->get_admin_email();

        $this->set_headers();
        $this->setup_tags_content();

		// Replace tags in from_email
	    $this->from_email = $this->filter_from_email_tags( $this->from_email );

        // Global for the templates
        global $qrr_email;
        $qrr_email = array();
        $qrr_email['header_img'] = get_post_meta($this->restaurant_id, 'qrr_email_logo', true);
        $qrr_email['heading'] = get_the_title($this->restaurant_id);
    }


    function set_headers()
    {

        $headers = "From: " . stripslashes_deep(html_entity_decode($this->from_name, ENT_COMPAT, 'UTF-8')) . " <" . apply_filters('qrr_email_header_from_email', get_option('admin_email')) . ">\r\n";
        $headers .= "Reply-To: =?utf-8?Q?" . quoted_printable_encode($this->from_name) . "?= <" . $this->from_email . ">\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        $this->headers = apply_filters('qrr_email_headers', $headers, $this);
    }


    // Depending on the status (pending, qrr-confirmed) can send a different template
    public function send_admin_email()
    {

        if (!isset($this->post_booking->post_status)) {
            return false;
        }

        $post_status = $this->post_booking->post_status;

        if ($post_status == 'pending') {

            $subject = $this->restaurant_model->get_email_subject_admin();
            $content = $this->restaurant_model->get_email_content_admin();

        } else if ($post_status == 'qrr-confirmed') {

            $subject = $this->restaurant_model->get_email_subject_admin_confirmed();
            $content = $this->restaurant_model->get_email_content_admin_confirmed();

        } else {
            return false;
        }

        // Filter the tags
        $subject = $this->filter_subject_tags($subject);
        $content = $this->filter_content_tags($content);

        // Message template
        $message = $this->get_template($content);

        // Send email
        $this->send_email($this->admin_email, $subject, $message, 'Adminstrator');
    }

    // Need this in ajax mode because is sending from the old status
    public function get_post_status()
    {
        global $wpdb;
        $sql = "SELECT post_status FROM {$wpdb->posts} WHERE ID={$this->booking_id}";
        return $wpdb->get_var($sql);
    }


    public function send_customer_email()
    {

        if (!isset($this->post_booking->post_status)) {
            return false;
        }

        $post_status = $this->post_booking->post_status;

        //if (defined('DOING_AJAX') && DOING_AJAX){
            //$post_status = $this->get_post_status();
        //}

        // Subject, Content
        if ($post_status == 'pending') {

            $subject = $this->restaurant_model->get_email_subject_pending();
            $content = $this->restaurant_model->get_email_content_pending();

        } else if ($post_status == 'qrr-confirmed') {

            $subject = $this->restaurant_model->get_email_subject_confirmed();
            $content = $this->restaurant_model->get_email_content_confirmed();

        } else if ($post_status == 'qrr-rejected') {

            $subject = $this->restaurant_model->get_email_subject_rejected();
            $content = $this->restaurant_model->get_email_content_rejected();

        } else {

            return false;
        }

        // Filter the tags
        $subject = $this->filter_subject_tags($subject);
        $content = $this->filter_content_tags($content);

        // Message template
        $message = $this->get_template($content);

        // Send email
        $this->send_email($this->customer_email, $subject, $message, 'Customer');
    }


    public function send_customer_email_update( $content )
    {

        $subject = $this->restaurant_model->get_email_subject_update();
        if (empty($subject)) {
            $subject = __('Your booking at ', 'qrr').get_bloginfo('name').' '.__('has been updated', 'qrr');
        }
        $content = wpautop($content);

        // Filter the tags
        $subject = $this->filter_subject_tags($subject);
        $content = $this->filter_content_tags($content);

        // Message template
        $message = $this->get_template($content);

        // Send email
        $this->send_email($this->customer_email, $subject, $message, 'Customer');

    }

    function get_template( $content )
    {

        ob_start();
        qrr_get_template_part('email/header');
        qrr_get_template_part('email/body');
        qrr_get_template_part('email/footer');
        $message = ob_get_clean();
        $message = str_replace('{content}', $content, $message);

        return $message;
    }


    // Send message
    //-----------------------------------------------------------

    public function send_email( $to, $subject, $message, $category = 'admin')
    {

        $sent = wp_mail($to, $subject, $message, $this->headers);

        $log_errors = apply_filters('qrr_log_email_errors', true, $to, $subject, $message);

        if (!$sent && true === $log_errors) {
            if (is_array($to)) {
                $to = implode(',', $to);
            }

            $remove_i18n = QRR()->settings->get('qrr_i18n');
            if ($remove_i18n) {
                $date_formatted = date('F j Y H:i:s', current_time('timestamp'));
            } else {
                $date_formatted = date_i18n('F j Y H:i:s', current_time('timestamp'));
            }

            $log_message = sprintf(
                __("Email from QRReservations failed to send.\nSend time: %s\nTo: %s\nSubject: %s\n\n", 'qrr'),
                $date_formatted,
                $to,
                $subject
            );

            error_log($log_message);

        }

        // Action
        do_action('qrr_email_sent', $sent, $category, $this->booking_id, $to, $subject, $message);

    }



    // Setup Tags content
    //--------------------------------------------------

    public function setup_tags_content()
    {

        $booking_model = new QRR_Booking_Model($this->booking_id);

        if (QRR_Active('capacity')) {
            $restaurant_link = get_permalink($this->restaurant_id);
        } else {
            $restaurant_link = site_url();
        }

        $this->tags_content = apply_filters(
            'qrr_email_tags', array(
            'restaurant'        => esc_html(get_the_title($this->restaurant_id)),
            //'restaurant_link'   => get_permalink( get_post_meta( $this->restaurant_id, 'qrr_email_restaurant_page', true ) ),
            'restaurant_link'   => $restaurant_link,

            'user_name'         => $booking_model->get_user_name(),
            'user_email'        => $booking_model->get_user_email(),

            'party'             => $booking_model->get_party(),
            'date'              => $booking_model->get_date_formatted(),
            'phone'             => $booking_model->get_phone(),
            //'message'           => $booking_model->get_message_from_custom_fields(),
            'fields'            => $booking_model->get_summary_custom_fields(),

            'site_name'         => get_bloginfo('name'),
            'site_link'         => get_site_url(),

            'bookings_link'     => '<a href="'.qrr_get_bookings_admin_url().'">'._x('Bookings link', 'Email link', 'qrr').'</a>',
            'confirm_link'      => '<a href="'.$booking_model->get_confirm_link_email() . '">' . _x('Confirm link', 'Email link', 'qrr') . '</a>',
            'reject_link'       => '<a href="'.$booking_model->get_reject_link_email() . '">' . _x('Reject link', 'Email link', 'qrr') . '</a>',
            'cancel_link'       => '<a href="'.$booking_model->get_cancel_link_email() . '">' . _x('Cancel link', 'Email link', 'qrr') . '</a>',

            'current_date'      => date(get_option('date_format'), current_time('timestamp')).' '.date(get_option('time_format'), current_time('timestamp')),
            ), $this->booking_id
        );

    }

    public function filter_subject_tags( $cadena )
    {

        $tags = $this->tags_content;
        $allowed = array('restaurant', 'user_name', 'party', 'date');
        foreach( $tags as $tag => $content ) {
            if (in_array($tag, $allowed)) {
                $cadena = str_replace('{'.$tag.'}', $content, $cadena);
            }
        }

        return $cadena;
    }

    public function filter_content_tags( $cadena )
    {

        $tags = $this->tags_content;
        foreach( $tags as $tag => $content ) {
            $cadena = str_replace('{'.$tag.'}', $content, $cadena);
        }

        return $cadena;
    }

	public function filter_from_email_tags( $cadena )
	{
		return str_replace('{user_email}', $this->tags_content['user_email'], $cadena);
	}

}
