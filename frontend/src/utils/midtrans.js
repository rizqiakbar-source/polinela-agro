/**
 * Midtrans Snap Script Dynamic Loader & Trigger
 */

const SNAP_SCRIPT_URL = 'https://app.sandbox.midtrans.com/snap/snap.js';
const CLIENT_KEY = 'Mid-client-_2Zc_0aE2qmqsO6H';

export const loadMidtransScript = (snapUrl = SNAP_SCRIPT_URL, clientKey = CLIENT_KEY) => {
    return new Promise((resolve, reject) => {
        if (window.snap) {
            resolve(window.snap);
            return;
        }

        let existingScript = document.getElementById('midtrans-snap-script');
        if (existingScript) {
            let elapsed = 0;
            const checkInterval = setInterval(() => {
                elapsed += 50;
                if (window.snap) {
                    clearInterval(checkInterval);
                    resolve(window.snap);
                } else if (elapsed > 4000) {
                    clearInterval(checkInterval);
                    // Try re-adding or resolve null
                    resolve(window.snap || null);
                }
            }, 50);
            return;
        }

        const script = document.createElement('script');
        script.id = 'midtrans-snap-script';
        script.src = snapUrl;
        script.setAttribute('data-client-key', clientKey);
        script.async = true;

        script.onload = () => {
            let elapsed = 0;
            const checkInterval = setInterval(() => {
                elapsed += 50;
                if (window.snap) {
                    clearInterval(checkInterval);
                    resolve(window.snap);
                } else if (elapsed > 3000) {
                    clearInterval(checkInterval);
                    resolve(window.snap || null);
                }
            }, 50);
        };
        script.onerror = () => {
            resolve(null);
        };

        document.head.appendChild(script);
    });
};

export const triggerMidtransPayment = async (snapToken, { onSuccess, onPending, onError, onClose, snapUrl, clientKey }) => {
    try {
        const snap = await loadMidtransScript(snapUrl, clientKey);
        if (!window.snap && !snap) {
            console.warn('Midtrans Snap is not loaded on window.');
            return false;
        }

        const snapInstance = window.snap || snap;
        snapInstance.pay(snapToken, {
            onSuccess: (result) => {
                if (onSuccess) onSuccess(result);
            },
            onPending: (result) => {
                if (onPending) onPending(result);
            },
            onError: (result) => {
                if (onError) onError(result);
            },
            onClose: () => {
                if (onClose) onClose();
            },
        });
        return true;
    } catch (err) {
        console.error('Midtrans trigger error:', err);
        return false;
    }
};
