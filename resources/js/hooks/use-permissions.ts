import { useCallback, useEffect, useRef, useState } from 'react';

export type PermissionStatus = 'granted' | 'denied' | 'prompt' | 'unsupported';

export interface PermissionState {
    notification: PermissionStatus;
    camera: PermissionStatus;
    microphone: PermissionStatus;
    geolocation: PermissionStatus;
}

export interface UsePermissionsReturn {
    /** Current status of each permission */
    permissions: PermissionState;
    /** Whether the Service Worker is registered and ready */
    swReady: boolean;
    /** Request notification permission (also registers SW) */
    requestNotification: () => Promise<PermissionStatus>;
    /** Request camera access */
    requestCamera: () => Promise<PermissionStatus>;
    /** Request microphone access */
    requestMicrophone: () => Promise<PermissionStatus>;
    /** Request camera + microphone together */
    requestCameraAndMicrophone: () => Promise<PermissionStatus>;
    /** Request geolocation */
    requestGeolocation: () => Promise<PermissionStatus>;
    /** Request all permissions at once */
    requestAll: () => Promise<PermissionState>;
}

const INITIAL_STATE: PermissionState = {
    notification: 'prompt',
    camera: 'prompt',
    microphone: 'prompt',
    geolocation: 'prompt',
};

/** Read current permission status without triggering a prompt */
async function queryPermission(
    name: PermissionName | 'camera' | 'microphone',
): Promise<PermissionStatus> {
    if (!navigator.permissions) {
        return 'unsupported';
    }
    try {
        const result = await navigator.permissions.query({
            name: name as PermissionName,
        });
        return result.state as PermissionStatus;
    } catch {
        return 'prompt';
    }
}

/** Register and return the ServiceWorkerRegistration */
async function registerServiceWorker(): Promise<ServiceWorkerRegistration | null> {
    if (!('serviceWorker' in navigator)) {
        return null;
    }
    try {
        const existing = await navigator.serviceWorker.getRegistration('/');
        if (existing) {
            return existing;
        }
        return await navigator.serviceWorker.register('/sw.js', {
            scope: '/',
        });
    } catch (err) {
        console.error('[SW] Registration failed:', err);
        return null;
    }
}

