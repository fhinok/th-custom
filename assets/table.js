jQuery(function ($) {
  "use strict",
    $(document).ready(function () {
      // Easy mode
      //   $("body").on("change", ".qty", function () {
      //     var checkbox = $(this).closest("tr").find(".wpt_tabel_checkbox");
      //     if ($(this).val() >= 1) {
      //       if (checkbox.prop("checked", false)) {
      //         checkbox.click();
      //       }
      //     } else {
      //       checkbox.prop("checked", false);
      //     }
      //   });

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
            complete: function (res) {
              $(document.body).trigger("updated_cart_totals");
              $(document.body).trigger("wc_fragments_refreshed"),
              $(document.body).trigger("wc_fragments_refresh"),
              $(document.body).trigger("wc_fragment_refresh");
            },
          });
        }, 500)
      });
    });
});
