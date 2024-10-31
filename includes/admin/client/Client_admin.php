<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}

class QRR_Client_Admin
{

    public function __construct()
    {

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        add_filter('manage_qrr_client_posts_columns', array( $this,'filter_custom_columns'));
        add_action('manage_qrr_client_posts_custom_column', array($this,'display_custom_columns'), 10, 2);

    }

    public function admin_scripts($hook)
    {

    }

    // Custom columns
    //--------------------------------------

    public function filter_custom_columns($columns)
    {

        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['email'] = _x('Email', 'Table clients', 'qrr');
        $new_columns['phone'] = _x('Phone', 'Table clients', 'qrr');
        $new_columns['bookings'] = _x('Bookings', 'Table clients', 'qrr');
        return $new_columns;
    }

    public function display_custom_columns( $column, $post_id )
    {

        global $post;

        $model = new QRR_Client_Model($post_id);

        switch ( $column ) {

        case 'email':
            echo esc_html($model->get_email());
            break;

        case 'phone':
            echo esc_html($model->get_phone());
            break;

        case 'bookings':
            $list = $model->get_bookings_count();
            foreach($list as $item){
                if ($item['count'] > 0) {
                    echo '<div class="status-'.esc_attr($item['status']).'">'.wp_kses_post($item['label']).': '.esc_html($item['count']).'</div>';
                }
            }
            break;

        default:
            break;
        }

        return $column;
    }

}

new QRR_Client_Admin();
