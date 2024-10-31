<?php


    /**
     * Class QRR_Settings
     *
     * @since 1.0
     */
class QRR_Settings
{

    private $options;
    private $set_name;
    public function __construct( $name = 'qrr_settings' )
    {
        $this->set_name = $name;
        $this->options = get_option($this->set_name, array());
        add_action('admin_init', array( $this, 'register_settings'));
    }

    public function register_settings()
    {

        if (false == get_option($this->set_name) ) {
            add_option($this->set_name);
        }

        foreach( $this->list_of_settings() as $tab => $settings ) {

            // Manage tab with several sections inside (name_) underscore at the end means sub settings
            if (preg_match('/.+_$/', $tab)) {

                foreach($settings as $sec=>$sub_settings) {
                    $this->add_settings_section($tab.$sec);
                    $this->add_settings_fields($tab.$sec, $sub_settings);
                }
            }
            else {
                $this->add_settings_section($tab);
                $this->add_settings_fields($tab, $settings);
            }
        }

        register_setting($this->set_name, $this->set_name, array( $this, 'sanitize'));
    }

    public function add_settings_section( $tab )
    {

        add_settings_section(
            $this->set_name . '_' . $tab,
            __return_null(),
            '__return_false',
            $this->set_name . '_' . $tab
        );
    }

    public function add_settings_fields( $tab, $settings )
    {

        foreach( $settings as $key => $option ) {

            $name = isset($option['name']) ? $option['name'] : '';

            // Check first it's a function already exists
            if (isset($option['function']) && function_exists($option['function']) ) {
                $callback = $option['function'];
            }

            // Function inside this calss
            else {
                $callback = is_callable(array($this, 'show_'.$option['type'])) ? array($this, 'show_'.$option['type']) : array($this, 'show_missing');
            }


            add_settings_field(
                $this->set_name . '[' . $key . ']',
                $name,
                $callback,
                $this->set_name . '_' . $tab, // Page
                $this->set_name . '_' . $tab, // Section
                array(
                    'id'      => $key,
                    'desc'    => ! empty($option['desc']) ? $option['desc'] : '',
                    'name'    => isset($option['name']) ? $option['name'] : null,
                    'section' => $tab,
                    'size'    => isset($option['size']) ? $option['size'] : null,
                    'options' => isset($option['options']) ? $option['options'] : '',
                    'std'     => isset($option['std']) ? $option['std'] : '',
                    'callback'=> isset($option['callback']) ? $option['callback'] : '',
                    'options' => isset($option['options']) ? $option['options'] : ''
                )
            );
        }
    }

    public function list_of_settings()
    {

        $list = array(
            /*'license' => apply_filters( 'qrr_settings_license',
                array(
                    'qrr_pro_license_key' => array(
                        'name' => __( 'License key', 'qrr' ),
                        'desc' => __( 'Enter your license key.' , 'qrr' ),
                        'type' => 'text',
                        'size' => 'regular',
                        'callback' => array( $this, 'sanitize_license')
                    ),
                    'qrr_pro_license_status' => array(
                        'name' => __( 'Activate license', 'qrr' ),
                        'desc' => __( '' , 'qrr' ),
                        'type' => 'activate_license',
                        'size' => 'regular',
                        'std' => ''
                    ),
                )
            ),*/
            'bookings' => apply_filters(
	            'qrr_settings_bookings',
               array(
	               'qrr_booking_skip_admin_email_nonce' => array(
		               'name' => __('Prevent nonce check with admin email links', 'qrr'),
		               'desc' => __('Mark this in case you are receiving the message "Email action not allowed"', 'qrr'),
		               'type' => 'checkbox',
		               'size' => '',
		               'std' => false
	               ),
	               'qrr_booking_edit_use_clients' => array(
		               'name' => __('Edit booking. <br>Load the List of clients', 'qrr'),
		               'desc' => __('If you have many clients this can delay the loading for editing the booking.', 'qrr'),
		               'type' => 'checkbox',
		               'size' => '',
		               'std' => false
	               ),
	               'qrr_booking_new_use_clients' => array(
		               'name' => __('New booking. <br>Load the List of clients', 'qrr'),
		               'desc' => __('If you have many clients this can delay the loading for creating the booking.', 'qrr'),
		               'type' => 'checkbox',
		               'size' => '',
		               'std' => false
	               )
               )
            ),
            'general' => apply_filters(
                'qrr_settings_general',
                array(
                    'qrr_banned_email' => array(
                        'name' => __('Banned Email Addresses', 'qrr'),
                        'desc' => __('Block bookings from specific email addresses. Enter each email address on a separate line.', 'qrr'),
                        'type' => 'textarea',
                        'size' => ''
                    ),
                    'qrr_banned_ip' => array(
                        'name' => __('Banned IP Addresses', 'qrr'),
                        'desc' => __('Block bookings from specific IP addresses. Enter each IP address on a separate line.', 'qrr'),
                        'type' => 'textarea',
                        'size' => ''
                    ),
                    'qrr_banned_message' => array(
                        'name' => __('Banned Message', 'qrr'),
                        'desc' => __('Show this message when user is banned', 'qrr'),
                        'type' => 'textarea',
                        'size' => '',
                        'std' => _x('We cannot process your request. Please contact us by email or phone.', 'validation', 'qrr')
                    ),
                    'qrr_i18n' => array(
                        'name' => __('Remove i18n date for emails', 'qrr'),
                        'desc' => __('', 'qrr'),
                        'type' => 'checkbox',
                        'size' => '',
                        'std' => false
                    )
                )
            ),

        );

        return apply_filters('qrr_registered_settings', $list);
    }

