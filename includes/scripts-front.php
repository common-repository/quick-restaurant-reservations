<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


/**
 * Load Scripts front End
 *
 * @since   1.0
 * @updated 1.1
 */
function qrr_load_front_scripts()
{

    global $wp_version, $post;

    if (!isset($post->post_content) ) { return;
    }

    $js_dir  = QRR_PLUGIN_URL . 'assets/js/';
    $css_dir = QRR_PLUGIN_URL . 'assets/css/';
    $lib_dir = QRR_PLUGIN_URL . 'assets/libs/';

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';


    //if ( has_shortcode( $post->post_content, 'qrr_form')  ) {

        wp_enqueue_style('pickadate', $lib_dir.'pickadate/themes/default.css', array(), QRR_VERSION);
        wp_enqueue_style('pickadate-date', $lib_dir.'pickadate/themes/default.date.css', array(), QRR_VERSION);

        wp_enqueue_style('qrr-front-style', $css_dir.'qrr-front-style.css', array(), QRR_VERSION);

        wp_register_script('picker', $lib_dir.'pickadate/picker.js', array(), QRR_VERSION, true);
        wp_register_script('pickadate', $lib_dir.'pickadate/picker.date.js', array('picker'), QRR_VERSION, true);
        wp_register_script('pickatime', $lib_dir.'pickadate/picker.time.js', array('picker'), QRR_VERSION, true);

        // Language pickadate
        $locale = get_locale();
        $locale_url_file = apply_filters('qrr_pickadate_locale_file_js', $lib_dir.'pickadate/translations/'.$locale.'.js');
        wp_register_script('picker-locale', $locale_url_file, array(), QRR_VERSION, true);

        wp_register_script('vuejs', $lib_dir.'vue/vue'.$suffix.'.js', array(), '2.4.2', true);
        wp_register_script('vue-components', $js_dir.'vue-components-front'.'.js', array('vuejs','pickadate', 'pickatime'), QRR_VERSION, true);

        wp_register_script('qrr-front-script', $js_dir . 'front-script.js', array( 'jquery','pickadate', 'pickatime', 'vue-components' ), QRR_VERSION, true);
    //}

}

add_action('wp_enqueue_scripts', 'qrr_load_front_scripts');