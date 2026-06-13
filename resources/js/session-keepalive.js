/**
 * Keeps the Laravel session alive while a tab is open by pinging the server
 * before the server-side idle timeout (SESSION_LIFETIME, default 120 min).
 * Also refreshes the CSRF token in the page so forms stay submittable.
 */
(function () {
    // Ping every 10 minutes — well within the default 120-minute session lifetime.
    const INTERVAL_MS = 10 * 60 * 1000;

    function refreshCsrfToken(token) {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) {
            meta.content = token;
        }
        document.querySelectorAll('input[name="_token"]').forEach((input) => {
            input.value = token;
        });
    }

    function ping() {
        fetch('/session/keep-alive', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((response) => (response.ok ? response.json() : Promise.reject()))
            .then((data) => {
                if (data.csrf_token) {
                    refreshCsrfToken(data.csrf_token);
                }
            })
            .catch(() => {});
    }

    // Refresh session when the user returns to a backgrounded tab.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            ping();
        }
    });

    setInterval(ping, INTERVAL_MS);
})();
