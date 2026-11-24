(function () {
    'use strict';

    function initCountdowns() {
        document.querySelectorAll('.gwp-cd[data-end]').forEach(function (el) {
            if (el.dataset.initialized) return;
            el.dataset.initialized = 'true';

            var end   = new Date(el.dataset.end).getTime();
            var nodes = {};
            el.querySelectorAll('[data-u]').forEach(function (n) { nodes[n.dataset.u] = n; });

            function set(u, v) {
                if (nodes[u]) nodes[u].textContent = String(v).padStart(2, '0');
            }

            function tick() {
                var diff = Math.max(0, end - Date.now());
                set('d', Math.floor(diff / 86400000));
                set('h', Math.floor(diff / 3600000) % 24);
                set('m', Math.floor(diff / 60000) % 60);
                set('s', Math.floor(diff / 1000) % 60);
                if (diff === 0) {
                    el.classList.add('is-ended');
                    var title = el.querySelector('.gwp-cd__title');
                    if (title) title.lastChild.textContent = 'Sale ended';
                    clearInterval(timer);
                }
            }

            tick();
            var timer = setInterval(tick, 1000);
        });
    }

    document.addEventListener('DOMContentLoaded', initCountdowns);
    document.addEventListener('updated_cart_totals', initCountdowns);
    document.addEventListener('updated_checkout', initCountdowns);
    document.addEventListener('wc-blocks_updated_cart', initCountdowns);
})();
