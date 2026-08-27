/**
 * DigitalBuilders Telemetry & Analytics Hub
 * Privacy-first event dispatcher supporting GA4, Plausible, Sentry/Error telemetry,
 * and custom Core Web-Vitals RUM (compliant with India DPDP Act 2023 & GDPR).
 */

export interface ConsentSettings {
    necessary: boolean;
    analytics: boolean;
    marketing: boolean;
    updatedAt: string;
}

const CONSENT_STORAGE_KEY = 'db_cookie_consent';
let isGAInitialized = false;
let isPlausibleInitialized = false;
let isErrorMonitoringInitialized = false;

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

/**
 * Dynamically injects and boots GA4 only when analytics consent is granted.
 */
export function initGA4(): void {
    if (typeof window === 'undefined' || isGAInitialized) return;

    const consent = getStoredConsent();
    if (!consent?.analytics) return;

    const measurementId = (window as any).GA_MEASUREMENT_ID;
    if (!measurementId || typeof measurementId !== 'string' || !measurementId.startsWith('G-')) {
        return;
    }

    // Initialize Google dataLayer
    (window as any).dataLayer = (window as any).dataLayer || [];
    function gtag(...args: any[]) {
        (window as any).dataLayer.push(args);
    }
    (window as any).gtag = gtag;

    gtag('js', new Date());
    gtag('config', measurementId, {
        send_page_view: false, // Managed manually via Inertia SPA router
        anonymize_ip: true,
        cookie_flags: 'SameSite=None;Secure',
    });

    // Injects the gtag script tag
    const script = document.createElement('script');
    script.async = true;
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(measurementId)}`;
    document.head.appendChild(script);

    isGAInitialized = true;
}

/**
 * Dynamically injects Plausible Analytics when enabled.
 */
export function initPlausible(): void {
    if (typeof window === 'undefined' || isPlausibleInitialized) return;

    const consent = getStoredConsent();
    if (!consent?.analytics) return;

    const plausibleDomain = (window as any).PLAUSIBLE_DOMAIN || 'digitalbuilders.in';
    const script = document.createElement('script');
    script.defer = true;
    script.dataset.domain = plausibleDomain;
    script.src = 'https://plausible.io/js/script.tagged-events.js';
    document.head.appendChild(script);

    isPlausibleInitialized = true;
}

/**
 * Client-side unhandled exception and performance monitoring.
 */
export function initErrorMonitoring(): void {
    if (typeof window === 'undefined' || isErrorMonitoringInitialized) return;

    window.addEventListener('error', (event) => {
        const errorData = {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            stack: event.error?.stack?.substring(0, 500),
            timestamp: new Date().toISOString(),
            url: window.location.href,
        };

        trackEvent('app_error', {
            event_category: 'System',
            event_label: event.message,
            error_details: JSON.stringify(errorData),
        });
    });

    window.addEventListener('unhandledrejection', (event) => {
        const reason = event.reason?.message || String(event.reason);
        trackEvent('promise_rejection', {
            event_category: 'System',
            event_label: reason,
            timestamp: new Date().toISOString(),
            url: window.location.href,
        });
    });

    isErrorMonitoringInitialized = true;
}

/**
 * Generic event tracker respecting user consent.
 */
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

    // 3. Custom internal event dispatch for in-app monitoring & RUM
    window.dispatchEvent(new CustomEvent('db:telemetry', {
        detail: { event: eventName, params, timestamp: Date.now() },
    }));
}

/**
 * Track SPA Pageviews on Inertia transitions.
 */
export function trackPageView(pageUrl?: string, pageTitle?: string): void {
    if (typeof window === 'undefined') return;

    const url = pageUrl || window.location.pathname + window.location.search;
    const title = pageTitle || document.title;

    trackEvent('page_view', {
        page_path: url,
        page_location: window.location.href,
        page_title: title,
    });
}

// ----------------------------------------------------------------------
// Typed High-Value Conversion Event Helpers
// ----------------------------------------------------------------------

export function trackWhatsAppClick(source: string, extra: Record<string, any> = {}): void {
    trackEvent('whatsapp_click', {
        event_category: 'Conversion',
        event_label: source,
        source,
        ...extra,
    });
}

export function trackBrochureDownload(tier: 'india' | 'international' | string): void {
    trackEvent('brochure_download', {
        event_category: 'Conversion',
        event_label: `Pricing Brochure - ${tier}`,
        tier,
    });
}

export function trackContactSubmit(projectType: string): void {
    trackEvent('generate_lead', {
        event_category: 'Lead',
        event_label: projectType,
        project_type: projectType,
    });
}

export function trackEstimatorConfigured(projectType: string, budget: string, timeline: string): void {
    trackEvent('estimate_configured', {
        event_category: 'Engagement',
        project_type: projectType,
        estimated_budget: budget,
        estimated_timeline: timeline,
    });
}

export function trackEstimatorSubmit(projectType: string, budget: string): void {
    trackEvent('estimate_submitted', {
        event_category: 'Lead',
        event_label: projectType,
        project_type: projectType,
        estimated_budget: budget,
    });
}

export function trackTierSelected(serviceId: string, tierName: string, region: string): void {
    trackEvent('pricing_tier_selected', {
        event_category: 'Conversion',
        service_id: serviceId,
        tier_name: tierName,
        region: region,
    });
}

export function trackPhoneClick(): void {
    trackEvent('phone_call_click', {
        event_category: 'Contact',
    });
}

export function trackEmailClick(): void {
    trackEvent('email_click', {
        event_category: 'Contact',
    });
}

// Auto-initialize trackers and error monitoring if consent is already given
if (typeof window !== 'undefined') {
    (window as any).dbTrack = trackEvent;
    (window as any).dbTrackPageView = trackPageView;

    initGA4();
    initErrorMonitoring();

    window.addEventListener('db:consent-updated', (e: any) => {
        if (e.detail?.analytics) {
            initGA4();
            initPlausible();
        }
    });
}
