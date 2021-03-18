jQuery(function ($) {
  "use strict",
    $(document).ready(function () {
      var timeout;

      $("body").on("change", ".qty", function () {
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
              $(document.body).trigger("wc_fragments_refreshed"),
              $(document.body).trigger("wc_fragments_refresh"),
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

      // Load qtys from cart to inputs
      function get_cart_qty() {
        $(".qib-container").addClass('loading');
        // Call to php to get cart items
        $.ajax({
          type: "POST",
          url: wc_add_to_cart_params.ajax_url,
          data: {
            action: "get_cart_qty"
          },
          success: (res) => {
            Object.keys(res).forEach((key) => {
              var item = $("[data-product_id="+key+"]").find('.qty');
              item.val( res[key] );
            })
            $(".qib-container").removeClass('loading');

          }
        })
      }
      // Watch for loaded fragments (which means a filter has been applied)
      $(document).on('wc_fragments_refreshed', function() {
        get_cart_qty();
      });
      $("body").on("removed_from_cart", function() {
        $(this).find(".qty").val(0);
        get_cart_qty();
      });
    });
});
