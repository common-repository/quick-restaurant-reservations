<?php

$tags = array(
        'restaurant'        => __('*Restaurant name', 'qrr'),
        'restaurant_link'   => __('Custom page of the restaurant (with Add-On capacity)', 'qrr'),

        'user_name'         => __('*Name of the customer', 'qrr'),
        'user_email'        => __('Email of the customer', 'qrr'),

        'party'             => __('*Number of people booked', 'qrr'),
        'date'              => __('*Date of the booking', 'qrr'),
        'phone'             => __('Phone number', 'qrr'),
        //'message'           => __('Message added','qrr'),
        'fields'            => __('Custom fields added to the request', 'qrr'),

        'site_name'         => __('Name of this website', 'qrr'),
        'site_link'         => __('Name of this website', 'qrr'),

        'bookings_link'     => __('Link to the admin panel (only for admin notifications)', 'qrr'),
        'confirm_link'      => __('Link to confirm this booking (only for admin notifications)', 'qrr'),
        'reject_link'       => __('Link to reject this booking (only for admin notifications)', 'qrr'),
        'cancel_link'       => __('Link to cancel this booking (only for admin notifications)', 'qrr'),

        'current_date'      => __('Current date and time', 'qrr'),
    );

echo '<div class="qrr-email-tags">';

foreach( $tags as $tag_key => $tag_desc ) {
    echo '<div class="qrr-email-tag">';
        echo '<span class="tag-tag">{'.esc_html($tag_key).'}</span>';
        echo '<span class="tag-desc">'.esc_html($tag_desc).'</span>';
    echo '</div>';
}

echo '</div>';
