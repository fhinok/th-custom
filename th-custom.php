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
                // Liste box_products Kategorie nicht auf
                if ($category->slug == "box_products" ) {
                    continue;
                }
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

    // Hack für Composite Plugin
    function th_enqueue($hook) {
        // Only add to the edit.php admin page.
        // See WP docs.
        if ('post.php' !== $hook) {
            return;
        }
        wp_enqueue_script('th_custom_admin_script', plugin_dir_url(__FILE__) . 'assets/admin.js');
    }
    
    add_action('admin_enqueue_scripts', 'th_enqueue');


    // Product Table Autocheck
    add_filter( 'woocommerce_quantity_input_args', 'th_woocommerce_quantity_input_args', 10, 2 );

    function th_woocommerce_quantity_input_args($args, $product) {
        $b2b_roles = th_return_option( 'b2b_roles' );
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;

        // Falls der Kunde ein Stammkunde ist, beginnt die Menge bei 0
        if( count(array_intersect( $b2b_roles, $roles ) ) ) {
            $args['input_value'] = 0;
        }
        return $args;
    }

    function th_custom_srcipts () {
        wp_enqueue_script( 'custom-js', plugin_dir_url(__FILE__) . 'assets/table.js', array( 'jquery' ),'',true );
    }
    add_action( 'wp_enqueue_scripts', 'th_custom_srcipts' );

    add_filter( 'wp_ajax_update_cart', 'ajax_update_cart' );
    add_filter( 'wp_ajax_nopriv_update_cart', 'ajax_update_cart' );
        
    function ajax_update_cart() {

        $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_STRING);
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
        $product_status = get_post_status($product_id);
        $exists = false;

        foreach( WC()->cart->get_cart() as $cart_item ) {
            $product_in_cart = $cart_item['product_id'];
            if ( $product_in_cart === $product_id ) {
                $exists = true;
            }
        }

        if ($passed_validation && !$exists) {
            WC()->cart->add_to_cart($product_id, $quantity);
            do_action('woocommerce_ajax_added_to_cart', $product_id);
            WC_AJAX :: get_refreshed_fragments();
        } else {
            $prod_unique_id = WC()->cart->generate_cart_id( $product_id );
            WC()->cart->set_quantity($prod_unique_id, $quantity);
            WC_AJAX :: get_refreshed_fragments();
        }

        wp_die();
    }

    add_filter( 'wp_ajax_get_cart_qty', 'ajax_get_cart_qty' );
    add_filter( 'wp_ajax_nopriv_get_cart_qty', 'ajax_get_cart_qty' );

    function ajax_get_cart_qty() {
        $data = array();
        foreach( WC()->cart->get_cart() as $cart_item_key => $cart_item ){
            $data[$cart_item['product_id']] = $cart_item['quantity'];
        }
        // var_dump( $data );
        wp_send_json( $data );

        wp_die();
    }
        
?>