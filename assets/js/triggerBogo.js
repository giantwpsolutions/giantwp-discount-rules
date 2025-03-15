document.addEventListener('DOMContentLoaded', function () {
    console.log('[AIO BOGO]: JS Loaded ✅');

    // Detect quantity input changes directly
    document.body.addEventListener('change', function (e) {
        if (e.target.matches('input.qty')) {
            console.log('[AIO BOGO]: Quantity changed, firing AJAX');

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
                    console.log('[AIO BOGO]: AJAX result', data);
                    if (data.fragments) {
                        for (const selector in data.fragments) {
                            const el = document.querySelector(selector);
                            if (el) el.innerHTML = data.fragments[selector];
                        }
                    }
                });
        }
    });
});
