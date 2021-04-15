var filterToRemove = [];
jQuery(function ($) {
    "use strict",
    $( document ).ready( function () {
        var $search = $('.search_select_product_cat option');
        $search.each(function( index, value ) {
            if ( !filterToRemove.includes( $( value ).val() ) ) {
                $( value ).remove();
            }
        });
    });
});