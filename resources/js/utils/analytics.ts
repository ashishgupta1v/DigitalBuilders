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
 * Dynamically injects Plausible Analytics with SPA pageview and custom event tracking.
 */
export function initPlausible(): void {
    if (typeof window === 'undefined' || isPlausibleInitialized) return;

    const consent = getStoredConsent();
    if (!consent?.analytics) return;

    // Create queue function if not yet loaded
    (window as any).plausible = (window as any).plausible || function() {
        ((window as any).plausible.q = (window as any).plausible.q || []).push(arguments);
    };

    const plausibleDomain = (window as any).PLAUSIBLE_DOMAIN || 'digitalbuilders.in';
    const script = document.createElement('script');
    script.defer = true;
    script.dataset.domain = plausibleDomain;
    script.src = 'https://plausible.io/js/script.tagged-events.pageview-props.js';
    
    script.onerror = () => {
        // Fallback to standard tagged events
        const fallbackScript = document.createElement('script');
        fallbackScript.defer = true;
        fallbackScript.dataset.domain = plausibleDomain;
        fallbackScript.src = 'https://plausible.io/js/script.tagged-events.js';
        document.head.appendChild(fallbackScript);
    };

    document.head.appendChild(script);
    isPlausibleInitialized = true;
}

let isSentryInitialized = false;

/**
 * Dynamically injects Sentry Error Monitoring.
 * If SENTRY_DSN is configured, initializes Sentry Browser SDK.
 * Exposes a robust window.Sentry interface so window.Sentry is never undefined.
 */
export function initSentry(): void {
    if (typeof window === 'undefined') return;

    // Ensure window.Sentry is always globally available
    if (!(window as any).Sentry) {
        (window as any).Sentry = {
            captureException: (err: any, extra?: any) => {
                trackEvent('app_error', {
                    event_category: 'Sentry_Telemetry',
                    event_label: err?.message || String(err),
                    error_details: JSON.stringify({ err, extra, url: window.location.href }),
                });
            },
            captureMessage: (msg: string, level = 'info') => {
                trackEvent('app_log', {
                    event_category: 'Sentry_Message',
                    event_label: msg,
                    level,
                });
            },
            setUser: (user: any) => {
                (window as any).__db_user = user;
            },
            init: (options: any) => {
                console.info('[Sentry] Telemetry active with options:', options);
            },
        };
    }

    if (isSentryInitialized) return;

    const sentryDsn = (window as any).SENTRY_DSN;
    if (!sentryDsn || typeof sentryDsn !== 'string' || !sentryDsn.startsWith('http')) {
        isSentryInitialized = true;
        return;
    }

    // Load full Sentry Browser SDK bundle
    const script = document.createElement('script');
    script.defer = true;
    script.crossOrigin = 'anonymous';
    script.src = 'https://browser.sentry-cdn.com/7.100.0/bundle.min.js';

    script.onload = () => {
        if ((window as any).Sentry?.init) {
            (window as any).Sentry.init({
                dsn: sentryDsn,
                tracesSampleRate: 0.2,
                environment: (window as any).APP_ENV || 'production',
            });
            console.info('[Sentry] Remote error monitoring successfully initialized.');
        }
    };
    document.head.appendChild(script);
    isSentryInitialized = true;
}

/**
 * Client-side unhandled exception and performance monitoring.
 */
export function initErrorMonitoring(): void {
    if (typeof window === 'undefined' || isErrorMonitoringInitialized) return;

    // Boot Sentry
    initSentry();

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

        // Dispatch to Sentry
        if ((window as any).Sentry?.captureException && event.error) {
            (window as any).Sentry.captureException(event.error);
        }

        trackEvent('app_error', {
            event_category: 'System',
            event_label: event.message,
            error_details: JSON.stringify(errorData),
        });
    });

    window.addEventListener('unhandledrejection', (event) => {
        const reason = event.reason?.message || String(event.reason);

        // Dispatch to Sentry if active
        if ((window as any).Sentry?.captureException && event.reason) {
            (window as any).Sentry.captureException(event.reason);
        }

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

    const consent = getStoredConsent();
    const isConsentGiven = consent?.analytics ?? false;

    // Dispatch to GA4
    if (isConsentGiven && typeof (window as any).gtag === 'function') {
        (window as any).gtag('event', 'page_view', {
            page_path: url,
            page_location: window.location.href,
            page_title: title,
        });
    }

    // Dispatch to Plausible
    if (isConsentGiven && typeof (window as any).plausible === 'function') {
        (window as any).plausible('pageview', {
            u: window.location.origin + url,
            props: { title },
        });
    }

    trackEvent('page_view', {
        page_path: url,
        page_location: window.location.href,
        page_title: title,
    });
}

// ----------------------------------------------------------------------
// Typed High-Value Conversion Event Helpers
// ----------------------------------------------------------------------

export function trackBookingCompleted(bookingType: string, region: string, extra: Record<string, any> = {}): void {
    trackEvent('Booking Completed', {
        event_category: 'Conversion',
        booking_type: bookingType,
        region,
        ...extra,
    });
}

export function trackBrochureDownload(tier: 'india' | 'international' | string, region = 'INR'): void {
    trackEvent('Brochure Download', {
        event_category: 'Conversion',
        event_label: `Pricing Brochure - ${tier}`,
        tier,
        region,
    });
}

export function trackContactSubmit(projectType: string, region = 'INR'): void {
    trackEvent('Contact Submit', {
        event_category: 'Lead',
        event_label: projectType,
        project_type: projectType,
        region,
    });
}

export function trackEstimatorConfigured(projectType: string, budget: string, timeline: string): void {
    trackEvent('Estimator Configured', {
        event_category: 'Engagement',
        project_type: projectType,
        estimated_budget: budget,
        estimated_timeline: timeline,
    });
}

export function trackEstimatorSubmit(projectType: string, budget: string, region = 'INR'): void {
    trackEvent('Estimator Complete', {
        event_category: 'Lead',
        event_label: projectType,
        project_type: projectType,
        estimated_budget: budget,
        region,
    });
}

export function trackTierSelected(serviceId: string, tierName: string, region: string): void {
    trackEvent('Package Select', {
        event_category: 'Conversion',
        service_id: serviceId,
        tier_name: tierName,
        region,
    });
}

export function trackPricingRegionViewed(region: string): void {
    trackEvent('Pricing Region Viewed', {
        event_category: 'Engagement',
        region,
    });
}

export function trackNewsletterSignup(source = 'blog'): void {
    trackEvent('Newsletter Signup', {
        event_category: 'Lead',
        source,
    });
}

export function trackWhatsAppClick(source: string, extra: Record<string, any> = {}): void {
    trackEvent('WhatsApp Click', {
        event_category: 'Conversion',
        event_label: source,
        source,
        ...extra,
    });
}

export function trackPhoneClick(): void {
    trackEvent('Phone Call Click', {
        event_category: 'Contact',
    });
}

export function trackEmailClick(): void {
    trackEvent('Email Click', {
        event_category: 'Contact',
    });
}

// Auto-initialize trackers and error monitoring if consent is already given
if (typeof window !== 'undefined') {
    (window as any).dbTrack = trackEvent;
    (window as any).dbTrackPageView = trackPageView;

    initGA4();
    initPlausible();
    initErrorMonitoring();

    window.addEventListener('db:consent-updated', (e: any) => {
        if (e.detail?.analytics) {
            initGA4();
            initPlausible();
        }
    });
}