    public function get( $key , $default = false )
    {
        return empty($this->options[$key]) ? $default : $this->options[$key];
    }

    public function delete( $key )
    {
        if (isset($this->options[$key]) ) { unset($this->options[$key]);
        }
        $options = get_option(QRR_SETTINGS);
        unset($options[$key]);
        update_option(QRR_SETTINGS, $options);
    }

    public function get_all( $key )
    {
        return $this->options;
    }

    public function sanitize( $input )
    {

        if (empty($_POST['_wp_http_referer']) ) {
            return $input;
        }

        // Get tab & section
        parse_str(sanitize_text_field($_POST['_wp_http_referer']), $referrer);

        $saved    = get_option($this->set_name, array());
        if(! is_array($saved) ) {
            $saved = array();
        }

        // Get list of settings
        $settings = $this->list_of_settings();
        $tab      = isset($referrer['tab']) ? $referrer['tab'] : 'general'; // TAB, First key by default
        $section  = isset($referrer['section']) ? $referrer['tab'] : ''; // SECTION

        $input = $input ? $input : array();

        // Sanitize tab section
        $input = apply_filters('qrr_settings_' . $tab . $section . '_sanitize', $input);


        // Ensure checkbox is passed
        if(!empty($settings[$tab]) ) {

            // Has sections inside tab
            if (preg_match('/.+_$/', $tab) ) {
                $comprobar = $settings[$tab][$section];
            }
            // No sections inside tab
            else {
                $comprobar = $settings[ $tab ];
            }

            foreach ( $comprobar as $key => $setting ) {
                // Single checkbox
                if (isset($settings[ $tab ][ $key ][ 'type' ]) && 'checkbox' == $settings[ $tab ][ $key ][ 'type' ] ) {
                    $input[ $key ] = ! empty($input[ $key ]);
                }
                // Multicheck list
                if (isset($settings[ $tab ][ $key ][ 'type' ]) && 'multicheck' == $settings[ $tab ][ $key ][ 'type' ] ) {
                    if(empty($input[ $key ]) ) {
                        $input[ $key ] = array();
                    }
                }
            }

        }

        // Loop each input to be saved and sanitize
        foreach( $input as $key => $value ) {

            // With sections inside tab
            if (preg_match('/.+_$/', $tab) ) {
                $type = isset($settings[$tab][$section][$key]['type']) ? $settings[$tab][$section][$key]['type'] : false;
            }
            // No sections inside tab
            else {
                $type = isset($settings[$tab][$key]['type']) ? $settings[$tab][$key]['type'] : false;
            }

            // Specific sanitize. Ex. qrr_settings_sanitize_textarea
            $input[$key]  = apply_filters(QRR_SETTINGS.'_sanitize_'.$type, $value, $key);

            // General sanitize
            $input[$key]  = apply_filters(QRR_SETTINGS.'_sanitize', $value, $key);
        }

        add_settings_error('qrr-notices', '', __('Settings updated.', 'qrr'), 'updated');

        return array_merge($saved, $input);
    }

    // Show fields, depends on type
    //-------------------------------------

    /**
     * Not found callback function
     *
     * @since 1.0
     * @param $args
     */
    public function show_missing( $args )
    {

        printf(__('The callback function for setting <strong>%s</strong> is missing.', 'qrr'), $args['id']);
    }

    public function show_esc_label($name, $desc)
    {
        echo '<label for="'.esc_attr($name).'"> '  . esc_html($desc). '</label>';
    }

    /**
     * Checkbox field
     *
     * @since 1.0
     * @param $args
     */
    public function show_checkbox( $args )
    {

        $checked = isset($this->options[$args['id']]) ? checked(1, $this->options[$args['id']], false) : '';
        $name = "{$this->set_name}[{$args['id']}]";
        $desc = $args['desc'];

        echo '<input type="checkbox" id="'.esc_attr($name).'" name="'.esc_attr($name).'" value="1" ' . esc_attr($checked) . '/>';
        $this->show_esc_label($name, $desc);
    }

