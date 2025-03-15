document.addEventListener('DOMContentLoaded', function () {
    console.log('[AIO BOGO]: JS Loaded ✅');

    // 🧠 Woo blocks fire this after cart update is complete
    document.body.addEventListener('wc-blocks_cart_updated', function () {
        console.log('[AIO BOGO]: wc-blocks_cart_updated triggered 🚀');

        fetch(aioDiscountBogo.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'aio_check_bogo_discounts',
                nonce: aioDiscountBogo.nonce
            })
        })
            .then(res => res.json())
            .then(data => {
                console.log('[AIO BOGO]: AJAX success:', data);
                if (data.fragments) {
                    for (const selector in data.fragments) {
                        const el = document.querySelector(selector);
                        if (el) el.innerHTML = data.fragments[selector];
                    }
                    document.body.dispatchEvent(new CustomEvent('wc-fragments_refreshed'));
                }
            })
            .catch(err => {
                console.error('[AIO BOGO]: AJAX failed ❌', err);
            });
    });
});
