<?php
/**
 * Plugin Name: Quick Restaurant Reservations
 * Plugin URI: http://thingsforrestaurants.com
 * Description: Restaurant manager for reservations
 * Author: ThingsForRestaurants
 * Author URI: https://thingsforrestaurants.com/
 * License:     GPLv2
 * Version: 1.6.7
 * Text Domain: qrr
 * Domain Path: languages
 *
 * Quick Restaurant Reservations is free software:
 * you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Quick Restaurant Reservations is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Restaurant Menu. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category Bookings
 * @package  QRR
 * @author   ThingsForRestaurants <elapsl.aps@gmail.com>
 * @link     https://thingsforrestaurants.com/
 */



if (!class_exists('Quick_Restaurant_Reservations') ) {

    /**
     * Main initiation class.
     *
     * @since 0.0.0
     */
    final class Quick_Restaurant_Reservations
    {

        private static $singleton;

        public $settings;

        public static function singleton()
        {

            if (!isset(self::$singleton) && !( self::$singleton instanceof Quick_Restaurant_Reservations ) ) {

                self::$singleton = new Quick_Restaurant_Reservations;
                self::$singleton->init();
                self::$singleton->setup_constants();

                add_action('plugins_loaded', array(self::$singleton, 'load_textdomain' ));

                self::$singleton->includes();
                //self::$singleton->setup_license();
                self::$singleton->settings = new QRR_Settings(QRR_SETTINGS);
            }
            return self::$singleton;
        }

        /**
         * With singleton: Error if clone object
         *
         * @since 1.0
         */
        public function __clone()
        {
            // Cloning is not allowed
            _doing_it_wrong(
                __FUNCTION__,
                __('Clone is not allowed', 'qrr'),
                '1.0.0'
            );
        }

        /**
         * With singleton: Error unserializing class
         *
         * @since 1.0
         */
        public function __wakeup()
        {
            // Unserializing is not allowd
            _doing_it_wrong(
                __FUNCTION__,
                __('Unserializing is not allowed', 'qrr'),
                '1.0.0'
            );
        }

        /**
         * Init
         *
         * @since 1.0
         */
        private function init()
        {
        }

        /**
         * Plugin Constants
         *
         * @since 1.0
         */
        private function setup_constants()
        {

            // Folder Path
            if (! defined('QRR_PLUGIN_DIR') ) {
                define('QRR_PLUGIN_DIR', plugin_dir_path(__FILE__));
            }

            // Folder URL
            if (! defined('QRR_PLUGIN_URL') ) {
                define('QRR_PLUGIN_URL', plugin_dir_url(__FILE__));
            }

            // Root File
            if (! defined('QRR_PLUGIN_FILE') ) {
                define('QRR_PLUGIN_FILE', __FILE__);
            }

            // Version
            if (! defined('QRR_VERSION') ) {
                define('QRR_VERSION', '1.6.7');
            }

            // Name for Setting
            if (! defined('QRR_SETTINGS') ) {
                define('QRR_SETTINGS', 'qrr_settings');
            }

            // For templates
            if (! defined('QRR_THEME_TEMPLATES') ) {
                define('QRR_THEME_TEMPLATES', 'qrr_templates');
            }

        }

        public function get_version()
        {
            return QRR_VERSION;
        }

        public function get_addons_link()
        {
            return 'https://thingsforrestaurants.com/quick-restaurant-reservations/';
        }

        /**
         * Required files
         *
         * @since 1.0
         */
        private function includes()
        {
            // General
            include_once QRR_PLUGIN_DIR . 'includes/post-types.php';
            include_once QRR_PLUGIN_DIR . 'includes/shortcodes.php';
            include_once QRR_PLUGIN_DIR . 'includes/scripts-front.php';
            include_once QRR_PLUGIN_DIR . 'includes/template-functions.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/settings/settings.php';

            include_once QRR_PLUGIN_DIR . 'includes/admin/functions.php';

            include_once QRR_PLUGIN_DIR . 'includes/ajax-actions.php';

            include_once QRR_PLUGIN_DIR . 'includes/admin/restaurant/Restaurant.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/restaurant/Restaurants.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/restaurant/Restaurant_model.php';

            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Bookings.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_edit.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_form.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_validation.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_email.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_model.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_record.php';

            include_once QRR_PLUGIN_DIR . 'includes/admin/client/Client_model.php';
            include_once QRR_PLUGIN_DIR . 'includes/admin/client/Clients.php';


            // Admin
            if (is_admin() ) {

                include_once QRR_PLUGIN_DIR . 'includes/admin/admin-pages.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/functions.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/settings/class-settings.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/scripts-admin.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/restaurant/Restaurant_admin.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/restaurant/Restaurant_edit.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/booking/Booking_admin.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/client/Client_admin.php';
                include_once QRR_PLUGIN_DIR . 'includes/admin/client/Client_edit.php';

				include_once QRR_PLUGIN_DIR . 'includes/banner.php';
            }

        }

        /**
         * Language files
         *
         * @since 1.0
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('qrr', false, plugin_basename(dirname(__FILE__)) . "/languages/");
        }

    }

}


if (!function_exists('QRR') ) {
    function QRR()
    {
        return Quick_Restaurant_Reservations::singleton();
    }
}

QRR();


if (!function_exists('QRR_Active') ) {

    function QRR_Active( $addon )
    {

        // Is plugin active
        if (QRR_Active_plugin($addon) ) {
            $func = "qrr_{$addon}_check_license_status";
            if (!function_exists($func) ) {
                return false;
            }
            return call_user_func($func);
        }
    }

    /**
     * Check is active addon
     *
     * @param $addon (name addon)
     *
     * @return bool
     */
    function QRR_Active_plugin( $addon )
    {

        $plugin = 'quick-restaurant-reservations-'.$addon.'/quick-restaurant-reservations-'.$addon.'.php';
        return in_array($plugin, (array) get_option('active_plugins', array()));
    }

    /**
     * Activate
     *
     * @param $addon (name of the addon)
     *
     * @return string
     */
    function QRR_Activate_license( $addon )
    {
        return admin_url()
               .'edit.php?post_type=qrr_booking&page=qrr-settings&tab=addon_'
               .$addon;
    }

}
