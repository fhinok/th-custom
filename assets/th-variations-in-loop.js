jQuery(function ($) {
    "use strict",
    $(document).ready(function () {
    // in loop 
        // Erste Version aktiv
        $('.variations_in_loop_thumbnail:first-child').addClass('active');
        // Richtige Variante Laden bei Klick
        $('.variations_in_loop_thumbnail').on('click', function() {
            var variation_attributes = $(this).data( 'variation-attributes' );
            var variation_url = "";
            Object.keys(variation_attributes).forEach( function( key ) {
                variation_url += "?"+key+"="+variation_attributes[key];
            })

            var product_url = $(this).data( 'product-url' );
            if ( $(this).hasClass('active') ) {
                location.href = product_url + variation_url;
            }

            $(this).closest('.product').find( '.variations_in_loop_thumbnail' ).removeClass('active');
            $(this).addClass('active');

            // Bild auswechseln
            var variation_img_src = $(this).data('variation-img-src');
            var variation_img_srcset = $(this).data('variation-img-srcset');
            var product_img = $(this).closest( '.product' ).find( '.wp-post-image' );
            product_img.attr('src', variation_img_src);
            product_img.attr('srcset', variation_img_srcset);

            // Alle Links auswechseln
            var product_links = $(this).closest( '.product' ).find('a');
            product_links.attr('href', product_url + variation_url);
        });

    // in table
        function th_variations_in_table() {
            // Erste Version aktiv
            $('.variations_in_table_thumbnail:first-child').addClass('active');

            $('.variations_in_table_thumbnail').on('click', function() {
                var variation_id = $(this).data( 'variation-id' );
                var variation_attributes = $(this).data( 'variation-attributes' );
                var variation_url = "";
                Object.keys(variation_attributes).forEach( function( key ) {
                    variation_url += "?"+key+"="+variation_attributes[key];
                })

                var product_url = $(this).data( 'product-url' );
                if ( $(this).hasClass('active') ) {
                    location.href = product_url + variation_url;
                }

                $(this).closest('.wpt_variation').find( '.variations_in_table_thumbnail' ).removeClass('active');
                $(this).addClass('active');

                // Produkte ID auswechseln
                $(this).closest('.wpt_row').data('product_id', variation_id);

                // Bild auswechseln
                var variation_img_src = $(this).data('variation-img-src');
                var variation_img_srcset = $(this).data('variation-img-srcset');
                var product_img = $(this).closest( '.wpt_row' ).find( '.wpt_thumbnails_popup img' );
                product_img.attr('src', variation_img_src);
                product_img.attr('srcset', variation_img_srcset);
                
                var product_popup = $(this).closest( '.wpt_row' ).find( '.wpt_thumbnails_popup' );
                product_popup.data( 'url', variation_img_src);

            })
        }
        $(document).on('wc_fragments_refreshed', function() {
            th_variations_in_table();
        });
    });
});