jQuery(function ($) {
    "use strict",
    $(document).ready(function () {
        // Produktbild wechseln bei mouseover
        $('.variations_in_loop_thumbnail').hover(function() {
            $(this).toggleClass('active');

            var variation_img_src = $(this).data('variation-img-src');
            var variation_img_srcset = $(this).data('variation-img-srcset');
            
            var product_img = $(this).closest( '.product' ).find( '.wp-post-image' );
            product_img.attr('src', variation_img_src);
            product_img.attr('srcset', variation_img_srcset);

        });

        // Richtige Variante Laden bei Klick
        $('.variations_in_loop_thumbnail').on('click', function() {
            var variation_attribute = $(this).data( 'variation-attribute' );
            var product_url = $(this).closest( '.product' ).find('a').attr('href');

            location.href=product_url + "?attribute_pa_ausfuehrung=" + variation_attribute;
        });
    });
});