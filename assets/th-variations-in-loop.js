jQuery(function ($) {
    "use strict",
    $(document).ready(function () {
        th_variations_in_loop();
    // in loop 
        function th_variations_in_loop() {
            $('.variations_in_loop_thumbnail:first-child').each( (index, element) => {
                console.log('first');
                swap_variation( $(element) ) ;
            })
        }

        function th_variations_in_table() {
            // Erste Version aktiv
            $('.variations_in_table_thumbnail:first-child').each( (index, element) => {
                swap_variation( $(element) ) ;
            })
        }

        function swap_variation( variation ) {
            var variation_id = variation.data( 'variation-id' );

            variation.closest('.wpt_variation').find( '.variations_in_table_thumbnail' ).removeClass('active');
            variation.closest('.product').find( '.variations_in_loop_thumbnail' ).removeClass('active');
            variation.addClass('active');

            // Produkte ID auswechseln
            variation.closest('.wpt_row').data('product_id', variation_id);

            // Bild auswechseln
            var variation_img_src = variation.data('variation-img-src');
            var variation_img_srcset = variation.data('variation-img-srcset');

            var product_img = variation.closest( '.wpt_row' ).find( '.wpt_thumbnails_popup img' );
            product_img.attr('src', variation_img_src);
            product_img.attr('srcset', variation_img_srcset);

            product_img = variation.closest( '.product' ).find( '.wp-post-image' );
            product_img.attr('src', variation_img_src);
            product_img.attr('srcset', variation_img_srcset);
            
            var product_popup = variation.closest( '.wpt_row' ).find( '.wpt_thumbnails_popup' );
            product_popup.data( 'url', variation_img_src);

            // Alle Links auswechseln in loop
            var product_links = variation.closest( '.product' ).find('a');
            product_links.attr('href', build_url(variation));
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

        $('.variations_in_table_thumbnail').on('click', function() {
            if ( $(this).hasClass('active') ) {
                location.href = build_url( $(this) );
            } else {
                swap_variation( $(this) );
            }
        })

        $(document).on('wc_fragments_refreshed', function() {
            th_variations_in_table();
        });

        $('.variations_in_loop_thumbnail').on('click', function() {
            if ( $(this).hasClass('active') ) {
                location.href = build_url( $(this) );
            } else {
                swap_variation( $(this) );
            }
        });

        // Trigger Variatonen-Script nach anwenden von Filter
        $(document).on('berocket_ajax_products_loaded', function() {
            th_variations_in_loop();
        });
    });
});