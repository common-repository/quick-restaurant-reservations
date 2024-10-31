<?php

global $post;

$editor_settings = array(
    'media_buttons' => false,
    'quicktags'     => array("buttons"=>"link,img,close"),
    'textarea_name' => "",
    'tinymce'       => true,
);

$keys_normal = array(

    'qrr_email_replay_to_name' => array(
        'type' => 'text',
        'title' => __('Reply-To Name', 'qrr'),
        'desc' => __('The name displayed in the Reply-To field', 'qrr'),
        'placeholder' => get_bloginfo('name'),
    ),

    'qrr_email_replay_to_email' => array(
        'type' => 'text',
        'title' => __('Reply-To Email', 'qrr'),
        'desc' => __('The email to be used in the Reply-To field', 'qrr') .'. '.__('Tags: {user_email}', 'qrr'),
        'placeholder' => qrr_get_current_user_email(),
    ),

    'qrr_email_admin_email_send' => array(
        'type' => 'select',
        'title' => __('Send Email', 'qrr'),
        'desc' => __('Send email notification when new booking is requested to admin', 'qrr'),
        'options' => array( 'yes' => __('Yes', 'qrr'), 'no' => __('No', 'qrr') ),
    ),

    'qrr_email_admin_email' => array(
        'type' => 'text',
        'title' => __('Admin Email', 'qrr'),
        'desc' => __('Where admin notifications should be sent', 'qrr'),
        'placeholder' => qrr_get_current_user_email(),
    ),

    /*'qrr_email_restaurant_page' => array(
        'type' => 'select',
        'title' => __('Restaurant page', 'qrr'),
        'desc' => __('To be used in the notifications to the customer', 'qrr'),
        'placeholder' => '',
        'options' => qrr_get_pages()
    ),*/

    'qrr_email_logo' => array(
        'type' => 'fileupload',
        'title' => __('Logo URL', 'qrr'),
        'desc' => __('Logo to be shown at the top of the email (max width 150px)', 'qrr'),
        'placeholder' => 'http://....',
    ),

    'qrr_email_tags' => array(
        'type' => 'content',
        'title' => __('Template Tags', 'qrr'),
        'desc' => __('You can use these tags to add booking information to the email.<br>* you can use these inside the subject field', 'qrr'),
        'content' => 'file:template-email-tags.php'
    )
);

