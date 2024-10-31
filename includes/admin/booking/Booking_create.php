<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Booking_Create
{

    private $errors = array();

    public function __contruct()
    {

    }

    public function output()
    {

        $this->errors = array();

        if (isset($_GET['restaurant_id'])) {
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-create-booking-restaurant.php';
        } else {
            include QRR_PLUGIN_DIR . 'includes/admin/templates/template-create-booking.php';
        }

    }

    public function show_errors()
    {
        foreach ( $this->errors as $error ) {
            echo '<div class="error"><p>' . esc_html($error) . '</p></div>';
        }
    }
}