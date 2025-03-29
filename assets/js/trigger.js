jQuery(function ($) {
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        $.ajax({
            url: gwpdrDiscountAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'gwpdr_check_cart_discounts',
                nonce: gwpdrDiscountAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    // console.log(response.data.message);
                    $(document.body).trigger('update_checkout'); // if needed
                }
            }
        });
    });
});

