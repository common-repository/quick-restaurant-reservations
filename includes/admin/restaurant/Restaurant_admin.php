<?php


// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


class QRR_Restaurant_Admin
{
    private $post_type = 'qrr_restaurant';

    public function __construct()
    {

        add_filter('manage_edit-'.$this->post_type.'_columns', array($this,'manage_columns'), 999);
        add_action('manage_'.$this->post_type.'_posts_custom_column', array($this,'display_custom_columns'), 10, 2);
    }

    public function manage_columns( $columns )
    {

        $remove_all_other_columns = apply_filters('qrr_restaurant_remove_all_other_admin_columns', true);

        if ($remove_all_other_columns) {

            // Remove all columns expect these ones
            $allowed = apply_filters('qrr_restaurant_allowed_admin_columns', array('cb','title','date'));

            foreach( $columns as $column => $data ) {
                if (!in_array($column, $allowed) ) {
                    unset($columns[$column]);
                }
            }

        }

        $columns['shortcode'] = _x('Shortcode', 'Table restaurants', 'qrr');

        $new_columns = array(
            'cb'        => $columns['cb'],
            'title'     => $columns['title'],
            'shortcode' => $columns['shortcode'],
            'date'      => $columns['date']
        );

        return $new_columns;
    }

    public function display_custom_columns( $column, $post_id )
    {

        global $post;

        switch ( $column ) {

        case 'shortcode':
            echo '[qrr_form id="'.esc_attr($post_id).'"]';
            break;
        default:
            break;
        }

        return $column;
    }



}

new QRR_Restaurant_Admin();
