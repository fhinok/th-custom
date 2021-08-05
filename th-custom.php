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

    /**
     * require other parts of plugin
     */
    require( 'th-custom-userfields.php' );
    require( 'th-no-mail-required.php' );
    require( 'th-variations-in-loop.php' );
    require( 'th-payment-b2b.php' );
    // require( 'th-shipping-pickwings.php' );

    /**
     * Manualy whitelist options because there was a bug?
     */
    $sections = array(
        'th-custom' => array(
            'categories_disabled',
            'b2b_roles',
            'max_cards',
            'hide_shipping_methods',
            'hide_shipping_methods_guest'
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

    
   /**
    * create admin settings
    */

    // render settings page
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
        echo 'Definieren einiger Einstellungen für Berechtigungen und Rollen.';
    }

    // add fields to settings page
    add_action( 'admin_init', 'setup_fields' );
    function setup_fields() {
        add_settings_field( 'categories_disabled', 'Kategorien für den Verkauf',  'categories_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'b2b_roles', 'B2B Rollen',  'roles_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'max_cards', 'Max. Karten',  'max_cards_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'hide_shipping_methods', 'Versteckte Lieferoptionen für Stammkunden', 'shipping_callback', 'th-custom', 'shop_settings');
        add_settings_field( 'hide_shipping_methods_guest', 'Versteckte Lieferoptionen für Standard-Kunden', 'shipping_callback_guest', 'th-custom', 'shop_settings');
    }

    // callback function for for enabled/disabled categories
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
                // Do not list 'box_product' categorie and all of its children
                if ($category->slug == "box_products" ) { continue; }
                ?>
                <input id="<?php echo $category->slug; ?>" name="categories_disabled[]" type="checkbox" value="<?php echo $category->slug; ?>" <?php checked( in_array( $category->slug, $categories_disabled ) ) ?> />
                <label for="<?php echo $category->slug; ?>"><?php echo $category->name; ?></label><br />
                <?php
            }
            $category->name;
        endforeach;
        register_setting( 'th-custom', 'categories_disabled' );        
    }

    // callback function for roles
    function roles_callback() {
        echo '<input name="b2b_roles" id="b2b_roles" type="text" value="' . get_option( 'b2b_roles' ) . '" />';
        register_setting( 'th-custom', 'b2b_roles' );
    }

    // callback function for max card quantity
    function max_cards_callback() {
        echo '<input name="max_cards" id="max_cards" type="number" value="' . get_option( 'max_cards' ) . '" />';
        register_setting( 'th-custom', 'max_cards' );
    }

    // callback function for shipping methods b2b
    function shipping_callback() {
        $zones = WC_Shipping_Zones::get_zones();
        $methods = array_map(function($zone) {
            echo "<strong>Zone : {$zone['zone_name']}</strong><br />";
            
            $hide_shipping_methods = get_option( 'hide_shipping_methods' );
            if(!$hide_shipping_methods) { $hide_shipping_methods = array(); }

            // List all shipping methods
            foreach( $zone['shipping_methods'] as $shipping_method ):
                $shipping_id = $shipping_method->id . ':' . $shipping_method->instance_id;
                ?> 
                <input id="<?php echo $shipping_id; ?>" name="hide_shipping_methods[]" type="checkbox" value="<?php echo $shipping_id; ?>" <?php checked( in_array( $shipping_id, $hide_shipping_methods ) ) ?> />
                <label for="<?php echo $shipping_id; ?>"><?php echo $shipping_method->title; ?></label><br />
                <?php
            endforeach;

            echo "<br/ >";
        }, $zones);

        register_setting( 'th-custom', 'hide_shipping_methods' );
    }

    // callback function for shipping methods not b2b
    function shipping_callback_guest() {
        $zones = WC_Shipping_Zones::get_zones();
        $methods = array_map(function($zone) {
            echo "<strong>Zone : {$zone['zone_name']}</strong><br />";
            
            $hide_shipping_methods = get_option( 'hide_shipping_methods_guest' );
            if(!$hide_shipping_methods) {
                $hide_shipping_methods = array();
            }

            // List all shipping methods
            foreach( $zone['shipping_methods'] as $shipping_method ):
                $shipping_id = $shipping_method->id . ':' . $shipping_method->instance_id;
                ?> 
                <input id="<?php echo $shipping_id; ?>_guest" name="hide_shipping_methods_guest[]" type="checkbox" value="<?php echo $shipping_id; ?>" <?php checked( in_array( $shipping_id, $hide_shipping_methods ) ) ?> />
                <label for="<?php echo $shipping_id; ?>_guest"><?php echo $shipping_method->title; ?></label><br />
                <?php
            endforeach;

            echo "<br/ >";
        }, $zones);

        register_setting( 'th-custom', 'hide_shipping_methods_guest' );
    }

    /**
     * disable purchasing based on categories
     * NOTE: I switched the logic while developing, resulting in wrong variable namings.
     */
    add_filter( 'woocommerce_is_purchasable', 'th_hide_add_to_cart', 30, 2 );
    function th_hide_add_to_cart( $return_val, $product ) {
        // Get all enabled categories
        $deactivate_categories = get_option( 'categories_disabled' );
        if(!$deactivate_categories) { $deactivate_categories = array(); }
        $b2b_roles = th_return_option( 'b2b_roles' );

        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;

        // if the customer has a b2b role, all categories are enabled
        if( count(array_intersect( $b2b_roles, $roles ) ) ) {
            // only return categories for b2b customers, if present in customer meta
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

        // if product is not in disabled categorie, disable purchasing
        if( empty($deactivate_categories) || !has_term( $deactivate_categories, 'product_cat', $product->id ) ) {
            add_action('woocommerce_single_product_summary', 'message_hide_add_to_cart');
            return false;
        } else {
            return $return_val;
        }
    }

    // show message for disabled products instead of add to cart button
    function message_hide_add_to_cart () {
        echo "<p>Dieses Produkt steht momentan im Webshop nicht zum Verkauf.</p>";
    }

    /**
     * disable purchasing in table based on customer meta
     */
    add_action( 'wpto_table_query_args', 'th_hide_products_in_table', 1, 3 );
    function th_hide_products_in_table($a, $b, $c) {
        $user = get_current_user_id();
        $user_categories = get_the_author_meta('can_buy_categories', $user);
        if( $user_categories && $c['name'] == 'Reseller' ) {
            $user_categories_ids = [];
            $a['tax_query']['product_cat_IN']['terms'] = [];
            foreach( $user_categories as $category) {
                $category = get_term_by( 'slug', $category, 'product_cat' );
                if( $category->slug === 'karten' ){ continue; }
                $user_categories_ids[] = $category->term_id;
                // remove categorie from select
                echo "<script>filterToRemove.push('".$category->term_id."');</script>";
            }
            
            $a['tax_query']['product_cat_IN']['terms'] = $user_categories_ids;
        }

        return $a;
    }

    /**
     * Hack for composite plugin
     */
    function th_enqueue($hook) {
        // Only add to the edit.php admin page.
        if ('post.php' !== $hook) {
            return;
        }
        wp_enqueue_script('th_custom_admin_script', plugin_dir_url(__FILE__) . 'assets/admin.js');
    }
    
    /**
     * enque scripts
     */
    wp_enqueue_script('th_custom_script', plugin_dir_url(__FILE__) . 'assets/th-custom.js', array('jquery'));
    add_action('admin_enqueue_scripts', 'th_enqueue');


    /**
     * set product table quantity fields to 0 for b2b customers
     */
    add_filter("woocommerce_quantity_input_min","th_woocommerce_quantity_input");
    function th_woocommerce_quantity_input($a) {
        $b2b_roles = th_return_option( 'b2b_roles' );
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        
        // Falls der Kunde ein Stammkunde ist, beginnt die Menge bei 0
        if( count(array_intersect( $b2b_roles, $roles ) ) ) {
            return 0;
        }
        return $a;
    }

    /**
     * enque scripts for ajax cart update
     */
    function th_custom_srcipts () {
        wp_enqueue_script( 'table-js', plugin_dir_url(__FILE__) . 'assets/table.js', array( 'jquery' ), '', true );
        wp_enqueue_style('table-css', plugin_dir_url(__FILE__) . 'assets/table.css');
    }
    add_action( 'wp_enqueue_scripts', 'th_custom_srcipts' );

    /** functions for ajax cart update */
    add_filter( 'wp_ajax_update_cart', 'ajax_update_cart' );
    add_filter( 'wp_ajax_nopriv_update_cart', 'ajax_update_cart' );
        
    function ajax_update_cart() {
        // get posted products
        $products = $_POST['products'];
        $cart_items = array();

        // get actual cart contents
        foreach( WC()->cart->get_cart() as $cart_item ) {
            $cart_items[] = $cart_item;
        }

        // loop over products
        foreach ($products as $product_id => $qty) {
            // sanitize product fields
            $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($product_id));
            $quantity = filter_var($qty, FILTER_SANITIZE_STRING);
            $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
            $product_status = get_post_status($product_id);
            $exists = false;

            // check if item is already in cart
            foreach ( $cart_items as $cart_item ) {
                if( $cart_item['product_id'] === $product_id ){
                    $exists = true;
                }
            }

            // if the product is not in cart, add it
            if ($passed_validation && !$exists) {
                    WC()->cart->add_to_cart($product_id, $quantity);
                do_action('woocommerce_ajax_added_to_cart', $product_id);
            } else {
                // if it is in cart, only update the quantity
                $prod_unique_id = WC()->cart->generate_cart_id( $product_id );
                WC()->cart->set_quantity($prod_unique_id, $quantity);
            }
        }

        wp_die();
    }


    /**
     * Copyright Perlenfischer
     * Add a field to product meta to set the value is_copyright
     */
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
    // save is_copyright 
    add_action( 'woocommerce_process_product_meta', 'th_custom_save_custom_product_fields' );
    function th_custom_save_custom_product_fields($post_id) {
        $_custom_text_option = isset( $_POST['is_copyright'] ) ? 'yes' : '';
        update_post_meta( $post_id, 'is_copyright', $_custom_text_option );
    }

    /**
     * Copyright Perlenfischer
     * Show message in product meta if is_copyright is set
     */
    add_action( 'woocommerce_product_additional_information', 'th_get_custom_product_fields', 20 );
    function th_get_custom_product_fields() {
        global $post;
        $is_copyright = get_post_meta( $post->ID, 'is_copyright', true );
        if( $is_copyright ){
            echo '<em>Diese Karte verwendet urheberrechtlich geschützte Stempel von <a href="https://www.perlenfischerdesign.de" target="_blank">perlenfischer</a>.</em>';
        } 
    }

    /**
     * Show label images for bio and urdinkel, based on attributes
     */
    add_action('woocommerce_product_additional_information', 'th_bio_logo', 20);
    function th_bio_logo() {
        global $product;
        $is_bio = $product->get_attribute( 'bio' );
        $is_urdinkel = $product->get_attribute( 'typ' );
        $is_saison = get_post_meta( $product->id, 'br_labels' );
        if ( $is_saison[0]['label_from_post'] ) {
            if ( in_array( '5679', $is_saison[0]['label_from_post'] ) ) {
                echo '<em>Nur solange Vorrat. Dieses Produkt wird mit saisonalen Zutaten hergestellt.</em>';
            }
        }

        if ( $is_bio ) {
            $is_bio = explode(", ", $is_bio);

            if ( !is_array( $is_bio ) ) {
                $arr = array();
                $arr[] = $is_bio;
                $is_bio = $arr;
            }
            
        } else {
            $is_bio = array();
        }

        echo '<div class="zertifizierungen">';
        if( in_array("Bio Knospe", $is_bio) ) {
            echo '<div class="bio_logo"><a href="https://www.bio-suisse.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '/img/bio-knospe-logo.png"></a></div>';
        }
        if( in_array("Bio Suisse Knospe", $is_bio) ) {
            echo '<div class="bio_logo"><a href="https://www.bio-suisse.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '/img/bio-suisse-logo.png"></a></div>';
        }
        if( in_array("Bio Gourmet Knospe", $is_bio) ) {
            echo '<div class="bio_logo"><a href="https://www.bio-suisse.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '/img/bio-gourmet-logo.png"></a></div>';
        }
        if ( strtolower($is_urdinkel) == 'urdinkel' ) {
            echo '<div class="urdinkel_logo"><a href="https://www.urdinkel.ch" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) .'/img/urdinkel-logo.png"</a></div>';
        }
        echo "</div>";
    }

    /**
     * Check if max quantity of cards in cart is reached
     */
    add_action( 'woocommerce_after_cart_table', 'th_notice_max_qty' );
    add_action( 'woocommerce_checkout_after_terms_and_conditions', 'th_notice_max_qty' );
    function th_notice_max_qty() {
        $count_qty = 0;
        
        $restricted_category = 'karten';
        $max_num_products = get_option( 'max_cards' );
        
        foreach (WC()->cart->get_cart() as $cart_item_key=>$cart_item) {
            $count_qty += $cart_item['quantity'];
            
            if( has_term( $restricted_category, 'product_cat', $cart_item['product_id'] ) ) {
                // if max is reached, disable all purchase options and show a message instead
                if ( $count_qty > $max_num_products ) {
                    echo "<p class='woocommerce-error'>Für Bestellungen von mehr als " . $max_num_products . " Karten kontaktieren Sie bitte das Töpferhaus.</p>";
                    remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
                    add_filter('woocommerce_order_button_html', '__return_false' );
                }
            }
        }
    }

?>