<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


add_action('admin_enqueue_scripts', 'qrr_load_admin_scripts', 20);


function qrr_load_admin_scripts( $hook )
{

    global $wp_version, $post, $pagenow, $typenow;

    $js_dir = QRR_PLUGIN_URL . 'assets/js/';
    $css_dir = QRR_PLUGIN_URL . 'assets/css/';
    $lib_dir = QRR_PLUGIN_URL . 'assets/libs/';

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

    if (isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'qrr_create_booking' ) {

        wp_enqueue_style('pickadate', $lib_dir.'pickadate/themes/default.css', array(), QRR_VERSION);
        wp_enqueue_style('pickadate-date', $lib_dir.'pickadate/themes/default.date.css', array(), QRR_VERSION);

        wp_enqueue_style('qrr-front-style', $css_dir.'qrr-front-style.css', array(), QRR_VERSION);

        wp_register_script('picker', $lib_dir.'pickadate/picker.js', array(), QRR_VERSION, true);
        wp_register_script('pickadate', $lib_dir.'pickadate/picker.date.js', array('picker'), QRR_VERSION, true);
        wp_register_script('pickatime', $lib_dir.'pickadate/picker.time.js', array('picker'), QRR_VERSION, true);

        wp_register_script('he', $lib_dir.'he/he.js', array(), '1.1.1', true);
        wp_register_script('vuejs', $lib_dir.'vue/vue'.$suffix.'.js', array(), '2.4.2', true);
        wp_register_script('vue-components', $js_dir.'vue-components-front'.'.js', array('vuejs','pickadate', 'pickatime', 'he'), QRR_VERSION, true);

        // For creating booking
        wp_register_script('admin-create-qrr_booking', $js_dir.'admin-create-qrr_booking.js', array('jquery','pickadate', 'pickatime' , 'vuejs','vue-components'), QRR_VERSION, true);

    }


}
