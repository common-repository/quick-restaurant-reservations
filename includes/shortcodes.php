<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

//----------------------------------
// Shortcode Booking Form
//----------------------------------
add_shortcode('qrr_form', 'qrr_shortcode_form');

function qrr_shortcode_form( $atts, $content = null )
{

    // In case want to use with the qrr_restaurant post type in the future
    global $post;
    $post_id = is_object($post) ? $post->ID : 0;

    $atts = shortcode_atts(
        array(
        'id' => $post_id
        ), $atts
    );

    $post_id = $atts['id'];
    if (get_post_type($post_id) != 'qrr_restaurant' ) { return '';
    }

    global $qrr_post_id;
    $qrr_post_id = $post_id;

    // Template
    ob_start();
    //qrr_get_template_part('front','form');
    qrr_get_template_part('front', 'form');
    return ob_get_clean();

}
