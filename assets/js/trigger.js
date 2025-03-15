jQuery(function ($) {
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        $.ajax({
            url: aioDiscountAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'aio_check_cart_discounts',
                nonce: aioDiscountAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    console.log(response.data.message);
                    $(document.body).trigger('update_checkout'); // if needed
                }
            }
        });
    });
});

