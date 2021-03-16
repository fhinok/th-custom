jQuery(function ($) {
    "use strict",
    $(document).ready(function () {

        // Erste Version aktiv
        $('.variations_in_loop_thumbnail:first-child').addClass('active');

        // Richtige Variante Laden bei Klick
        $('.variations_in_loop_thumbnail').on('click', function() {
            var variation_attribute = $(this).data( 'variation-attribute' );
            var old_url = $(this).closest( '.product' ).find('a').attr('href');
            old_url = old_url.replace(/([?]?attribute_pa_ausfuehrung=)+(\w+)/g, '');
            if ( $(this).hasClass('active') ) {
                location.href=old_url + "?attribute_pa_ausfuehrung=" + variation_attribute;
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
            product_links.attr('href', old_url + "?attribute_pa_ausfuehrung=" + variation_attribute);

        });

    });
});