$keys_emails = array(

    'qrr_email_admin' => array(
        'description' => __('Email sent to the administrator when a new booking is requested.', 'qrr'),
        'subject' => array(
            'title' => __('Admin Email Subject', 'qrr'),
            'desc'  => __('Email subject sent to the administrator.', 'qrr'),
            'placeholder' => __('You have received a new booking request', 'qrr'),
        ),
        'content' => array(
            'title' => __('Admin Email Content', 'qrr'),
            'desc'  => __('Email content sent to the administrator when booking has been automatically confirmed.', 'qrr'),
            'placeholder' => '',
        )
    ),

    'qrr_email_admin_confirmed' => array(
        'description' => __('Email sent to the administrator when a new booking is automatically confirmed (Needs capacity add-on).', 'qrr'),
        'subject' => array(
            'title' => __('Admin Email Subject', 'qrr'),
            'desc'  => __('Email subject sent to the administrator.', 'qrr'),
            'placeholder' => __('You have received a new booking already confirmed', 'qrr'),
        ),
        'content' => array(
            'title' => __('Admin Email Content', 'qrr'),
            'desc'  => __('Email content sent to the administrator to Confirm/Reject/Cancel booking.', 'qrr'),
            'placeholder' => '',
        )
    ),

    'qrr_email_pending' => array(
        'description' => __('Email sent to the customer when the booking is Pending.', 'qrr'),
        'subject' => array(
            'title' => __('Pending Email Subject', 'qrr'),
            'desc'  => __('The email subject a user will receive when requesting a new booking.', 'qrr'),
            'placeholder' => __('Your booking at ', 'qrr').get_bloginfo('name').' '.__('is pending', 'qrr'),
        ),
        'content' => array(
            'title' => __('Pending Email', 'qrr'),
            'desc'  => __('The email subject a user will receive when requesting a new booking.', 'qrr'),
            'placeholder' => '',
        )
    ),

    'qrr_email_confirmed' => array(
        'description' => __('Email sent to the customer when the booking is Confirmed.', 'qrr'),
        'subject' => array(
            'title' => __('Confirmed Email Subject', 'qrr'),
            'desc'  => __('The email subject a user will receive the booking is confirmed.', 'qrr'),
            'placeholder' => __('Your booking at ', 'qrr').get_bloginfo('name').' '.__('has been confirmed', 'qrr'),
        ),
        'content' => array(
            'title' => __('Confirmed Email', 'qrr'),
            'desc'  => __('The email content a user will receive the booking has been confirmed.', 'qrr'),
            'placeholder' => '',
        )
    ),

    'qrr_email_rejected' => array(
        'description' => __('Email sent to the customer when the booking is Rejected.', 'qrr'),
        'subject' => array(
            'title' => __('Rejected Email Subject', 'qrr'),
            'desc'  => __('The email subject a user will receive the booking has been rejected.', 'qrr'),
            'placeholder' => __('Your booking at ', 'qrr').get_bloginfo('name').' '.__('has been rejected', 'qrr'),
        ),
        'content' => array(
            'title' => __('Rejected Email', 'qrr'),
            'desc'  => __('The email content a user will receive the booking has been rejected.', 'qrr'),
            'placeholder' => '',
        )
    ),

    'qrr_email_update' => array(
        'description' => __('Custom email subject sent to the customer from the bookings admin panel.', 'qrr'),
        'subject' => array(
            'title' => __('Update Email Subject', 'qrr'),
            'desc'  => '',
            'placeholder' => __('Your booking at ', 'qrr').get_bloginfo('name').' '.__('has been updated', 'qrr'),
        )
    ),


);


echo '<table class="table-fields">';

foreach( $keys_normal as $key => $item ) {

    // Normal field
    if (isset($item['type'])) {

        $value = get_post_meta($post->ID, $key, true);

        if ($item['type'] == 'text' ) {

            echo '<tr>';
            echo '<td><div class="field-title">'.esc_html($item['title']).'</div></td>';
            echo '<td>';
            echo '<input type="text" class="text-medium" name="'.esc_attr($key).'" value="'.esc_attr($value).'" placeholder="'.esc_attr($item['placeholder']).'">';
            echo '<div class="qrr-settings-desc">'.esc_html($item['desc']).'</div>';
            echo '</td>';
            echo '</tr>';
        }

        else if ($item['type'] == 'select' ) {

            echo '<tr>';
            echo '<td><div class="field-title">'.esc_html($item['title']).'</div></td>';
            echo '<td>';
            echo '<select name="'.esc_attr($key).'">';
            foreach($item['options'] as $key_option => $name_option) {
                $selected = selected($key_option, $value);
                echo '<option '.esc_attr($selected).' value="'.esc_attr($key_option).'">'.esc_html($name_option).'</option>';
            }
            echo '</select>';
            echo '<div class="qrr-settings-desc">'.esc_html($item['desc']).'</div>';
            echo '</td>';
            echo '</tr>';

        } else if ($item['type'] == 'content' ) {

            echo '<tr>';
            echo '<td><div class="field-title">'.esc_html($item['title']).'</div></td>';
            echo '<td>';

			if (preg_match('#file:(.+)#', $item['content'], $matches))
			{
				$file_name = $matches[1];
		        ob_start();
		        require $file_name;
		        echo ob_get_clean();
			}

            echo '<div class="qrr-settings-desc">'.esc_html($item['desc']).'</div>';
            echo '</td>';
            echo '</tr>';

        } else if ($item['type'] == 'fileupload' ) {

            echo '<tr>';
            echo '<td><div class="field-title">'.esc_html($item['title']).'</div></td>';
            echo '<td>';

            if (QRR_Active('capacity')) {
                echo '<div class="fileupload">';
                if (!empty($value) ) {
                    $img = '<img src="'.esc_url($value).'">';
                }else {
                    $img = '';
                }
                echo '<input id="'.esc_attr($key).'" name="'.esc_attr($key).'" type="hidden" value="'.esc_attr($value).'">';
                echo '<input id="'.esc_attr($key).'_button" readonly class="button" value="'.__('Upload', 'qrr').'">';
                echo '<div class="preview-image">'.esc_html($img).'</div>';
                echo '</div>';

                echo '<div class="qrr-settings-desc">'.esc_html($item['desc']).'</div>';
            } else {
                include QRR_PLUGIN_DIR.'includes/admin/template-addons/addon-capacity.php';
            }


            echo '</td>';
            echo '</tr>';

        }

    }

}

