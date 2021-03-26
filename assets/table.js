jQuery(function ($) {
  "use strict",
    $(document).ready(function () { 
      // Save on input change
      var timeout;
      $("#wpt_table").on("change", ".qty", function () {
        var qty_container = $(this).closest('.qib-container');
        $(this).prop('disabled', true);
        if (timeout !== undefined ) {
          clearTimeout( timeout );
        }
        
        timeout = setTimeout(() => {
          // display loading
          qty_container.addClass('loading');
          var qty = $(this).val();
          var product_id = $(this).closest("tr").data("product_id");
          $.ajax({
            type: "POST",
            url: wc_add_to_cart_params.ajax_url,
            data: {
              action: "update_cart",
              product_id: product_id,
              quantity: qty,
            },
            complete: () => {
              $(document.body).trigger("updated_cart_totals");
              // $(document.body).trigger("wc_fragments_refresh");
              $(document.body).trigger("wc_fragment_refresh");
              
              
            },
            success: () => {
              // remove loading
              qty_container.removeClass('loading');
              $(this).prop('disabled', false);
            }
          });
        }, 500)
      });

      var cart_old = "";
      // Load qtys from cart to inputs
      function get_cart_qty() {
        console.log("trigger qty");
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
          var qty_container = $("[data-product_id="+item+"]").find('.qib-container');
          qty_container.addClass('loading');

          $("[data-product_id="+item+"]").find('.qty').val(0);

          qty_container.removeClass('loading');
        })
        cart_old = cart_new;
      }


      $(document).on('wc_fragments_refreshed', function() {
        console.log("trigger refresh");
        get_cart_qty();
      });
      $("body").on("removed_from_cart", function() {
        set_qty_zero()
      });
    });
});

// Store mini cart in var and compare with new on 'removed from cart'