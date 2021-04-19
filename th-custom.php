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
    require( 'th-variations-in-loop.php' );

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
            // Prüfe, ob der Stammkunde die passende Berechtigung besitzt.
            $user_categories = get_the_author_meta('can_buy_categories', $user->ID);
            if($user_categories) {
                foreach( $user_categories as $category ) {
                    if( has_term( $category, 'product_cat', $product->id )) {
                        return $return_val;
                    }
                }
            }
            return false;
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
        echo "<p>Dieses Produkt steht momentan im Webshop nicht zum Verkauf.</p>";
    }

    // In Tabelle ausblenden
    add_action( 'wpto_table_query_args', 'th_hide_products_in_table', 1, 3 );
    function th_hide_products_in_table($a, $b, $c) {
        $user = get_current_user_id();
        $user_categories = get_the_author_meta('can_buy_categories', $user);
        // var_dump($c);
        if( $user_categories && $c['name'] == 'Reseller' ) {
            $user_categories_ids = [];
            $a['tax_query']['product_cat_IN']['terms'] = [];
            foreach( $user_categories as $category) {
                $category = get_term_by( 'slug', $category, 'product_cat' );
                $user_categories_ids[] = $category->term_id;
                echo "<script>filterToRemove.push('".$category->term_id."');</script>";
            }
            
            $a['tax_query']['product_cat_IN']['terms'] = $user_categories_ids;
        }

        return $a;
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
    
    wp_enqueue_script('th_custom_script', plugin_dir_url(__FILE__) . 'assets/th-custom.js', array('jquery'));
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

    // Product Table Ajax Update
    function th_custom_srcipts () {
        wp_enqueue_script( 'table-js', plugin_dir_url(__FILE__) . 'assets/table.js', array( 'jquery' ), '', true );
        wp_enqueue_style('table-css', plugin_dir_url(__FILE__) . 'assets/table.css');
    }

    add_action( 'wp_enqueue_scripts', 'th_custom_srcipts' );

    add_filter( 'wp_ajax_update_cart', 'ajax_update_cart' );
    add_filter( 'wp_ajax_nopriv_update_cart', 'ajax_update_cart' );
        
    function ajax_update_cart() {

        $products = $_POST['products'];
        $cart_items = array();

        foreach( WC()->cart->get_cart() as $cart_item ) {
            $cart_items[] = $cart_item;
        }

        foreach ($products as $product_id => $qty) {
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($product_id));
            $quantity = filter_var($qty, FILTER_SANITIZE_STRING);
            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            $product_status = get_post_status($product_id);
            $exists = false;

            foreach ( $cart_items as $cart_item ) {
                if( $cart_item['product_id'] === $product_id ){
                    $exists = true;
                }
            }

            if ($passed_validation && !$exists) {
                    WC()->cart->add_to_cart($product_id, $quantity);
                do_action('woocommerce_ajax_added_to_cart', $product_id);
            } else {
                $prod_unique_id = WC()->cart->generate_cart_id( $product_id );
                WC()->cart->set_quantity($prod_unique_id, $quantity);
            }
        }

        wp_die();
    }


    // Copyright Perlenfischer
    // Hook Admin Produkteseite
    add_action( 'woocommerce_product_options_advanced', 'th_custom_add_custom_product_fields' );
    function th_custom_add_custom_product_fields() {
        global $post;

        $input_checkbox = get_post_meta( $post->ID, 'is_copyright', true );
        if( empty( $input_checkbox ) ) $input_checkbox = '';

        woocommerce_wp_checkbox(array(
            'id'            => 'is_copyright',
            'label'         => __('Perlenfischer', 'woocommerce' ),
            'description'   => __( 'Hat einen Perlenfischer-Stempel', 'woocommerce' ),
            'value'         => $input_checkbox,
        ));
    }

    add_action( 'woocommerce_process_product_meta', 'th_custom_save_custom_product_fields' );
    function th_custom_save_custom_product_fields($post_id) {
        $_custom_text_option = isset( $_POST['is_copyright'] ) ? 'yes' : '';
        update_post_meta( $post_id, 'is_copyright', $_custom_text_option );
    }

    // Hook Produkteseite
    add_action( 'woocommerce_product_additional_information', 'th_get_custom_product_fields', 20 );
    function th_get_custom_product_fields() {
        global $post;
        $is_copyright = get_post_meta( $post->ID, 'is_copyright', true );
        if( $is_copyright ){
            echo '<em>Diese Karte verwendet urheberrechtlich geschützte Stempel von <a href="https://www.perlenfischerdesign.de" target="_blank">perlenfischer</a>.</em>';
        } 
    }

    // Meldung Saisonal
    add_filter('berocket_apl_label_show_text', 'th_saison');
    function th_saison($text) {
        if (!is_product()) {
            return $text;
        }
        
        if( 'saison' === strtolower($text)) {
            add_action( 'woocommerce_product_additional_information', 'th_get_custom_product_fields', 20 );
            echo '<em>Nur solange Vorrat. Dieses Produkt wird mit saisonalen Zutaten hergestellt.</em>';
        }
        return $text;
    }
    
    // Bio Suisse Logo
    add_action('woocommerce_product_additional_information', 'th_bio_logo', 20);
    function th_bio_logo() {
        global $product;
        $is_bio = $product->get_attribute( 'bio' );
        $is_urdinkel = $product->get_attribute( 'typ' );
        echo '<div class="zertifizierungen">';
        if( $is_bio == "Ja") {
            echo '<div class="bio_logo"><a href="https://www.bio-suisse.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '/img/bio-knospe-logo.png"></a></div>';
        }
        if ( strtolower($is_urdinkel) == 'urdinkel' ) {
            echo '<div class="urdinkel_logo"><a href="https://www.urdinkel.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) .'/img/urdinkel-logo.png"</a></div>';
        }
        echo "</div>";
    }

?>