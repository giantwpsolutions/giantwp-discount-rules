/**
 * WooCommerce Blocks Cart - Free Badge Display
 * Universal solution for ALL themes (block-based and traditional)
 */

(function() {
    'use strict';

    /**
     * Add Free badge to cart items - Universal approach
     */
    function addFreeBadgeUniversal() {
        // Find all elements with 0.00 price in the TOTAL column
        const totalColumns = document.querySelectorAll('.wc-block-cart-item__total, td.product-subtotal');

        totalColumns.forEach(totalColumn => {
            // Skip if already has badge
            if (totalColumn.querySelector('.gwpdr-free-badge:not([style*="display: none"])')) {
                return;
            }

            const priceText = totalColumn.textContent || '';
            const hasZeroPrice = priceText.includes('0.00') || priceText.includes('0,00');

            if (hasZeroPrice) {
                // Check if this row has a free item (look for the hidden badge in metadata)
                const row = totalColumn.closest('.wc-block-cart-items__row, tr.cart_item');
                if (!row) {
                    return;
                }

                const hasFreeItemData = row.innerHTML.includes('gwpdr-free-badge');

                if (hasFreeItemData) {
                    // Create the Free badge
                    const freeBadge = document.createElement('span');
                    freeBadge.className = 'gwpdr-free-badge gwpdr-total-badge';
                    freeBadge.style.cssText = 'display: inline-block !important; background: #000 !important; color: #fff !important; padding: 2px 8px !important; border-radius: 3px !important; font-size: 11px !important; font-weight: 600 !important; text-transform: uppercase !important; margin-left: 5px !important;';
                    freeBadge.textContent = 'Free';

                    // Find the price value element
                    const priceValue = totalColumn.querySelector('.wc-block-components-product-price__value, .wc-block-formatted-money-amount, .amount');

                    if (priceValue) {
                        priceValue.appendChild(freeBadge);
                    } else {
                        totalColumn.appendChild(freeBadge);
                    }
                }
            }
        });
    }

    /**
     * Initialize and observe for changes
     */
    function initialize() {
        // Run initially
        addFreeBadgeUniversal();

        // Watch for any changes in the document
        const observer = new MutationObserver((mutations) => {
            // Debounce to avoid excessive calls
            clearTimeout(window.gwpdrDebounceTimer);
            window.gwpdrDebounceTimer = setTimeout(() => {
                addFreeBadgeUniversal();
            }, 100);
        });

        // Observe the entire body for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        // Also run on various events
        const events = [
            'DOMContentLoaded',
            'load',
            'wc-blocks_updated_cart',
            'wc-blocks_added_to_cart',
            'wc-blocks_removed_from_cart',
            'updated_cart_totals',
            'updated_wc_div'
        ];

        events.forEach(event => {
            document.addEventListener(event, () => {
                setTimeout(addFreeBadgeUniversal, 50);
            });
        });

        // Run periodically for first 5 seconds (catches late renders)
        let attempts = 0;
        const periodicCheck = setInterval(() => {
            addFreeBadgeUniversal();
            attempts++;
            if (attempts > 10) {
                clearInterval(periodicCheck);
            }
        }, 500);
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

})();
