<?php 

    defined('ABSPATH') or die("not allowed");

    /*

        Plugin Name: Töpferhaus Custom
        Plugin URI: https://git.willsam.ch/fhinok/th-custom
        Description: Add custom settings and functions for töpferhaus
        Version: 0.1
        Author: Samuel Will
        Author URI: https://willsam.ch
        License: GPL2 or later
        License URI:  https://www.gnu.org/licenses/gpl-2.0.html
        Text Domain:  th-custom

    */

    require( 'th-custom-userfields.php' );
    require( 'th-no-mail-required.php' );

    // Manualy whitelist options because there was a bug
    $sections = array(
        'th-custom' => array(
            'categories_disabled',
            'b2b_roles'
        )
    );

    add_filter( 'allowed_options', function($allowed_options) use ($sections) {
        foreach($sections as $section => $fields) {
            foreach($fields as $field) {
                $allowed_options[$section][] = $field;
            }
        }
        return $allowed_options;
    } );


    // Create Admin Settings

    function th_render_plugin_settings_page() {
        ?>
        <div class="wrap">
            <h1>Töpferhaus Plugin Einstellungen</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'th-custom' );
                do_settings_sections( 'th-custom' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    add_action( 'admin_menu', 'th_add_settings_page' );
    function th_add_settings_page() {
        add_options_page( 'Töpferhaus Custom Seite', 'Töpferhaus', 'manage_options', 'th-custom', 'th_render_plugin_settings_page' );
    }

    add_action( 'admin_init', 'setup_sections' );
    function setup_sections() {
        add_settings_section( 'shop_settings', 'Shop Einstellungen', 'section_callback',  'th-custom' );
    }

    function section_callback() {
        echo 'Definieren einiger Einstellungen für Berechtigungen und Rollen. Kategorien mit Komma trennen.';
    }

    add_action( 'admin_init', 'setup_fields' );
    function setup_fields() {
        add_settings_field( 'categories_disabled', 'Deaktivierte Kategorien',  'categories_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'b2b_roles', 'B2B Rollen',  'roles_callback', 'th-custom', 'shop_settings');
    }

    function categories_callback() {
        echo '<input name="categories_disabled" id="categories_disabled" type="text" value="' . get_option( 'categories_disabled' ) . '" />';
        register_setting( 'th-custom', 'categories_disabled' );
    }

    function roles_callback() {
        echo '<input name="b2b_roles" id="b2b_roles" type="text" value="' . get_option( 'b2b_roles' ) . '" />';
        register_setting( 'th-custom', 'b2b_roles' );
    }

    // return options and create array from comma separated string
    function th_return_option( $name ) {
        $option = preg_split( '/(\s*,*\s*)*,+(\s*,*\s*)*/', get_option( $name ));
        return $option;
    }
?>