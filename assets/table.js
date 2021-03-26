jQuery(function ($) {
  "use strict",
    $(document).ready(function () {
      // Save all changed inputs to array and after timeout post to ajax 
      // Save on input change
      var timeout;
      var changed_products = {};
      $("#wpt_table").on("change", ".qty", function () {
        if (timeout !== undefined ) {
          clearTimeout( timeout );
        }
        
        var qty = $(this).val();
        var product_id = $(this).closest("tr").data("product_id");
        
        changed_products[product_id] = qty;

        
        timeout = setTimeout(() => {
          $.each(changed_products, (item) => {
            $("[data-product_id="+item+"]").find('.qib-container').addClass('loading');
          })
          $.ajax({
            type: "POST",
            url: wc_add_to_cart_params.ajax_url,
            data: {
              action: "update_cart",
              products: changed_products
            },
            success: (res) => {
              console.log(res);
              // remove loading
              $.each(changed_products, (item) => {
                $("[data-product_id="+item+"]").find('.qib-container').removeClass('loading');
              })
            },
            error: (err) => {
              console.error(err.responseText);
            },
            complete: () => {
              changed_products = {};
              $(document.body).trigger("updated_cart_totals");
              $(document.body).trigger("wc_fragment_refresh");              
            },

          });
        }, 500)
      });

      var cart_old;
      // Load qtys from cart to inputs
      function get_cart_qty() {
        var cart = JSON.parse(sessionStorage.getItem( wc_cart_fragments_params.fragment_name ));
        cart_json = JSON.parse(cart.wpt_per_product)
        $.each(cart_json, (key, val) => {
          var qty_container = $("[data-product_id="+key+"]").find('.qib-container');
          qty_container.addClass('loading');

          $("[data-product_id="+key+"]").find('.qty').val(val);

          qty_container.removeClass('loading');
        })
        cart_old = cart_json;
      }
      
      function set_qty_zero() {
        var cart_new = JSON.parse(sessionStorage.getItem( wc_cart_fragments_params.fragment_name ));
        cart_new = JSON.parse(cart_new.wpt_per_product);
        // entferne alle items, welche immernoch im warenkorb sind
        $.each(cart_new, (item) => {
          delete cart_old[item];
        })

        // die hier noch vorhandenen items sind die entfernten, also setze .qty auf 0 
        $.each(cart_old, (item) => {
          $("[data-product_id="+item+"]").find('.qib-container').addClass('loading');

          $("[data-product_id="+item+"]").find('.qty').val(0);

          $("[data-product_id="+item+"]").find('.qib-container').removeClass('loading');
        })
        cart_old = cart_new;
      }


      $(document).on('wc_fragments_refreshed', function() {
        get_cart_qty();
      });
      $("body").on("removed_from_cart", function() {
        set_qty_zero()
      });
    });
});

// Store mini cart in var and compare with new on 'removed from cart'