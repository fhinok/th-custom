<?php

    wp_enqueue_style( 'th-variations-in-loop', plugin_dir_url(__FILE__) . '/assets/th-variations-in-loop.css');
    wp_enqueue_script( 'th-variations-in-loop', plugin_dir_url(__FILE__) . '/assets/th-variations-in-loop.js', array('jquery'));

    add_filter( 'woocommerce_after_shop_loop_item_title', 'th_display_variations_in_loop' );
 
    function th_display_variations_in_loop() {
        
        global $product;

        if( $product->is_type( 'variable' )) {

            $attribute_keys = array_keys( $product->get_attributes() );
            $variations_data = [];

            ?> <div class="variations_in_loop"> <?php
            foreach($product->get_available_variations() as $variation ) {
                $variations_data[$variation['variation_id']] = $variation;
                ?>
                    <img 
                        src="<?php echo $variation['image']['gallery_thumbnail_src']; ?>" 
                        class="variations_in_loop_thumbnail"
                        data-variation-id="<?php echo $variation['variation_id']; ?>"
                        data-variation-img-src="<?php echo $variation['image']['src']; ?>"
                        data-variation-img-srcset="<?php echo $variation['image']['srcset']; ?>"
                        data-variation-attribute="<?php echo $variation['attributes']['attribute_pa_ausfuehrung'] ?>"
                        data-product-url="<?php echo get_permalink($product_id) ?>"
                    >
                <?php
            }
            ?> </div> <?php
        }
        
    }
?>
