<?php

global $product;

        if( $product->is_type( 'variable' )) {

            $attribute_keys = array_keys( $product->get_attributes() );
            $variations_data = [];

            ?> <div class="variations_in_table"> <?php
            foreach($product->get_available_variations() as $variation ) {
                $variations_data[$variation['variation_id']] = $variation;
                ?>
                    <img 
                        src="<?php echo $variation['image']['gallery_thumbnail_src']; ?>" 
                        class="variations_in_table_thumbnail"
                        data-variation-id="<?php echo $variation['variation_id']; ?>"
                        data-variation-img-src="<?php echo $variation['image']['src']; ?>"
                        data-variation-img-srcset="<?php echo $variation['image']['srcset']; ?>"
                        data-variation-attributes='<?php echo json_encode($variation['attributes']) ?>'
                        data-product-url="<?php echo get_permalink($product_id) ?>"
                    >
                <?php
            }
            ?> </div> <?php
        } ?>
<?php