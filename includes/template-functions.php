<?php

// Exit if accessed directly
if (! defined('ABSPATH') ) { exit;
}


/**
 * Returns the path to the templates directory
 *
 * @since  1.0.0
 * @return string
 */
function qrr_get_templates_dir()
{
    return QRR_PLUGIN_DIR . 'templates';
}

/**
 * Returns the URL to the templates directory
 *
 * @since  1.0.0
 * @return string
 */
function qrr_get_templates_url()
{
    return QRR_PLUGIN_URL . 'templates';
}


/**
 * Retrieves a template part
 *
 * @since v1.0
 *
 * Taken from bbPress
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses qrr_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function qrr_get_template_part( $slug, $name = null, $load = true )
{

    // Execute code for this part
    do_action('get_template_part_' . $slug, $slug, $name);

    // Setup possible parts
    $templates = array();
    if (isset($name) ) {
        $templates[] = $slug . '-' . $name . '.php';
    }
    $templates[] = $slug . '.php';

    // Allow template parts to be filtered
    $templates = apply_filters('qrr_get_template_part', $templates, $slug, $name);

    // Return the part that is found
    return qrr_locate_template($templates, $load, false);
}

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since 1.0
 *
 * @param  string|array $template_names Template file(s) to search for, in order.
 * @param  bool         $load           If true the template file will be loaded if it is found.
 * @param  bool         $require_once   Whether to require_once or require. Default true.
 *                                      Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function qrr_locate_template( $template_names, $load = false, $require_once = true )
{

    // No file found yet
    $located = false;

    // Try to find a template file
    foreach ( (array) $template_names as $template_name ) {

        // Continue if template is empty
        if (empty($template_name) ) {
            continue;
        }

        // Trim off any slashes from the template name
        $template_name = ltrim($template_name, '/');

        // try locating this template file by looping through the template paths
        foreach( qrr_get_theme_template_paths() as $template_path ) {

            if(file_exists($template_path . $template_name) ) {
                $located = $template_path . $template_name;
                break;
            }
        }

        if($located ) {
            break;
        }
    }

    if (( true == $load ) && ! empty($located) ) {
        load_template($located, $require_once);
    }

    return $located;
}

/**
 * Returns a list of paths to check for template locations
 *
 * @since  1.0.0
 * @return mixed|void
 */
function qrr_get_theme_template_paths()
{

    $template_dir = qrr_get_theme_template_dir_name();

    $file_paths = array(
        1 => trailingslashit(get_stylesheet_directory()) . apply_filters('qrr_theme_template_dir', $template_dir),
        10 => trailingslashit(get_template_directory()) . $template_dir,
        100 => qrr_get_templates_dir()
    );

    $file_paths = apply_filters('qrr_template_paths', $file_paths);

    // sort the file paths based on priority
    ksort($file_paths, SORT_NUMERIC);

    return array_map('trailingslashit', $file_paths);
}

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the qrr_templates_dir filter.
 *
 * @since  1.0.0
 * @return string
 */
function qrr_get_theme_template_dir_name()
{
    return trailingslashit(apply_filters('qrr_templates_dir', QRR_THEME_TEMPLATES));
}