echo '</table>';



// Tabs with subject and content for each type of email
$tabs = array(
    'qrr_email_admin' => 'Admin pending',
    'qrr_email_admin_confirmed' => 'Admin confirmed',
    'qrr_email_pending' => 'Client Pending',
    'qrr_email_confirmed' => 'Client Confirmed',
    'qrr_email_rejected' => 'Client Rejected',
    'qrr_email_update' => 'Client Update',
);

echo '<div id="qrr-email-tabs">';


echo '<div class="qrr-tabs">';
$index = 0;
foreach( $tabs as $tab_key => $tab_title ) {
    $class = $index++ == 0 ? 'active' : '';
    echo '<a href="#'.esc_attr($tab_key).'" class="'.esc_attr($class).'">'.esc_html($tab_title).'</a>';
}
echo '</div>';

echo '<div class="qrr-tabs-content">';
$index = 0;
foreach( $tabs as $tab_key => $tab_title ) {
    $class = $index++ == 0 ? 'active' : '';
    echo '<div id="'.esc_attr($tab_key).'" class="qrr-tab-content '.esc_attr($class).'">';
    echo '<table class="table-fields">';
    qrr_display_tab_content($keys_emails, $tab_key);
    echo '</table>';
    echo '</div>';
}
echo '</div>';


function qrr_display_tab_content( $keys_emails, $key )
{

    global $post;

    $titles = $keys_emails[$key];

    if (isset($titles['description'])) {
        echo '<tr>';
        echo '<td colspan="2"><strong>'.esc_html($titles['description']).'</strong></td>';
        echo '</tr>';
    }

    // Subject
    if (isset($titles['subject'])) {

        $subject = get_post_meta($post->ID, $key . '_subject', true);
        echo '<tr>';
        echo '<td><div class="field-title">'.esc_html($titles['subject']['title']).'</div></td>';
        echo '<td>';
        echo '<input type="text" class="text-medium" name="'.esc_attr($key).'_subject" value="'.esc_attr($subject).'" placeholder="'.esc_attr($titles['subject']['placeholder']).'">';
        echo '<div class="qrr-settings-desc">'.esc_html($titles['subject']['desc']).'</div>';
        echo '</td>';
        echo '</tr>';
    }

    // Content
    if (isset($titles['content'])) {

        $content = get_post_meta($post->ID, $key . '_content', true);
        //if (empty($content)) {
        //    $content = $titles['content']['placeholder'];
        //}
        if (empty($content)) {
            ob_start();
            include 'template-email-' . str_replace('qrr_email_', '', $key) . '.php';
            $content = ob_get_clean();
        }
        echo '<tr>';
        echo '<td><div class="field-title">'.esc_html($titles['content']['title']).'</div></td>';
        $editor_settings['textarea_name'] = $key.'_content';
        echo '<td>';

        /**/

        //echo '<pre>$key '; print_r( $key ); echo '</pre>';
        wp_editor(htmlspecialchars_decode($content), $key . '_content', $editor_settings);

        echo '<div class="qrr-settings-desc">'.esc_html($titles['content']['desc']).'</div>';
        echo '</td>';
        echo '</tr>';
    }

}


