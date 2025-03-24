jQuery(function ($) {
    $(document.body).on('updated_cart_totals updated_checkout', function () {
        $.ajax({
            url: dbDiscountAjax.ajax_url,
            method: 'POST',
            data: {
                action: 'db_check_cart_discounts',
                nonce: dbDiscountAjax.nonce
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

