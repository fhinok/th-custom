jQuery(function ($) {
    "use strict",
    $(document).ready(function () {
        th_variations_in_loop();
    // in loop 
        function th_variations_in_loop() {
            $('.variations_in_loop_thumbnail:first-child').each( (index, element) => {
                swap_variation( $(element) ) ;
            })
        }

        function th_variations_in_table() {
            // activate first variaton by default
            $('.variations_in_table_thumbnail:first-child').each( (index, element) => {
                swap_variation( $(element) ) ;
            })
        }

        function swap_variation( variation ) {
            var variation_id = variation.data( 'variation-id' );
            var product_imgs = [variation.closest( '.wpt_row' ).find( '.wpt_thumbnails_popup img' ), variation.closest( '.product' ).find( '.wp-post-image' )];
            $.each(product_imgs, (key, product_img) => {
                $(product_img).addClass('loading');
            });

            variation.closest('.wpt_variation').find( '.variations_in_table_thumbnail' ).removeClass('active');
            variation.closest('.product').find( '.variations_in_loop_thumbnail' ).removeClass('active');
            variation.addClass('active');

            // change id
            variation.closest('.wpt_row').data('product_id', variation_id);

            // change picture
            var variation_img_src = variation.data('variation-img-src');
            var variation_img_srcset = variation.data('variation-img-srcset');

            var product_imgs = [variation.closest( '.wpt_row' ).find( '.wpt_thumbnails_popup img' ), variation.closest( '.product' ).find( '.wp-post-image' )];
            $.each(product_imgs, (key, product_img) => {
                product_img.attr('src', variation_img_src);
                product_img.attr('srcset', variation_img_srcset);
            });
            
            var product_popup = variation.closest( '.wpt_row' ).find( '.wpt_thumbnails_popup' );
            product_popup.data( 'url', variation_img_src);

            // change links in loop
            var product_links = variation.closest( '.product' ).find('a');
            product_links.attr('href', build_url(variation));

            $.each(product_imgs, (key, product_img) => {
                $(product_img).imagesLoaded(() => {
                    $(product_img).removeClass('loading');
                });
            });
        }

        function build_url(variation) {
            var variation_attributes = variation.data( 'variation-attributes' );
            var variation_url = "";
            Object.keys(variation_attributes).forEach( function( key ) {
                variation_url += "?"+key+"="+variation_attributes[key];
            })
            var product_url = variation.data( 'product-url' );

            return product_url + variation_url;
        }

        function click_bind() {
            // bind click event to all variations
            // required because of ajax table and pagination
            $('.variations_in_table_thumbnail').unbind( "click" )
            $('.variations_in_table_thumbnail').on('click', function() {
                // if clicked variation is the active one, open product page
                if ( $(this).hasClass('active') ) {
                    location.href = build_url( $(this) );
                } else {
                    swap_variation( $(this) );
                }
            })

            // if clicked variation is the active one, open product page
            $('.variations_in_loop_thumbnail').on('click', function() {
                if ( $(this).hasClass('active') ) {
                    location.href = build_url( $(this) );
                } else {
                    swap_variation( $(this) );
                }
            });

        }

        $(document).on('wc_fragments_refreshed', function() {
            click_bind()
            th_variations_in_table();
        });

        // Trigger Variatonen-Script nach anwenden von Filter
        $(document).on('berocket_ajax_products_loaded', function() {
            click_bind()
            th_variations_in_loop();
        });

        click_bind();
    });
});