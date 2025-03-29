jQuery(function ($) {
    function triggerDiscountRecalc(method) {
        $.post(gwpdr_checkout_ajax.ajax_url, {
            action: 'gwpdr_set_payment_method',
            payment_method: method,
            security: gwpdr_checkout_ajax.nonce,
        }, function (response) {
            if (response.success) {
                // console.log("✅ Payment method saved in session:", method);

                // WooCommerce classic checkout will handle this
                $('body').trigger('update_checkout');
            } else {
                console.warn("❌ Failed to save payment method:", response);
            }
        });
    }

    // Handle both classic and block-based payment method selections
    $(document.body).on('change', 'input[name="payment_method"], input[name="radio-control-wc-payment-method-options"]', function () {
        const selectedMethod = $(this).val();
        if (selectedMethod) {
            // console.log("✅ Payment method selected:", selectedMethod);
            triggerDiscountRecalc(selectedMethod);
        }
    });
});
