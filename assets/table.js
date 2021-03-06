jQuery(function ($) {
  "use strict",
    $(document).ready(function () {
      var cart_old;
      var timeout;
      var changed_products = {};

      function set_cart_qty (changed_products) {
        // add loading indicator to each changed product
        $.each(changed_products, (item) => {
          $("[data-product_id="+item+"]").find('.qib-container').addClass('loading');
        })

        // process changed products via ajax post
        $.ajax({
          type: "POST",
          url: wc_add_to_cart_params.ajax_url,
          data: {
            action: "update_cart",
            products: changed_products
          },
          success: () => {
            // remove loading
            $.each(changed_products, (item) => {
              $("[data-product_id="+item+"]").find('.qib-container').removeClass('loading');
            })
          },
          complete: () => {
            // trigger cart update events
            $(document.body).trigger("updated_cart_totals");
            $(document.body).trigger("wc_fragment_refresh");              
          },
        });
      }
      
      // store products in array after qty changes
      $("#wpt_table").on("change", ".qty", function () {
        var qty = $(this).val();
        var product_id = $(this).closest("tr").data("product_id");
        changed_products[product_id] = qty;

        // prevent from submiting every change individually
        if (timeout !== undefined ) {
          clearTimeout( timeout );
        }

        timeout = setTimeout(() => {
          // as soon as no more changes are made, update via ajax
          set_cart_qty(changed_products);

          // clear changed_products
          changed_products = {};
        }, 750)
      });

      // Load qtys from cart to inputs
      function get_cart_qty() {
        // get contents of cart
        var cart = JSON.parse(sessionStorage.getItem( wc_cart_fragments_params.fragment_name ));
        cart_json = {}
        cart_html = $.parseHTML(cart['div.widget_shopping_cart_content']) 

        $(cart_html).find('li').each(function(key, value) {
          sku = $(value).find('.remove_from_cart_button').data('product_sku');
          qty = $(value).find('.quantity')[0].childNodes[0].nodeValue
          qty = parseInt(qty)
          cart_json[sku] = qty
        });
        
        // find matching qty input and set to value
        $.each(cart_json, (key, val) => {
          var qty_container = $("[data-sku="+key+"]").find('.qib-container');
          qty_container.addClass('loading');

          $("[data-sku="+key+"]").find('.qty').val(val);

          qty_container.removeClass('loading');
        })

        // store cart_json to cart_old
        cart_old = cart_json;
      }
      
      function set_qty_zero() {
        // get the cart contents after the item was removed
        // var cart_new = JSON.parse(sessionStorage.getItem( wc_cart_fragments_params.fragment_name ));
        // cart_new = JSON.parse(cart_new.wpt_per_product);
        var cart = JSON.parse(sessionStorage.getItem( wc_cart_fragments_params.fragment_name ));
        cart_new = {}
        cart_html = $.parseHTML(cart['div.widget_shopping_cart_content']) 

        $(cart_html).find('li').each(function(key, value) {
          sku = $(value).find('.remove_from_cart_button').data('product_sku');
          qty = $(value).find('.quantity')[0].childNodes[0].nodeValue
          qty = parseInt(qty)
          cart_new[sku] = qty
        });

        // remove all items from cart_old which are still in the cart
        $.each(cart_new, (item) => {
          delete cart_old[item];
        })
 
        // remaining items in cart_old have to be the removed ones 
        $.each(cart_old, (item) => {
          // set qty to zero
          $("[data-sku="+item+"]").find('.qib-container').addClass('loading');

          $("[data-sku="+item+"]").find('.qty').val(0);

          $("[data-sku="+item+"]").find('.qib-container').removeClass('loading');
        })
        // store cart_new to cart_old
        cart_old = cart_new;
      }


      $(document).on("wc_fragments_refreshed", function() {
        // fired when cart changes
        get_cart_qty();
      });
      $("body").on("removed_from_cart", function() {
        // fired when item gets removed from cart
        set_qty_zero()
      });
    });

    // Replace category filters with product link per row
    $(document).ready(function() {
      $('.wpt_row').each(function() {
        product_url = $(this).data('href');
        $(this).find('a').attr('href', product_url);
      })
    });
});
