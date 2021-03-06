<?php

/** 
 * show variations of products in loop, on top of thumbnail
 */

    /**
     * enque scripts for variatons in loop
     */
    function th_variations_in_loop_srcipts () {    
        wp_enqueue_style( 'th-variations-in-loop', plugin_dir_url(__FILE__) . '/assets/th-variations-in-loop.css');
        wp_enqueue_script( 'th-variations-in-loop-imagesloaded', plugin_dir_url(__FILE__) . '/assets/imagesloaded.pkgd.min.js', array('jquery'));
        wp_enqueue_script( 'th-variations-in-loop', plugin_dir_url(__FILE__) . '/assets/th-variations-in-loop.js', array('jquery', 'th-variations-in-loop-imagesloaded'));
    }
    add_action( 'wp_enqueue_scripts', 'th_variations_in_loop_srcipts' );

    add_filter( 'woocommerce_after_shop_loop_item_title', 'th_display_variations_in_loop' );
    function th_display_variations_in_loop() {
        
        global $product;

        if( $product->is_type( 'variable' )) {

            $attribute_keys = array_keys( $product->get_attributes() );
            $variations_data = [];

            ?> <div class="variations_in_loop"> <?php
            // get all variations and create a thumbnail with link to variation
            foreach($product->get_available_variations() as $variation ) {
                $variations_data[$variation['variation_id']] = $variation;
                ?>
                    <img 
                        src="<?php echo $variation['image']['gallery_thumbnail_src']; ?>" 
                        class="variations_in_loop_thumbnail"
                        data-variation-id="<?php echo $variation['variation_id']; ?>"
                        data-variation-img-src="<?php echo $variation['image']['src']; ?>"
                        data-variation-img-srcset="<?php echo $variation['image']['srcset']; ?>"
                        data-variation-attributes='<?php echo json_encode($variation['attributes']) ?>'
                        data-product-url="<?php echo get_permalink($product_id) ?>"
                    >
                <?php
            }
            ?> </div> <?php
        }
        
    }

    /**
     * allow overriding templates from plugin
     */
    remove_filter( 'wpto_item_final_loc', 'wpt_item_manage_from_theme' );
    add_filter( 'wpto_item_final_loc', 'th_display_variations_in_table', 1, 2 );

    function th_display_variations_in_table($file, $file_name) {
        $file_from_plugin = plugin_dir_path(__FILE__) . 'woo-product-table/items/' . $file_name . '.php';
        if (file_exists( $file_from_plugin ) ) {
            return $file_from_plugin;
        }

        return $file;
    }
?>