export function usePermissions(): UsePermissionsReturn {
    const [permissions, setPermissions] =
        useState<PermissionState>(INITIAL_STATE);
    const [swReady, setSwReady] = useState(false);
    const swRef = useRef<ServiceWorkerRegistration | null>(null);

    // ── Bootstrap: register SW + read current permission states ──────────────

    useEffect(() => {
        let cancelled = false;

        const init = async () => {
            // Register service worker
            const reg = await registerServiceWorker();
            if (!cancelled) {
                swRef.current = reg;
                setSwReady(reg !== null);
            }

            // Read all current permission states
            const [notification, camera, microphone, geolocation] =
                await Promise.all([
                    'Notification' in window
                        ? (Notification.permission as PermissionStatus)
                        : ('unsupported' as PermissionStatus),
                    queryPermission('camera'),
                    queryPermission('microphone'),
                    queryPermission('geolocation'),
                ]);

            if (!cancelled) {
                setPermissions({ notification, camera, microphone, geolocation });
            }
        };

        void init();
        return () => {
            cancelled = true;
        };
    }, []);

    // ── Helpers ───────────────────────────────────────────────────────────────

    const updatePermission = useCallback(
        (key: keyof PermissionState, status: PermissionStatus) => {
            setPermissions((prev) => ({ ...prev, [key]: status }));
        },
        [],
    );

    // ── Request Notification ──────────────────────────────────────────────────

    const requestNotification =
        useCallback(async (): Promise<PermissionStatus> => {
            if (!('Notification' in window)) {
                updatePermission('notification', 'unsupported');
                return 'unsupported';
            }

            if (Notification.permission === 'granted') {
                updatePermission('notification', 'granted');
                return 'granted';
            }

            // Ensure SW is registered before requesting permission
            if (!swRef.current) {
                swRef.current = await registerServiceWorker();
                setSwReady(swRef.current !== null);
            }

            const result = await Notification.requestPermission();
            const status = result as PermissionStatus;
            updatePermission('notification', status);
            return status;
        }, [updatePermission]);

    // ── Request Camera ────────────────────────────────────────────────────────

    const requestCamera = useCallback(async (): Promise<PermissionStatus> => {
        if (!navigator.mediaDevices?.getUserMedia) {
            updatePermission('camera', 'unsupported');
            return 'unsupported';
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: true,
            });
            stream.getTracks().forEach((t) => t.stop());
            updatePermission('camera', 'granted');
            return 'granted';
        } catch (err) {
            const status: PermissionStatus =
                err instanceof DOMException && err.name === 'NotAllowedError'
                    ? 'denied'
                    : 'denied';
            updatePermission('camera', status);
            return status;
        }
    }, [updatePermission]);

    // ── Request Microphone ────────────────────────────────────────────────────

    const requestMicrophone =
        useCallback(async (): Promise<PermissionStatus> => {
            if (!navigator.mediaDevices?.getUserMedia) {
                updatePermission('microphone', 'unsupported');
                return 'unsupported';
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                });
                stream.getTracks().forEach((t) => t.stop());
                updatePermission('microphone', 'granted');
                return 'granted';
            } catch {
                updatePermission('microphone', 'denied');
                return 'denied';
            }
        }, [updatePermission]);

    // ── Request Camera + Microphone together ──────────────────────────────────

    const requestCameraAndMicrophone =
        useCallback(async (): Promise<PermissionStatus> => {
            if (!navigator.mediaDevices?.getUserMedia) {
                updatePermission('camera', 'unsupported');
                updatePermission('microphone', 'unsupported');
                return 'unsupported';
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true,
                });
                stream.getTracks().forEach((t) => t.stop());
                updatePermission('camera', 'granted');
                updatePermission('microphone', 'granted');
                return 'granted';
            } catch {
                updatePermission('camera', 'denied');
                updatePermission('microphone', 'denied');
                return 'denied';
            }
        }, [updatePermission]);

    // ── Request Geolocation ───────────────────────────────────────────────────

    const requestGeolocation =
        useCallback(async (): Promise<PermissionStatus> => {
            if (!('geolocation' in navigator)) {
                updatePermission('geolocation', 'unsupported');
                return 'unsupported';
            }

            return new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(
                    () => {
                        updatePermission('geolocation', 'granted');
                        resolve('granted');
                    },
                    (err) => {
                        const status: PermissionStatus =
                            err.code === GeolocationPositionError.PERMISSION_DENIED
                                ? 'denied'
                                : 'denied';
                        updatePermission('geolocation', status);
                        resolve(status);
                    },
                    { timeout: 10_000 },
                );
            });
        }, [updatePermission]);

    // ── Request All ───────────────────────────────────────────────────────────

    const requestAll = useCallback(async (): Promise<PermissionState> => {
        const [notification, , , geolocation] = await Promise.all([
            requestNotification(),
            requestCameraAndMicrophone(),
            Promise.resolve(), // placeholder — camera+mic handled above
            requestGeolocation(),
        ]);

        const cameraStatus = permissions.camera;
        const micStatus = permissions.microphone;

        return {
            notification,
            camera: cameraStatus,
            microphone: micStatus,
            geolocation,
        };
    }, [
        requestNotification,
        requestCameraAndMicrophone,
        requestGeolocation,
        permissions.camera,
        permissions.microphone,
    ]);

    return {
        permissions,
        swReady,
        requestNotification,
        requestCamera,
        requestMicrophone,
        requestCameraAndMicrophone,
        requestGeolocation,
        requestAll,
    };
}
