/**
 * Service Worker — Permission & Notification handler
 *
 * Handles:
 * - Push notification display
 * - Notification click → focus/open app
 * - Background sync (future use)
 */

const APP_NAME = self.registration.scope;

// ── Install ───────────────────────────────────────────────────────────────────

self.addEventListener('install', (event) => {
    // Activate immediately, no waiting for old clients to close
    event.waitUntil(self.skipWaiting());
});

// ── Activate ──────────────────────────────────────────────────────────────────

self.addEventListener('activate', (event) => {
    // Take control of all open clients right away
    event.waitUntil(self.clients.claim());
});

// ── Push Notifications ────────────────────────────────────────────────────────

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload;
    try {
        payload = event.data.json();
    } catch {
        payload = { title: 'Notifikasi', body: event.data.text() };
    }

    const title = payload.title ?? 'Notifikasi';
    const options = {
        body: payload.body ?? '',
        icon: payload.icon ?? '/apple-touch-icon.png',
        badge: payload.badge ?? '/favicon.ico',
        image: payload.image ?? undefined,
        data: {
            url: payload.url ?? '/',
            ...( payload.data ?? {} ),
        },
        tag: payload.tag ?? 'default',
        renotify: payload.renotify ?? false,
        requireInteraction: payload.requireInteraction ?? false,
        silent: payload.silent ?? false,
        vibrate: payload.vibrate ?? [200, 100, 200],
        actions: payload.actions ?? [],
    };

    event.waitUntil(
        self.registration.showNotification(title, options),
    );
});

// ── Notification Click ────────────────────────────────────────────────────────

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url ?? '/';

    event.waitUntil(
        self.clients
            .matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Focus existing tab if already open
                for (const client of clientList) {
                    if (client.url === targetUrl && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Otherwise open a new tab
                if (self.clients.openWindow) {
                    return self.clients.openWindow(targetUrl);
                }
            }),
    );
});

// ── Notification Close ────────────────────────────────────────────────────────

self.addEventListener('notificationclose', () => {
    // Reserved for analytics if needed
});

// ── Message from main thread ──────────────────────────────────────────────────

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