    /**
     * Show text field
     *
     * @since 1.0
     * @param $args
     */
    public function show_text( $args )
    {

        if (isset($this->options[ $args['id'] ]) ) {
            $value = $this->options[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $size = ( isset($args['size']) && ! is_null($args['size']) ) ? $args['size'] : 'regular';
        $name = "{$this->set_name}[{$args['id']}]";
        $desc = $args['desc'];


        echo '<input type="text" class="' . esc_attr($size) . '-text" id="'.esc_attr($name).'" name="'.esc_attr($name).'" value="' . esc_attr(stripslashes($value)) . '"/>';
        $this->show_esc_label($name, $desc);
    }

    /**
     * Show textarea
     *
     * @since 1.0
     * @param $args
     */
    public function show_textarea( $args )
    {

        if (isset($this->options[ $args['id'] ]) ) {
            $value = $this->options[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $size = ( isset($args['size']) && ! is_null($args['size']) ) ? $args['size'] : 'regular';
        $name = "{$this->set_name}[{$args['id']}]";
        $desc = $args['desc'];

        echo '<textarea class="'.esc_attr($size).'-text" cols="50" rows="10" id="'.esc_attr($name).'" name="'.esc_attr($name).'">' .
             esc_textarea(stripslashes($value)) .
             '</textarea>';
        echo '<div class="qrr-settings-desc">'.esc_html($desc).'</div>';
    }

    /**
     * Radio field
     *
     * @since 1.0
     * @param $args
     */
    public function show_radio( $args )
    {

        foreach( $args['options'] as $key => $value ) {
            $checked = false;
            if (isset($this->options[ $args['id'] ]) && $this->options[ $args['id'] ] == $key ) {
                $checked = true;
            } else if (!isset($this->options[ $args['id'] ]) && isset($args['std']) && $args['std'] == $key ) {
                $checked = true;
            }

            $name = "{$this->set_name}[{$args['id']}]";
            $id = "{$this->set_name}[{$args['id']}][{$key}]";
            $desc = $args['desc'];

            echo '<input name="'.esc_attr($name).'" id="'.esc_attr($id).'" type="radio" value="' . esc_attr($key) . '" ' . checked(true, $checked, false) . '/>&nbsp;';
            echo '<label for="'.esc_attr($id).'">' . esc_html($value) . '</label><br/>';
        }
        echo '<p class="description">' . esc_html($desc) . '</p>';
    }

    /**
     * Select image size field
     *
     * @since 1.0
     * @param $args
     */
    public function show_select_size( $args )
    {

        if (isset($this->options[ $args['id'] ]) ) {
            $value = $this->options[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $sizes = qrr_get_image_sizes();
        $size = ( isset($args['size']) && ! is_null($args['size']) ) ? $args['size'] : 'regular';
        $name = "{$this->set_name}[{$args['id']}]";
        $desc = $args['desc'];

        echo '<select name="'.esc_attr($name).'" id="'.esc_attr($name).'" >';
        foreach($sizes as $key => $dim) {
            $selected =  ($key == $value ? 'selected': '');
            $size = $key.'('.$dim['width'].'x'.$dim['height'].')';
            echo '<option value="'.esc_attr($key).'" '.esc_attr($selected).'>'.esc_html($size).'</option>';
        }
        echo '</option>';
        echo '<p class="description">' . esc_html($desc) . '</p>';
    }

    /**
     * Show description
     *
     * @since 1.0
     * @param $args
     */
    public function show_desc( $args )
    {

        echo'<p>'.esc_html($args['desc']).'</p>';
    }


    public function show_activate_license()
    {

        //qrr_delete_license_status();
        $license = qrr_get_license_key();
        $status = qrr_get_license_status();
        echo '<div>License: '.esc_html($license).' '.esc_html($status).'</div>';

        if (false != $license ) {
            if ($status == 'valid' ) {
                wp_nonce_field('qrr_license_nonce', 'qrr_license_nonce');
                ?>
                <input type="submit" class="button-secondary" name="qrr_license_deactivate" value="<?php _e('Deactivate License', 'qrr'); ?>"/>
                <?php
            } else {
                wp_nonce_field('qrr_license_nonce', 'qrr_license_nonce');
                ?>
                <input type="submit" class="button-secondary" name="qrr_license_activate" value="<?php _e('Activate License', 'qrr'); ?>"/>
                <?php
            }
        } else {
            _e('Enter a license and save before activating.', 'qrr');
        }
    }

    /**
     * Call a callback function, name has to be in $args['callback']
     *
     * @since 1.0
     * @param $args
     */
    public function show_callback( $args )
    {
        $func = $args['callback'];
        if (is_callable($func)) {
            call_user_func($func, $args);
        }
    }

    public function sanitize_license( $new )
    {
        $old =  $this->get('qrr_pro_license_key');
        if($old && $old != $new ) {
            $this->delete('qrr_pro_license_status'); // new license has been entered, so must reactivate
        }
        return $new;
    }
}

/**
 * Sanitize text field
 *
 * @since  1.0
 * @param  $value
 * @return mixed
 */
function qrr_settings_sanitize_text( $value )
{
    return trim($value);
}
add_filter('qrr_settings_sanitize_text', 'qrr_settings_sanitize_text');

/**
 * Sanitize textarea field
 *
 * @since  1.0
 * @param  $value
 * @return mixed
 */
function qrr_settings_sanitize_textarea( $value )
{
    return $value;
}
add_filter('qrr_settings_sanitize_textarea', 'qrr_settings_sanitize_textarea');

