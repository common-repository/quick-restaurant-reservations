<?php

class QRR_Page_Settings
{

    public function __construct()
    {
        add_action('admin_init', array( $this, 'init' ));
    }

    public function init()
    {

        $page = key_exists('page', $_GET) ? sanitize_text_field($_GET['page']) : '';

        if ($page == 'qrr-settings' ) {
            add_action('qrr_settings_admin_page',    array( $this, 'output' ));
        }
    }


    public function output()
    {

        // All tabs
        $tabs = apply_filters(
            'qrr_settings_tabs', array(
            //'license' => __('License','qrr'),
            'general' => __('General', 'qrr'),
            'bookings' => __('Bookings', 'qrr')
            )
        );

        // Detect Tab
        $active_tab = key_exists('tab', $_GET) ? sanitize_text_field($_GET['tab']) : '';
        if (!array_key_exists($active_tab, $tabs)) {
            $active_tab = 'general';
        }

        $active_section = key_exists('section', $_GET) ? sanitize_text_field($_GET['section']) : '';

        ob_start();
        ?>
        <div class="wrap">
            <h2 class="nav-tab-wrapper">
                <?php
                foreach( $tabs as $tab_id => $tab_data ) {

                    // Tab with one section
                    if (is_string($tab_data) ) {
                        $tab_name = $tab_data;
                    }
                    // Tab with several sections
                    else {
                        $tab_name = $tab_data['name'];
                    }

                    // URL
                    $tab_url = add_query_arg(
                        array(
                        'settings-updated' => false,
                        'tab' => $tab_id,
                        'section' => false
                        )
                    );

                    // First section if exists
                    if (is_array($tab_data) ) {
                        $keys = array_keys($tab_data['sections']);
                        $tab_url = add_query_arg(
                            array(
                            'settings-updated' => false,
                            'tab' => $tab_id,
                            'section' => $keys[0]
                            )
                        );
                    }

                    $active = ($active_tab == $tab_id) ? ' nav-tab-active' : '';

                    echo '<a href="' . esc_url($tab_url) . '" title="' . esc_attr($tab_name) . '" class="nav-tab' . esc_attr($active) . '">';
                    echo esc_html($tab_name);
                    echo '</a>';
                }
                ?>
            </h2>
        </div>
        <?php settings_errors(); ?>
        <div id="tab_container">

            <?php

            //List of sections if exists
            $tab = $tabs[$active_tab];

            if (is_array($tab) ) {

                echo '<ul class="qrr-options-sections__ subsubsub">';

                $count = count($tab['sections']);
                $index = 1;
                foreach( $tab['sections'] as $section_key => $section_name ) {

                    $tab_sec_url = add_query_arg(
                        array(
                        'settings-updated' => false,
                        'tab' => $active_tab,
                        'section' => $section_key,
                        'qrr-message' => false
                        )
                    );

                    echo '<li>';
                    $class = ( $section_key == $active_section ? 'current' : '' ) ;
                    echo '<a href="' . esc_url($tab_sec_url) . '" class="'. esc_attr($class).'">' . esc_html($section_name) . '</a>';
                    if ($index++ < $count ) { echo ' | ';
                    }
                    echo '</li>';

                }
                echo '</ul>';
            }
            ?>

            <form method="post" action="options.php">
                <table class="form-table">
                    <?php
                    settings_fields(QRR_SETTINGS);
                    do_settings_fields(QRR_SETTINGS.'_' . $active_tab.$active_section, QRR_SETTINGS.'_' . $active_tab.$active_section);
                    ?>
                </table>
                <?php submit_button(); ?>
            </form>


        </div>
        <?php
        echo ob_get_clean();
    }
}
new QRR_Page_Settings;
