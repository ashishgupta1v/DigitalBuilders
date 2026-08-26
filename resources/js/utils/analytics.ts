/**
 * DigitalBuilders Telemetry & Analytics Hub
 * Privacy-first event dispatcher supporting GA4, Plausible, and custom web-vitals RUM.
 */

export interface ConsentSettings {
    necessary: boolean;
    analytics: boolean;
    marketing: boolean;
    updatedAt: string;
}

const CONSENT_STORAGE_KEY = 'db_cookie_consent';

export function getStoredConsent(): ConsentSettings | null {
    if (typeof window === 'undefined') return null;
    try {
        const stored = localStorage.getItem(CONSENT_STORAGE_KEY);
        return stored ? JSON.parse(stored) : null;
    } catch {
        return null;
    }
}

export function saveConsent(settings: Partial<ConsentSettings>): ConsentSettings {
    const current: ConsentSettings = {
        necessary: true,
        analytics: settings.analytics ?? false,
        marketing: settings.marketing ?? false,
        updatedAt: new Date().toISOString(),
    };

    if (typeof window !== 'undefined') {
        try {
            localStorage.setItem(CONSENT_STORAGE_KEY, JSON.stringify(current));
            window.dispatchEvent(new CustomEvent('db:consent-updated', { detail: current }));
        } catch {
            // Storage quota or private browsing safeguard
        }
    }

    return current;
}

export function trackEvent(eventName: string, params: Record<string, any> = {}): void {
    if (typeof window === 'undefined') return;

    const consent = getStoredConsent();
    const isConsentGiven = consent?.analytics ?? false;

    // 1. Google Analytics 4
    if (isConsentGiven && typeof (window as any).gtag === 'function') {
        (window as any).gtag('event', eventName, params);
    }

    // 2. Plausible Analytics
    if (isConsentGiven && typeof (window as any).plausible === 'function') {
        (window as any).plausible(eventName, { props: params });
    }

    // 3. Custom internal event dispatch for in-app tracking
    window.dispatchEvent(new CustomEvent('db:telemetry', {
        detail: { event: eventName, params, timestamp: Date.now() },
    }));
}

// Bind to window for component convenience
if (typeof window !== 'undefined') {
    (window as any).dbTrack = trackEvent;
}
