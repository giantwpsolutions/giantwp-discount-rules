jQuery(function ($) {
    console.log("[AIO BOGO]: JS Loaded ✅");

    function runBogoTrigger() {
        $.ajax({
            url: aioDiscountBogo.ajax_url,
            method: "POST",
            data: {
                action: "aio_check_bogo_discounts",
                nonce: aioDiscountBogo.nonce,
            },
            success: function (response) {
                if (response.success) {
                    console.log("[AIO BOGO]: Triggered via AJAX 🚀");
                    $(document.body).trigger("update_checkout");
                }
            },
        });
    }

    // Classic Cart + Checkout
    $(document.body).on("updated_cart_totals updated_checkout", function () {
        runBogoTrigger();
    });

    // 🧠 For Block Cart — Observe changes in quantity fields
    const observer = new MutationObserver(function () {
        runBogoTrigger();
    });

    const interval = setInterval(function () {
        const blockCart = document.querySelector(".wc-block-cart");
        if (blockCart) {
            observer.observe(blockCart, {
                childList: true,
                subtree: true,
            });
            console.log("[AIO BOGO]: Observing Block Cart ✅");
            clearInterval(interval);
        }
    }, 500);
});

(function ($) {
    $(document.body).on('change', '.wc-block-cart .quantity input', function () {
        console.log('[AIO BOGO] Quantity changed – forcing cart update');
        $(document.body).trigger('wc-blocks_refresh_cart_totals');
    });

    $(document.body).on('updated_wc_div updated_cart_totals', function () {
        console.log('[AIO BOGO] Cart updated');
    });
})(jQuery);
