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
        add_settings_field( 'categories_disabled', 'Kategorien für den Verkauf',  'categories_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'b2b_roles', 'B2B Rollen',  'roles_callback', 'th-custom', 'shop_settings');
    }

    function categories_callback() {
        $categories_disabled = get_option( 'categories_disabled' );
        if(!$categories_disabled) {
            $categories_disabled = array();
        }


        $args = array(
            'taxonomy' => 'product_cat',
            'orderby' => 'parent',
            'order' => 'ASC',
            'hide_empty' => false
        );
        foreach( get_categories( $args ) as $category ) :
            if( $category->parent === 0 ) {
                ?>
                <input id="<?php echo $category->slug; ?>" name="categories_disabled[]" type="checkbox" value="<?php echo $category->slug; ?>" <?php checked( in_array( $category->slug, $categories_disabled ) ) ?> />
                <label for="<?php echo $category->slug; ?>"><?php echo $category->name; ?></label><br />
                <?php
            }
            $category->name;
        endforeach;
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

    // Deaktiviere die Verkaufsfunktion für bestimmte Kategorien
    add_filter( 'woocommerce_is_purchasable', 'th_hide_add_to_cart', 30, 2 );
    function th_hide_add_to_cart( $return_val, $product ) {
        // Alle Kategorien, die (noch) nicht zum Verkauf stehen
        $deactivate_categories = get_option( 'categories_disabled' );
        if(!$deactivate_categories) {
            $deactivate_categories = array();
        }
        $b2b_roles = th_return_option( 'b2b_roles' );

        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;

        // Falls der Kunde ein Stammkunde ist, aktiviere die Verkaufsfunktion
        if( count(array_intersect( $b2b_roles, $roles ) ) ) {
            return $return_val;
        }

        // Ist das Produkt in einer ausgeschlossenen Kategorie, wird der Verkauf deaktiviert
        if( empty($deactivate_categories) || !has_term( $deactivate_categories, 'product_cat', $product->id ) ) {
            add_action('woocommerce_single_product_summary', 'message_hide_add_to_cart');
            return false;
        } else {
            return $return_val;
        }
    }

    // Zeige eine Meldung bei ausgeblendeten Kategorien
    function message_hide_add_to_cart () {
        echo "<p>Dieses Produkt steht momentan im Webshop noch nicht zum Verkauf.</p>";
    }
?>