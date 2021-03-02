jQuery(function ($) {
  "use strict",
    $(document).ready(function () {
      get_cart_qty();

      //   Ajax Mode
      $("body").on("change", ".qty", function () {
        setTimeout(() => {
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
          });
        }, 500)
      });

      // Load qtys from cart to inputs
      function get_cart_qty() {
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
          }
        })
      }

    });
});
